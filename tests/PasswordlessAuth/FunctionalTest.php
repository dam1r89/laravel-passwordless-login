<?php

namespace dam1r89\PasswordlessAuth;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;


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

        $email = $this->faker->email;

        $this->assertDatabaseMissing('users', compact('email'));

        $response = $this->post('/passwordless/login', [
            'email' => $email
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHas('status', 'We have e-mailed your sign in link!');

        $this->assertNull(\Auth::user());

        $this->assertDatabaseHas('login_tokens', compact('email'));

        $this->assertDatabaseHas('users', compact('email'));

    }

    public function testThrottling()
    {
        Mail::fake();

        for ($i=0; $i < 5; $i++) { 
            $this->post('/passwordless/login', [
                'email' => 'mail@gmail.com'
            ]);
        }

        $this->assertEquals(1, LoginToken::whereEmail('mail@gmail.com')->count(), 'Should send only once for consecutive tries.');
    }

    public function testShouldNotDuplicateLoginTokens()
    {
        Mail::fake();

        $throttle = $this->app['config']->get('passwordless.throttle');
        for ($i=0; $i < 5; $i++) { 
            Carbon::setTestNow(Carbon::now()->addSeconds($throttle + 10)); 
            $this->post('/passwordless/login', [
                'email' => 'mail@gmail.com'
            ]);
        }

        $this->assertEquals(1, LoginToken::whereEmail('mail@gmail.com')->count(), 'Should send only once for consecutive tries.');

    }

    public function testLoggingIn()
    {

        Mail::fake();

        $email = $this->faker->email;


        $response = $this->post('/passwordless/login', [
            'email' => $email
        ]);

        $user = User::whereEmail($email)->first();

        $loginToken = LoginToken::whereEmail($email)->first();

        $this->assertNotNull($loginToken);

        $token = $loginToken->token;

        $response = $this->get(route('passwordless.auth', compact('token')));
        
        $response->assertRedirect('/home');

        $this->assertNotNull(\Auth::user());
    }

    public function testNotUsingSignUp()
    {
        Mail::fake();

        $this->app['config']->set('passwordless.sign_up', false);

        $email = $this->faker->email;

        $this->assertDatabaseMissing('users', compact('email'));

        $response = $this->post('/passwordless/login', [
            'email' => $email
        ]);

        $response->assertStatus(302);

        $this->assertRegexp('/User with  e-mail ".+?" not found/', app('session.store')->get('status'));

        $this->assertDatabaseMissing('login_tokens', compact('email'));
        $this->assertDatabaseMissing('users', compact('email'));

    }

    public function testResponseMessage()
    {
        Mail::fake();

        $this->app['config']->set('passwordless.sign_up', false);

        $email = $this->faker->email;

        User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        $this->assertDatabaseHas('users', compact('email'));

        $this->post('/passwordless/login', [
            'email' => $email
        ]);

        $response = $this->post('/passwordless/login', [
            'email' => $email
        ]);

        $response->assertStatus(302)
            ->assertSessionHas('status', 'We have e-mailed your sign in link!');

    }

    public function testMultipleSignUps()
    {

    }

    public function testRedirectToIntended()
    {
        
    }
}
