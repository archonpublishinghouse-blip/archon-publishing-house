<?php
namespace App\Services;

use App\Core\Env;

final class MailService {
    public static function send(string $to, string $subject, string $body): bool {
        $from=Env::get('MAIL_FROM_ADDRESS','hello@example.test');
        if (Env::get('MAIL_DRIVER','log') === 'mail') return @mail($to,$subject,$body,"From: $from\r\nContent-Type: text/plain; charset=UTF-8");
        $entry=sprintf("[%s]\nTo: %s\nSubject: %s\n\n%s\n%s\n",date('c'),$to,$subject,$body,str_repeat('-',64));
        return file_put_contents(dirname(__DIR__,2).'/storage/logs/mail.log',$entry,FILE_APPEND|LOCK_EX)!==false;
    }
}
