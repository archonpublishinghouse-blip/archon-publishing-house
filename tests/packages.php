<?php
// Run: php tests/packages.php. Uses SQLite memory only; never loads .env or a live database.
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) require dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
});
class_alias(App\Core\Security::class, 'Security');

use App\Services\PackageService;

final class PackageTestDatabase extends PDO {
    public bool $failSave = false;
    public function __construct() {
        parent::__construct('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $this->exec('CREATE TABLE settings(setting_key TEXT PRIMARY KEY,setting_value TEXT)');
        $this->exec('CREATE TABLE admins(id INTEGER PRIMARY KEY,name TEXT,email TEXT,role TEXT,is_active INTEGER)');
    }
    public function prepare(string $query, array $options = []): PDOStatement|false {
        if ($this->failSave && str_starts_with($query, 'INSERT INTO settings')) throw new PDOException('Simulated unavailable storage');
        // Adapt only MySQL's upsert clause; execute and reload real prepared statements in SQLite.
        $query = str_replace('ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)', 'ON CONFLICT(setting_key) DO UPDATE SET setting_value=excluded.setting_value', $query);
        return parent::prepare($query, $options);
    }
}

$db = new PackageTestDatabase();
(new ReflectionProperty(App\Core\Database::class, 'pdo'))->setValue(null, $db);
(new ReflectionProperty(App\Services\CrmSchemaService::class, 'ready'))->setValue(null, true);
$_SESSION = ['_csrf'=>'test-csrf'];

if (($argv[1] ?? '') === '--request') {
    $request = json_decode(stream_get_contents(STDIN), true, 32, JSON_THROW_ON_ERROR);
    if (isset($request['saved'])) PackageService::save($request['saved'], $db);
    if ($request['role'] !== 'guest') {
        $db->prepare('INSERT INTO admins VALUES (1,?,?,?,1)')->execute(['Test User', 'test@example.invalid', $request['role']]);
        $_SESSION['admin'] = ['id'=>1, 'role'=>$request['role']];
    }
    $db->failSave = $request['failSave'] ?? false;
    $_SERVER['REQUEST_URI'] = '/admin/packages';
    $_SERVER['REQUEST_METHOD'] = $request['method'];
    $_POST = $request['post'] ?? [];
    ob_start();
    register_shutdown_function(static function () use ($db): void {
        $body = ob_get_clean();
        echo json_encode(['status'=>http_response_code() ?: 200, 'body'=>$body, 'saved'=>PackageService::get($db)], JSON_THROW_ON_ERROR);
    });
    (new App\Core\Application())->run();
}

$count = 0;
function check(bool $condition, string $message): void {
    global $count;
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $count++;
    echo 'PASS: ' . $message . PHP_EOL;
}
function request(string $role, string $method, array $post = [], ?array $saved = null, bool $failSave = false): array {
    $process = proc_open([PHP_BINARY, __FILE__, '--request'], [['pipe','r'], ['pipe','w'], ['pipe','w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Could not start isolated request');
    fwrite($pipes[0], json_encode(compact('role','method','post','saved','failSave'), JSON_THROW_ON_ERROR));
    fclose($pipes[0]);
    $result = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
    if (proc_close($process) !== 0 || $error !== '') throw new RuntimeException('Request failed: ' . $error . $result);
    return json_decode($result, true, 32, JSON_THROW_ON_ERROR);
}
function publicPackages(): string {
    ob_start();
    require dirname(__DIR__) . '/app/Views/components/writing-packages.php';
    return ob_get_clean();
}

$default = PackageService::defaults();
check(PackageService::get($db) === $default, 'Unconfigured installation displays the three defaults without a migration');
check(PackageService::validate($default)[1] === [], 'Default configuration passes validation');
check(array_keys(PackageService::visible($default)) === ['basic','medium','premium'], 'Initial package order is Basic, Medium, Premium');

$edited = $default;
$edited['heading'] = 'Choose your writing plan';
$edited['packages']['basic']['name'] = 'Starter';
$edited['packages']['basic']['price'] = 'From $900';
$edited['packages']['basic']['features'] = "Outline\nAuthor review";
$edited['packages']['medium']['enabled'] = false;
$edited['packages']['premium']['order'] = 0;
PackageService::save($edited, $db);
check(PackageService::get($db) === $edited, 'Edited names, prices, features, order and visibility survive save and reload');
check(array_keys(PackageService::visible(PackageService::get($db))) === ['premium','basic'], 'Hidden packages disappear and visible packages follow display order');
check(PackageService::selected('medium') === null, 'Hidden package cannot be selected for a quote');
check(PackageService::selected(['basic']) === null && PackageService::selected('not-a-package') === null, 'Malformed and unknown package selections are rejected');
$html = publicPackages();
check(str_contains($html, 'Starter') && str_contains($html, 'From $900') && !str_contains($html, 'package-title-medium'), 'Public HTML reads saved configuration, including hidden state');
check(strpos($html, 'package-title-premium') < strpos($html, 'package-title-basic'), 'Saved display order is reflected in rendered HTML');
$snapshot = PackageService::leadDescription('A client project description.', PackageService::selected('basic'));
check(str_contains($snapshot, 'Starter (basic)') && str_contains($snapshot, 'From $900') && str_ends_with($snapshot, 'A client project description.'), 'CRM lead description retains selected package and displayed price with the enquiry');

$hostile = $default;
$hostile['packages']['basic']['name'] = '<script>alert(1)</script>';
$hostile['packages']['basic']['features'] = '<img src=x onerror=alert(1)>';
PackageService::save($hostile, $db);
$html = publicPackages();
check(!str_contains($html, '<script>') && !str_contains($html, '<img src=x') && str_contains($html, '&lt;script&gt;'), 'Admin-supplied package text is escaped in public HTML');

foreach (['missing'=>[], 'nested'=>['heading'=>[]], 'invalid order'=>['packages'=>['basic'=>['order'=>'-1']]], 'invalid highlight'=>['featured'=>'unknown'], 'empty features'=>['packages'=>['basic'=>['features'=>'']]], 'too many features'=>['packages'=>['basic'=>['features'=>implode("\n", array_fill(0, 16, 'Feature'))]]]] as $label => $change) {
    $input = $label === 'missing' ? [] : array_replace_recursive($default, $change);
    check(PackageService::validate($input)[1] !== [], 'Validation rejects ' . $label);
}
$hidden = $default;
foreach ($hidden['packages'] as &$package) $package['enabled'] = false;
unset($package);
PackageService::save($hidden, $db);
check(trim(publicPackages()) === '', 'Hiding every package does not restore defaults or leave an empty section');

foreach (['guest'=>302, 'employee'=>404] as $role=>$status) {
    foreach (['GET','POST'] as $method) {
        $result = request($role, $method, ['_token'=>'test-csrf','config'=>$edited]);
        check($result['status'] === $status && $result['saved'] === $default, $role . ' cannot view or change packages via ' . $method);
    }
}
foreach (['admin','super_admin'] as $role) {
    $result = request($role, 'GET', [], $edited);
    check($result['status'] === 200 && str_contains($result['body'], 'Starter'), $role . ' can load the editor with saved values');
    $result = request($role, 'POST', ['_token'=>'test-csrf', 'config'=>$edited]);
    check($result['status'] === 302 && $result['saved'] === $edited, $role . ' saves successfully through the protected route');
}
$result = request('admin', 'POST', ['_token'=>'invalid','config'=>$edited]);
check($result['status'] === 419 && $result['saved'] === $default, 'Invalid CSRF token prevents writes');
$invalid = $edited;
$invalid['packages']['basic']['name'] = '';
$result = request('admin', 'POST', ['_token'=>'test-csrf','config'=>$invalid]);
check($result['status'] === 422 && $result['saved'] === $default && str_contains($result['body'], 'From $900'), 'Validation errors preserve submitted fields without writing changes');
$result = request('admin', 'POST', ['_token'=>'test-csrf','config'=>$edited], null, true);
check($result['status'] === 503 && $result['saved'] === $default && str_contains($result['body'], 'From $900'), 'Storage errors show a retry message and preserve the form without claiming success');
echo $count . ' package checks passed.' . PHP_EOL;
