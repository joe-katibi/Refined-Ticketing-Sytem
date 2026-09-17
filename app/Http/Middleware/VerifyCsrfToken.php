<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    // 'login' was previously exempted here too, enabling login CSRF (an
    // attacker can force a victim's browser to submit a login to an
    // attacker-controlled account). The login form (resources/views/auth/
    // login.blade.php) already sends @csrf, so there was no technical
    // reason for the exemption — removed.
    protected $except = [
        'api/mobile/*',
    ];
}
