<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\EmailVerificationService;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use RuntimeException;

final class EmailVerificationController
{
    public function verify(Request $req): Response
    {
        try {
            EmailVerificationService::verify($req->param('token') ?? '');
        } catch (RuntimeException $exception) {
            return View::render('auth/verify-invalid', [
                'title' => 'Invalid verification link',
                'message' => $exception->getMessage(),
            ]);
        }

        Session::flash('success', 'Email verified');

        return (new Response())->redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function resend(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $user = Auth::user();
        if ($user === null) {
            return (new Response())->redirect('/login');
        }
        if ($user['email_verified_at'] !== null) {
            Session::flash('info', 'Email already verified');

            return (new Response())->redirect('/dashboard');
        }

        $rawToken = EmailVerificationService::send((int) $user['id'], (string) $user['email']);
        if (config('app.env') === 'local') {
            Session::flash('dev_verify_url', 'Verification URL: ' . url('/verify-email/' . $rawToken));
        }
        Session::flash('success', 'Verification email sent');

        return (new Response())->redirect('/dashboard');
    }
}
