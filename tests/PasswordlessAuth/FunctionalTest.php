<?php

namespace dam1r89\PasswordlessAuth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/passwordless/login');

        $response->assertStatus(200);
    }

    public function testSigningUp()
    {

        Mail::fake();

        $this->assertDatabaseMissing('users', [
           'email' => 'mail@gmail.com'
        ]);

        $response = $this->post('/passwordless/login', [
            'email' => 'mail@gmail.com'
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHas('status', 'We have e-mailed your sign in link!');

        $this->assertNull(\Auth::user());

        $this->assertDatabaseHas('login_tokens', [
           'email' => 'mail@gmail.com'
        ]);

        $this->assertDatabaseHas('users', [
           'email' => 'mail@gmail.com'
        ]);

    }

    public function testLoggingIn()
    {

        Mail::fake();

        $response = $this->post('/passwordless/login', [
            'email' => 'mail@gmail.com'
        ]);

        $user = User::whereEmail('mail@gmail.com')->first();

        $token = LoginToken::whereEmail('mail@gmail.com')->first()->token;

        $response = $this->get(route('passwordless.auth', compact('token')));
        
        $response->assertRedirect('/home');

        $this->assertNotNull(\Auth::user());


    }

    public function testMultipleSignUps()
    {

    }

    public function testRedirectToIntended()
    {
        
    }
}
