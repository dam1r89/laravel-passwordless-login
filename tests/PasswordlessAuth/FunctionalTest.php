<?php

namespace dam1r89\PasswordlessAuth;

use Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use dam1r89\PasswordlessAuth\Events\UserRegistered;
use dam1r89\PasswordlessAuth\PasswordlessLink;


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

        $response = $this->post('/passwordless/login', compact('email'));

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


        $response = $this->post('/passwordless/login', compact('email'));

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

        $response = $this->post('/passwordless/login', compact('email'));

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

        $this->post('/passwordless/login', compact('email'));

        $response = $this->post('/passwordless/login', compact('email'));

        $response->assertStatus(302)
            ->assertSessionHas('status', 'We have e-mailed your sign in link!');

    }

    public function testRedirectToIntended()
    {
        Mail::fake();

        $email = $this->faker->email;

        $response = $this->get('protected');

        $response->assertRedirect('/login');

        $response = $this->post('/passwordless/login', compact('email'));

        $user = User::whereEmail($email)->first();

        $token = LoginToken::whereEmail($email)->first()->token;


        $response = $this->get(route('passwordless.auth', compact('token')));
        
        $response->assertRedirect('/protected');

        $response = $this->get('protected');
        
        $response->assertSee('protected content');

        $this->assertNotNull(\Auth::user());
    }

    public function testPasswordlessLink()
    {
        $email = $this->faker->email;
        $user = User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        $link = PasswordlessLink::for($user)->url('/protected-resource');

        $this->assertNotNull($link);

        $response = $this->get($link);

        $response->assertRedirect('/protected-resource');
    }

    public function testPasswordlessLinkRoute()
    {
        $email = $this->faker->email;
        $user = User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        $link = PasswordlessLink::for($user)->route('namedRoute', ['some' => 'param']);

        $response = $this->get($link)
            ->assertRedirect('named/route?some=param');

    }

    public function testPasswordlessLinkNotCreatingDuplicatesForSameLink()
    {
        $email = $this->faker->email;
        $user = User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        PasswordlessLink::for($user)->url('/protected-resource');
        PasswordlessLink::for($user)->url('/protected-resource');

        $this->assertEquals(1, LoginToken::whereEmail($email)->count());

        PasswordlessLink::for($user)->url('/second-resource');

        $this->assertEquals(2, LoginToken::whereEmail($email)->count());
    }

    public function testUsingLinkTwice()
    {
        Mail::fake();

        $email = $this->faker->email;
        $user = User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        $response = $this->post('/passwordless/login', compact('email'));

        $link = LoginToken::whereEmail($email)->first()->getLoginLink();

        $response = $this->get($link);
        $response->assertRedirect('/home');

        $response = $this->get($link);
        $response->assertRedirect('/passwordless/login');

    }

    public function testRedirectConfig()
    {
        Mail::fake();

        $email = $this->faker->email;
        $user = User::forceCreate(['email' => $email, 'name' => '', 'password' => '']);

        $response = $this->post('/passwordless/login', compact('email'));

        $link = LoginToken::whereEmail($email)->first()->getLoginLink();

        $this->app['config']->set('passwordless.redirect_to', '/dashboard');

        $response = $this->get($link);
        $response->assertRedirect('/dashboard');
    }

    public function testTrigeringEvent()
    {

        Mail::fake();
        Event::fake();

        $email = $this->faker->email;

        $this->assertDatabaseMissing('users', compact('email'));

        $response = $this->post('/passwordless/login', compact('email'));


        Event::assertDispatched(UserRegistered::class, function ($e) {
            return $e->user !== null;
        });


        $this->assertDatabaseHas('users', compact('email'));
    }
}
