<?php

namespace Tests\Feature\Auth;

use App\Model\host;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_register_with_correct_info()
    {
        User::where('email', 'ofdeclast@gmail.com')->delete();
        $this->visit(route('register'))
            ->type('Omar Faruque1234', 'name')
            ->type('ofdeclast@gmail.com', 'email')
            ->type('123456', 'password')
            ->type('123456', 'password_confirmation')
            ->type('male', 'gender')
            ->type('traveller', 'user_type')
            ->press('Sign Up ⇾')
            ->seePageIs('/register-success')
            ->see('A verification email sent to your email')
            ->see('Resend');
    }

    public function test_user_cannot_register_with_incorrect_info()
    {
        // unique email
        $this->visit(route('register'))
            ->type('omar', 'name')
            ->type('ofdeclast@gmail.com', 'email')
            ->type('123456', 'password')
            ->type('123456', 'password_confirmation')
            ->type('male', 'gender')
            ->type('traveller', 'user_type')
            ->press('Sign Up ⇾')
            ->seePageIs('/register')
            ->see('The email has already been taken');
        // invalid email
        $this->visit(route('register'))
            ->type('omar', 'name')
            ->type('ofdeclast', 'email')
            ->type('123456', 'password')
            ->type('123456', 'password_confirmation')
            ->type('male', 'gender')
            ->type('traveller', 'user_type')
            ->press('Sign Up ⇾')
            ->seePageIs('/register')
            ->see('The email must be a valid email address');
        // match password
        $this->visit(route('register'))
            ->type('omar', 'name')
            ->type('ofdeclast@gmail.com', 'email')
            ->type('123456', 'password')
            ->type('1234567', 'password_confirmation')
            ->type('male', 'gender')
            ->type('traveller', 'user_type')
            ->press('Sign Up ⇾')
            ->seePageIs('/register')
            ->see('The password confirmation does not match');
        // password min 6
        $this->visit(route('register'))
            ->type('omar', 'name')
            ->type('ofdeclast@gmail.com', 'email')
            ->type('1111', 'password')
            ->type('1111', 'password_confirmation')
            ->type('male', 'gender')
            ->type('traveller', 'user_type')
            ->press('Sign Up ⇾')
            ->seePageIs('/register')
            ->see('The password must be at least 6 characters');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $this->visit(route('login'))
            ->type('ofdeclast@gmail.com', 'email')
            ->type('123456', 'password')
            ->press('Login')
            ->seePageIs('/');
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $this->visit(route('login'))
            ->type('hello@gmail.com', 'email')
            ->type('testpass123', 'password')
            ->press('Login')
            ->see('These credentials do not match our records');
	}

	public function test_user_can_view_a_forgot_password_form()
    {
        $this->visit('/login')
        ->click('Forgot Password?')
        ->seePageIs('/password/reset');
    }

    public function test_can_sent_reset_password_with_correct_email()
    {
        $this->visit('/password/reset')
        ->type('ofdeclast@gmail.com', 'email')
        ->press('Send Password Reset Link')
        ->see("We have e-mailed your password reset link!");
    }

    public function test_cannot_sent_reset_password_with_incorrect_email()
    {
        $this->visit('/password/reset')
        ->type('ofdeast@gmail.com', 'email')
        ->press('Send Password Reset Link')
        ->see("We can't find a user with that e-mail address.");
    }

    public function test_see_passsword_reset_form()
    {
        $token = DB::table('password_resets')->where('email', 'ofdeclast@gmail.com')->first();
        $this->visit('/password/reset/'.$token->token)
        ->see("Reset Password");
    }

    public function test_verification_email()
    {
        $user = User::where('email', 'ofdeclast@gmail.com')->first();
        $this->visit('/verification/'.$user->id.'/'.$user->verify_token)
        ->see("Verified Successfully");
    }

    public function test_verification_email_mismatch()
    {
        $user = User::where('email', 'ofdeclast@gmail.com')->first();
        $this->visit('/verification/'.$user->id.'/dkjfdjflkjdfjd')
        ->see("Token mismatch");
    }

    public function test_user_can_view_a_login_form()
    {
        $this->visit('/login')
        ->see('Sign in Using Your Email Address');
    }

    public function test_user_cannot_view_a_login_form_when_authenticated()
    {
        $user = User::where('email', 'ofdeclast@gmail.com')->first();
        $this->actingAs($user)
             ->visit('/login')
             ->seePageIs('/');
    }

    public function test_user_can_view_a_register_form()
    {
        $this->visit('/register')
        ->see('Sign Up For Free');
    }
    public function test_user_cannot_view_a_register_form_when_authenticated()
    {
        $user = User::where('email', 'ofdeclast@gmail.com')->first();
        $this->actingAs($user)
             ->visit('/register')
             ->seePageIs('/');
    }
}
