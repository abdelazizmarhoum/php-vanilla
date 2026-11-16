<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\PasswordResetService;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\PasswordReset;
use InvalidArgumentException;
use RuntimeException;

final class PasswordResetController
{
    public function showRequest(Request $req): Response
    {
        return View::render('auth/forgot-password', ['title' => 'Forgot password']);
    }

    public function request(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $email = is_string($body['email'] ?? null) ? $body['email'] : '';
        PasswordResetService::request($email);
        Session::flash('success', 'If that email is registered, a reset link has been sent.');

        return (new Response())->redirect('/forgot-password');
    }

    public function showReset(Request $req): Response
    {
        $token = $req->param('token') ?? '';
        $reset = PasswordReset::findByTokenHash(hash('sha256', $token));
        if (
            $reset === null
            || $reset['used_at'] !== null
            || strtotime((string) $reset['expires_at']) < time()
        ) {
            return View::render('auth/reset-invalid', ['title' => 'Invalid reset link']);
        }

        return View::render('auth/reset-password', [
            'title' => 'Set a new password',
            'token' => $token,
        ]);
    }

    public function reset(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $csrfToken = $body['_token'] ?? null;
        if (!is_string($csrfToken) || !CSRF::verify($csrfToken)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $token = $req->param('token') ?? '';
        $password = is_string($body['password'] ?? null) ? $body['password'] : '';
        $confirmation = is_string($body['password_confirmation'] ?? null) ? $body['password_confirmation'] : '';
        if ($password !== $confirmation) {
            Session::flash('error', 'Passwords do not match');

            return (new Response())->redirect('/reset-password/' . $token);
        }

        try {
            PasswordResetService::reset($token, $password);
        } catch (InvalidArgumentException $exception) {
            Session::flash('error', $exception->getMessage());

            return (new Response())->redirect('/reset-password/' . $token);
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());

            return (new Response())->redirect('/forgot-password');
        }

        Session::flash('success', 'Password updated. Please sign in.');

        return (new Response())->redirect('/login');
    }
}
