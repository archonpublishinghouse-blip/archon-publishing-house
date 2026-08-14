<?php
namespace App\Services;

use App\Core\{Database,Env};

final class RememberService {
    private const COOKIE='archon_remember';
    public static function issue(int $customerId): void {$token=bin2hex(random_bytes(32));$expires=time()+60*60*24*30;Database::pdo()->prepare('INSERT INTO remember_tokens(customer_id,token_hash,expires_at) VALUES(?,?,?)')->execute([$customerId,hash('sha256',$token),date('Y-m-d H:i:s',$expires)]);setcookie(self::COOKIE,$token,['expires'=>$expires,'path'=>'/','secure'=>Env::get('SESSION_SECURE','false')==='true','httponly'=>true,'samesite'=>'Lax']);}
    public static function restore(): void {if(isset($_SESSION['customer'])||empty($_COOKIE[self::COOKIE]))return;$token=$_COOKIE[self::COOKIE];if(!preg_match('/^[a-f0-9]{64}$/',$token)){self::forget();return;}try{$statement=Database::pdo()->prepare('SELECT c.id,c.name,c.email FROM remember_tokens r JOIN customers c ON c.id=r.customer_id WHERE r.token_hash=? AND r.expires_at>NOW() AND c.is_active=1 ORDER BY r.id DESC LIMIT 1');$statement->execute([hash('sha256',$token)]);$customer=$statement->fetch();if(!$customer){self::forget();return;}session_regenerate_id(true);$_SESSION['customer']=['id'=>$customer['id'],'name'=>$customer['name'],'email'=>$customer['email']];}catch(\Throwable){}}
    public static function forget(): void {$token=$_COOKIE[self::COOKIE]??null;if(is_string($token)&&preg_match('/^[a-f0-9]{64}$/',$token)){try{Database::pdo()->prepare('DELETE FROM remember_tokens WHERE token_hash=?')->execute([hash('sha256',$token)]);}catch(\Throwable){}}setcookie(self::COOKIE,'',['expires'=>time()-3600,'path'=>'/','secure'=>Env::get('SESSION_SECURE','false')==='true','httponly'=>true,'samesite'=>'Lax']);unset($_COOKIE[self::COOKIE]);}
}
