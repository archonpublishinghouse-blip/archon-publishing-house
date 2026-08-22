<?php
namespace App\Core;
use App\Controllers\{SiteController,AuthController,AdminController};

final class Application {
    private string $path;
    public function __construct() { $path=parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; $base=rtrim(parse_url(Env::get('APP_URL',''),PHP_URL_PATH) ?: '','/'); if($base && str_starts_with($path,$base))$path=substr($path,strlen($base))?:'/'; $this->path=rtrim($path,'/')?:'/'; }
    public function run(): never {
        header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = $this->path;
        $site = new SiteController; $auth = new AuthController; $admin = new AdminController;
        if ($route === '/' && $method === 'GET') $site->home();
        if ($route === '/book-preview' && $method === 'GET') $site->bookPreview();
        if ($route === '/_archon-db-check' && $method === 'GET') $site->systemCheck();
        if ($route === '/contents' && $method === 'GET') $site->contents();
        if ($route === '/sitemap.xml' && $method === 'GET') $site->sitemap();
        if ($route === '/about' && $method === 'GET') $site->page('About Archon', 'about');
        if (in_array($route, ['/privacy','/terms'], true) && $method === 'GET') $site->policy(ltrim($route,'/'));
        if ($route === '/contact' && $method === 'GET') $site->contact();
        if ($route === '/contact' && $method === 'POST') $site->sendContact();
        if ($route === '/newsletter' && $method === 'POST') $site->newsletter();
        if ($route === '/unsubscribe' && $method === 'GET') $site->unsubscribe();
        if ($route === '/services' && $method === 'GET') $site->services();
        if (preg_match('#^/services/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->service($m[1]);
        if ($route === '/quote' && $method === 'GET') $site->quote();
        if ($route === '/quote' && $method === 'POST') $site->sendQuote();
        if ($route === '/authors' && $method === 'GET') $site->authors();
        if (preg_match('#^/authors/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->author($m[1]);
        if ($route === '/blog' && $method === 'GET') $site->blog();
        if (preg_match('#^/blog/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->article($m[1]);
        // Store and customer-account routes remain dormant in the services-only public site.
        if ($route === '/admin/login' && $method === 'GET') $auth->adminLoginForm();
        if ($route === '/admin/login' && $method === 'POST') $auth->adminLogin();
        if ($route === '/admin/logout' && $method === 'POST') $auth->adminLogout();
        if (in_array($route, ['/admin/login','/admin/logout'], true)) View::render('errors/404', [], 404);
        if ($route === '/admin' && $method === 'GET') $admin->dashboard();
        if ($route === '/admin/leads' && $method === 'GET') $admin->leads();
        if (preg_match('#^/admin/leads/(quote|contact)/([0-9]+)$#', $route, $m) && $method === 'GET') $admin->leadDetail($m[1], (int)$m[2]);
        if (preg_match('#^/admin/leads/(quote|contact)/([0-9]+)/status$#', $route, $m) && $method === 'POST') $admin->updateLeadStatus($m[1], (int)$m[2]);
        if (preg_match('#^/admin/leads/(quote|contact)/([0-9]+)/assign$#', $route, $m) && $method === 'POST') $admin->assignLead($m[1], (int)$m[2]);
        if (preg_match('#^/admin/leads/(quote|contact)/([0-9]+)/notes$#', $route, $m) && $method === 'POST') $admin->addLeadNote($m[1], (int)$m[2]);
        if (preg_match('#^/admin/leads/quote-attachments/([0-9]+)$#', $route, $m) && $method === 'GET') $admin->downloadQuoteAttachment((int)$m[1]);
        if ($route === '/admin/employees' && $method === 'GET') $admin->employees();
        if ($route === '/admin/employees/create' && $method === 'POST') $admin->createEmployee();
        if (preg_match('#^/admin/employees/([0-9]+)/update$#', $route, $m) && $method === 'POST') $admin->updateEmployee((int)$m[1]);
        if ($route === '/admin/book-files' && $method === 'GET') $admin->bookFiles();
        if ($route === '/admin/book-files' && $method === 'POST') $admin->uploadBookFile();
        if ($route === '/admin/book-files/delete' && $method === 'POST') $admin->deleteBookFile();
        if (preg_match('#^/admin/export/(quotes|contacts|subscribers)$#', $route, $m) && $method === 'GET') $admin->export($m[1]);
        if (preg_match('#^/admin/([a-z-]+)$#', $route, $m) && $method === 'GET') $admin->resource($m[1]);
        if (preg_match('#^/admin/([a-z-]+)/(create|edit|delete)$#', $route, $m) && $method === 'POST') $admin->mutate($m[1],$m[2]);
        View::render('errors/404', [], 404);
    }
}
