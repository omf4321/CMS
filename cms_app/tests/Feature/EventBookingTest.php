<?php

namespace Tests\Feature;

use App\Model\author;
use App\Model\host;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Auth;
use File;

class EventBookingTest extends TestCase
{
    public function test_guest_user_cannot_visit_profile()
    {
        $this->visit('/user/profile')
        ->seePageIs('/login');
    }

    public function test_redirect_after_register()
    {
        User::where('email', 'panda@animal.com')->delete();
        
        $this->visit(route('event.booking.initial', 87))
             ->seePageIs('/login');
        $this->visit(route('register'))
             ->seePageIs('/register')
             ->type('durbeen', 'name')
             ->type('panda@animal.com', 'email')
             ->type('male', 'gender')
             ->type('traveller', 'user_type')
             ->type('123456', 'password')
             ->type('123456', 'password_confirmation')
             ->press('Sign Up')
             ->see('You are likely to booking a trip');
        User::where('email', 'panda@animal.com')->delete();
    }
}

