<?php
namespace App\Services;

final class BookChapterService {
    public static function chapters(): array {
        return [
            ['page'=>'cover','number'=>'','label'=>'Cover','title'=>'Your personalized eBook journey','href'=>'/','available'=>true],
            ['page'=>'contents','number'=>'I','label'=>'Table of Contents','title'=>'Inside your journey','href'=>'/contents','available'=>true],
            ['page'=>'home','number'=>'II','label'=>'Home','title'=>'Turn your idea into an eBook','href'=>'/','available'=>true],
            ['page'=>'about','number'=>'III','label'=>'About Archon','title'=>'Who we are and how we help','href'=>'/about','available'=>true],
            ['page'=>'services','number'=>'IV','label'=>'eBook Writing Services','title'=>'Professional support for your eBook','href'=>'/services','available'=>true],
            ['page'=>'genres','number'=>'V','label'=>'Genres and Writing Expertise','title'=>'Writing expertise for your subject','href'=>'/services','available'=>true],
            ['page'=>'process','number'=>'VI','label'=>'Our eBook Writing Process','title'=>'How your eBook takes shape','href'=>'/services','available'=>true],
            ['page'=>'work','number'=>'VII','label'=>'Our Work / eBook Portfolio','title'=>'Examples of our publishing craft','href'=>'/authors','available'=>true],
            ['page'=>'authors','number'=>'VIII','label'=>'Authors We Have Worked With','title'=>'Writers supported by Archon','href'=>'/authors','available'=>true],
            ['page'=>'reviews','number'=>'IX','label'=>'Client Reviews','title'=>'Words from our clients','href'=>'/','available'=>true],
            ['page'=>'quote','number'=>'X','label'=>'Start Your eBook / Request a Quote','title'=>'Share your idea with our team','href'=>'/quote','available'=>true],
            ['page'=>'contact','number'=>'XI','label'=>'Contact Us','title'=>'Start a conversation','href'=>'/contact','available'=>true],
            ['page'=>'final','number'=>'XII','label'=>'Back Cover / Final Call to Action','title'=>'Bring your eBook to life','href'=>'/quote','available'=>true],
        ];
    }
}
