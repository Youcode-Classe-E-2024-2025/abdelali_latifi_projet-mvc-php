<?php

namespace App\Core\Twig;

use App\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SecurityExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];
    }

    public function getCsrfToken()
    {
        return Security::generateCsrfToken();
    }
}
