<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use InvalidArgumentException;

final class AuthController
{
    public function showLogin(Request $req): Response
    {
        return View::render('auth/login', ['title' => 'Sign in']);
    }

    public function login(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $email = is_string($body['email'] ?? null) ? $body['email'] : '';
        $password = is_string($body['password'] ?? null) ? $body['password'] : '';
        if (Auth::login($email, $password) === null) {
            Session::flash('error', 'Invalid email or password');

            return (new Response())->redirect('/login');
        }

        return (new Response())->redirect('/');
    }

    public function showRegister(Request $req): Response
    {
        return View::render('auth/register', ['title' => 'Create account']);
    }

    public function register(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $name = is_string($body['name'] ?? null) ? $body['name'] : '';
        $email = is_string($body['email'] ?? null) ? $body['email'] : '';
        $password = is_string($body['password'] ?? null) ? $body['password'] : '';

        try {
            Auth::register($name, $email, $password);
        } catch (InvalidArgumentException $exception) {
            Session::flash('error', $exception->getMessage());

            return (new Response())->redirect('/register');
        }

        if (Auth::login($email, $password) === null) {
            Session::flash('error', 'Registration completed, but sign-in failed');

            return (new Response())->redirect('/login');
        }

        Session::flash('success', 'Welcome aboard');

        return (new Response())->redirect('/');
    }

    public function logout(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        Auth::logout();

        return (new Response())->redirect('/login');
    }
}
