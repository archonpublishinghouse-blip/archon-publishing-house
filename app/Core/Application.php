<?php
namespace App\Core;
use App\Controllers\{SiteController,ShopController,AuthController,AccountController,AdminController};

final class Application {
    private string $path;
    public function __construct() { $path=parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; $base=rtrim(parse_url(Env::get('APP_URL',''),PHP_URL_PATH) ?: '','/'); if($base && str_starts_with($path,$base))$path=substr($path,strlen($base))?:'/'; $this->path=rtrim($path,'/')?:'/'; }
    public function run(): never {
        header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = $this->path;
        $site = new SiteController; $shop = new ShopController; $auth = new AuthController; $account = new AccountController; $admin = new AdminController;
        if ($route === '/' && $method === 'GET') $site->home();
        if ($route === '/book-preview' && $method === 'GET') $site->bookPreview();
        if ($route === '/contents' && $method === 'GET') $site->contents();
        if ($route === '/sitemap.xml' && $method === 'GET') $site->sitemap();
        if ($route === '/about' && $method === 'GET') $site->page('About Archon', 'about');
        if (in_array($route, ['/privacy','/terms','/refund-policy','/download-policy'], true) && $method === 'GET') $site->policy(ltrim($route,'/'));
        if ($route === '/contact' && $method === 'GET') $site->contact();
        if ($route === '/contact' && $method === 'POST') $site->sendContact();
        if ($route === '/newsletter' && $method === 'POST') $site->newsletter();
        if ($route === '/unsubscribe' && $method === 'GET') $site->unsubscribe();
        if ($route === '/services' && $method === 'GET') $site->services();
        if (preg_match('#^/services/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->service($m[1]);
        if ($route === '/quote' && $method === 'GET') $site->quote();
        if ($route === '/quote' && $method === 'POST') $site->sendQuote();
        if ($route === '/authors' && $method === 'GET') $site->authors();
        if ($route === '/categories' && $method === 'GET') $site->categories();
        if (preg_match('#^/authors/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->author($m[1]);
        if ($route === '/blog' && $method === 'GET') $site->blog();
        if (preg_match('#^/blog/([a-z0-9-]+)$#', $route, $m) && $method === 'GET') $site->article($m[1]);
        // Store/customer routes are deliberately not exposed in the services-only public site.
        if ($route === '/login' && $method === 'GET') $auth->loginForm();
        if ($route === '/login' && $method === 'POST') $auth->login();
        if ($route === '/register' && $method === 'GET') $auth->registerForm();
        if ($route === '/register' && $method === 'POST') $auth->register();
        if ($route === '/logout' && $method === 'POST') $auth->logout();
        if ($route === '/forgot-password' && $method === 'GET') $auth->forgotForm();
        if ($route === '/forgot-password' && $method === 'POST') $auth->forgot();
        if ($route === '/reset-password' && $method === 'GET') $auth->resetForm();
        if ($route === '/reset-password' && $method === 'POST') $auth->reset();
        if ($route === '/verify-email' && $method === 'GET') $auth->verifyEmail();
        if ($route === '/admin' && $method === 'GET') $admin->dashboard();
        if ($route === '/admin/book-files' && $method === 'GET') $admin->bookFiles();
        if ($route === '/admin/book-files' && $method === 'POST') $admin->uploadBookFile();
        if ($route === '/admin/book-files/delete' && $method === 'POST') $admin->deleteBookFile();
        if (preg_match('#^/admin/export/(quotes|contacts|subscribers)$#', $route, $m) && $method === 'GET') $admin->export($m[1]);
        if (preg_match('#^/admin/([a-z-]+)$#', $route, $m) && $method === 'GET') $admin->resource($m[1]);
        if (preg_match('#^/admin/([a-z-]+)/(create|edit|delete)$#', $route, $m) && $method === 'POST') $admin->mutate($m[1],$m[2]);
        View::render('errors/404', [], 404);
    }
}
