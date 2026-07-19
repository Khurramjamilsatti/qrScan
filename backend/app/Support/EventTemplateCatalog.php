<?php

namespace App\Support;

class EventTemplateCatalog
{
    public const FREE_TEMPLATES = [
        'simple-invite',
        'birthday-bash',
        'classic-wedding',
    ];

    public const ALL_TEMPLATES = [
        'simple-invite',
        'birthday-bash',
        'classic-wedding',
        'wedding-elegant',
        'wedding-modern',
        'wedding-romantic',
        'wedding-minimal',
        'desi-wedding',
        'birthday-kids',
        'birthday-elegant',
        'surprise-party',
        'baby-shower',
        'gender-reveal',
        'graduation',
        'farewell',
        'corporate-event',
        'retirement',
        'eid-greeting',
        'christmas-card',
        'new-year',
        'memorial',
        'digital-gift-card',
    ];

    public static function isValid(string $template): bool
    {
        return in_array($template, self::ALL_TEMPLATES, true);
    }

    public static function isPremium(string $template): bool
    {
        return ! in_array($template, self::FREE_TEMPLATES, true);
    }
}
