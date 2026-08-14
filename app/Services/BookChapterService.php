<?php
namespace App\Services;

final class BookChapterService {
    public static function chapters(): array {
        return [
            ['number'=>'I','label'=>'Home','title'=>'A beginning for your book','href'=>'/','available'=>true],
            ['number'=>'II','label'=>'Services','title'=>'eBook writing services','href'=>'/services','available'=>true],
            ['number'=>'III','label'=>'Books','title'=>'Our work and portfolio','href'=>null,'available'=>false],
            ['number'=>'IV','label'=>'Authors','title'=>'Authors we have worked with','href'=>'/authors','available'=>true],
            ['number'=>'V','label'=>'Publishing Process','title'=>'How your eBook takes shape','href'=>'/','available'=>true],
            ['number'=>'VI','label'=>'Reviews','title'=>'Words from our clients','href'=>'/','available'=>true],
            ['number'=>'VII','label'=>'Contact Us','title'=>'Start a conversation','href'=>'/contact','available'=>true],
            ['number'=>'VIII','label'=>'Begin Your Publishing Journey','title'=>'Request a quote','href'=>'/quote','available'=>true],
        ];
    }
}
