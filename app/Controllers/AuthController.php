<?php
namespace App\Controllers;
use App\Core\Security;
use App\Services\MailService;
use App\Services\CartService;
use App\Services\RememberService;
use App\Services\CrmSchemaService;

final class AuthController extends Controller {
    public function adminLoginForm(): never {
        if ($this->admin()) Security::redirect('/admin');
        $this->render('auth/admin-login', ['title'=>'Archon Administration','__layout'=>'admin']);
    }
    public function adminLogin(): never {
        $this->requirePost();
        CrmSchemaService::ensure($this->db());
        if (!Security::rateLimit('admin-login', 5, 900)) {
            Security::flash('error', 'Too many login attempts. Please try again later.');
            Security::redirect('/admin/login');
        }
        $email=filter_var($_POST['email']??'', FILTER_VALIDATE_EMAIL);
        $password=$_POST['password']??'';
        $admin=$email?$this->one('SELECT * FROM admins WHERE email=? AND is_active=1',[$email]):null;
        if (!$admin||!password_verify($password,$admin['password_hash'])) {
            Security::flash('error','The email or password is incorrect.');
            Security::redirect('/admin/login');
        }
        session_regenerate_id(true);
        $_SESSION['admin']=['id'=>$admin['id'],'name'=>$admin['name'],'email'=>$admin['email'],'role'=>$admin['role'] ?: 'admin'];
        $this->db()->prepare('UPDATE admins SET last_login_at=NOW() WHERE id=?')->execute([$admin['id']]);
        Security::flash('success','Admin session started.');
        Security::redirect('/admin');
    }
    public function adminLogout(): never {
        $this->requirePost();
        unset($_SESSION['admin']);
        session_regenerate_id(true);
        Security::flash('success','You have been signed out.');
        Security::redirect('/admin/login');
    }
    public function loginForm(): never {$this->render('auth/login');}
    public function registerForm(): never {$this->render('auth/register');}
    public function login(): never {$this->requirePost();if(!Security::rateLimit('login',5,900)){Security::flash('error','Too many login attempts. Please try again later.');Security::redirect('/login');}$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);$password=$_POST['password']??'';$admin=$email?$this->one('SELECT * FROM admins WHERE email=? AND is_active=1',[$email]):null;if($admin&&password_verify($password,$admin['password_hash'])){session_regenerate_id(true);$_SESSION['admin']=['id'=>$admin['id'],'name'=>$admin['name'],'email'=>$admin['email'],'role'=>'super_admin'];$this->db()->prepare('UPDATE admins SET last_login_at=NOW() WHERE id=?')->execute([$admin['id']]);Security::flash('success','Admin session started.');Security::redirect('/admin');}$customer=$email?$this->one('SELECT * FROM customers WHERE email=? AND is_active=1',[$email]):null;if(!$customer||!password_verify($password,$customer['password_hash'])){Security::flash('error','The email or password is incorrect.');Security::redirect('/login');}session_regenerate_id(true);$_SESSION['customer']=['id'=>$customer['id'],'name'=>$customer['name'],'email'=>$customer['email']];CartService::mergeIntoCustomer((int)$customer['id']);if(!empty($_POST['remember']))RememberService::issue((int)$customer['id']);Security::flash('success','Welcome back, '.$customer['name'].'. Your saved cart is ready.');Security::redirect('/account');}
    public function register(): never {$this->requirePost();$name=trim($_POST['name']??'');$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);$password=$_POST['password']??'';if(strlen($name)<2||!$email||strlen($password)<10||!preg_match('/[A-Za-z]/',$password)||!preg_match('/\d/',$password)){Security::flash('error','Use your name, a valid email, and a 10-character password with letters and numbers.');Security::redirect('/register');}try{$this->db()->prepare('INSERT INTO customers(name,email,password_hash) VALUES(?,?,?)')->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);}catch(\PDOException){Security::flash('error','That email is already registered.');Security::redirect('/register');}$id=(int)$this->db()->lastInsertId();$token=bin2hex(random_bytes(32));$this->db()->prepare('INSERT INTO email_verifications(customer_id,token_hash,expires_at) VALUES(?,?,?)')->execute([$id,hash('sha256',$token),date('Y-m-d H:i:s',time()+86400)]);MailService::send($email,'Verify your Archon email','Welcome to Archon. Verify your email within 24 hours: /verify-email?token='.$token);session_regenerate_id(true);$_SESSION['customer']=['id'=>$id,'name'=>$name,'email'=>$email];CartService::mergeIntoCustomer($id);Security::flash('success','Your Archon account is ready. Check your email for a verification link.');Security::redirect('/account');}
    public function logout(): never {$this->requirePost();RememberService::forget();unset($_SESSION['customer'],$_SESSION['admin']);session_regenerate_id(true);Security::flash('success','You have been signed out.');Security::redirect('/');}
    public function forgotForm(): never {$this->render('auth/forgot');}
    public function forgot(): never {$this->requirePost();if(!Security::rateLimit('forgot',3,3600)){Security::flash('success','If an account exists, reset instructions have been sent.');Security::redirect('/forgot-password');}$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);if($email&&($c=$this->one('SELECT id FROM customers WHERE email=?',[$email]))){$token=bin2hex(random_bytes(32));$this->db()->prepare('INSERT INTO password_resets (customer_id,token_hash,expires_at) VALUES (?,?,?)')->execute([$c['id'],hash('sha256',$token),date('Y-m-d H:i:s',time()+3600)]);MailService::send($email,'Reset your Archon password','Use this one-hour reset link: /reset-password?token='.$token);}Security::flash('success','If an account exists, reset instructions have been sent.');Security::redirect('/forgot-password');}
    public function resetForm(): never {$token=trim($_GET['token']??'');$this->render('auth/reset',compact('token'));}
    public function reset(): never {$this->requirePost();$token=trim($_POST['token']??'');$password=$_POST['password']??'';if(!preg_match('/^[a-f0-9]{64}$/',$token)||strlen($password)<10||!preg_match('/[A-Za-z]/',$password)||!preg_match('/\d/',$password)){Security::flash('error','This reset link is invalid or the password does not meet the requirements.');Security::redirect('/reset-password?token='.rawurlencode($token));}$reset=$this->one('SELECT * FROM password_resets WHERE token_hash=? AND used_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1',[hash('sha256',$token)]);if(!$reset){Security::flash('error','This reset link is invalid or has expired.');Security::redirect('/forgot-password');}$pdo=$this->db();$pdo->beginTransaction();try{$pdo->prepare('UPDATE customers SET password_hash=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$reset['customer_id']]);$pdo->prepare('UPDATE password_resets SET used_at=NOW() WHERE customer_id=? AND used_at IS NULL')->execute([$reset['customer_id']]);$pdo->prepare('DELETE FROM remember_tokens WHERE customer_id=?')->execute([$reset['customer_id']]);$pdo->commit();Security::flash('success','Your password has been reset. Please sign in.');Security::redirect('/login');}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
    public function verifyEmail(): never {$token=trim($_GET['token']??'');if(!preg_match('/^[a-f0-9]{64}$/',$token)){Security::flash('error','That verification link is invalid.');Security::redirect('/');}$verification=$this->one('SELECT * FROM email_verifications WHERE token_hash=? AND expires_at>NOW() ORDER BY id DESC LIMIT 1',[hash('sha256',$token)]);if(!$verification){Security::flash('error','That verification link is invalid or expired.');Security::redirect('/');}$pdo=$this->db();$pdo->beginTransaction();try{$pdo->prepare('UPDATE customers SET email_verified_at=NOW() WHERE id=?')->execute([$verification['customer_id']]);$pdo->prepare('DELETE FROM email_verifications WHERE customer_id=?')->execute([$verification['customer_id']]);$pdo->commit();Security::flash('success','Your email address has been verified.');Security::redirect('/account');}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
}
