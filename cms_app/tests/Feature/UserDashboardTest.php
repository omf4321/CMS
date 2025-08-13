<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Auth;
use Faker\Factory as Faker;
use Tests\Feature\supportClass;

class UserDashboardTest extends TestCase
{
    use supportClass;

    public function test_guest_user_cannot_visit_dashboard()
    {
        $this->visit('/user/dashboard')
        ->seePageIs('/login');
    }

    public function test_authenticated_user_can_visit_dashboard()
    {        
        $user = $this->create_user('panda@animal.com', 'Robin Marvels');
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->see('USER DASHBOARD');
        $this->delete_user('panda@animal.com');
    }
    public function test_unverified_user_see_verification_message()
    {
        $user = $this->create_user('panda@animal.com', 'Robin Marvels');
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->see('Your email is not verified yet');
        $this->delete_user('panda@animal.com');
    }

    public function test_verified_user_dont_see_verification_message()
    {
        $user = $this->create_user('panda@animal.com', 'Robin Marvels', 1);
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->dontSee('Your email is not verified yet');
        $this->delete_user('panda@animal.com');
    }

    public function test_verification_email_success()
    {
        $user = $this->create_user('panda@animal.com', 'Robin Marvels');
        $this->visit('/verification/'.$user->id.'/'.$user->verify_token)
        ->see("Verified Successfully");
        $this->delete_user('panda@animal.com');
    }

    public function test_verification_token_mismatch_with_incorrect()
    {
        $user = $this->create_user('panda@animal.com', 'Robin Marvels');
        $this->visit('/verification/'.$user->id.'/dfdfdjfkjdfk')
        ->see("Token mismatch");
        $this->delete_user('panda@animal.com');
    }

    public function test_verification_sent_on_resend_click()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels');
        Auth::login($user);
        $this->call('POST', '/verification/resend');
        $user1 = User::where('email', $user->email)->first();
        $this->visit('/verification/'.$user1->id.'/'.$user1->verify_token)
        ->see("Verified Successfully");
        $this->delete_user('biggaponvan@gmail.com');
    }

    public function test_user_see_profile_complete_if_not_80()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1);
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->See('Complete Your Profile');
        $this->delete_user('biggaponvan@gmail.com');
    }

    public function test_user_dontsee_profile_complete_if_80_and_see_listing_warn()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->dontSee('Complete Your Profile')
             ->see('You Have No Upcomming Trip')
             ->see('You Have no Travel Story');
        $this->delete_user('biggaponvan@gmail.com');
    }

    public function test_user_see_upcomming_trip_summary_if_dates_upcomming()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $this->create_event($user, 'upcomming', 1200);
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->dontSee('Complete Your Profile')
             ->dontSee('You Have No Upcomming Trip')
             ->dontSee('You Have no Travel Story')
             ->see('Upcomming Trips\' Join Summary');
        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_event($event);
    }

    public function test_user_dontsee_upcomming_trip_summary_if_dates_old()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $this->create_event($user, 'old', 1200);
        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->See('You Have No Upcomming Trip')
             ->See('You Have no Travel Story')
             ->dontSee('Upcomming Trips\' Join Summary');
        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_event($event);
    }

    public function test_user_see_pending_booking_request()
    {
        $host_user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $guest_user = $this->create_user('durbeen@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');

        $event = $this->create_event($host_user, 'upcomming', 1200);
        $booking = $this->create_booking($guest_user, $event, 'confirmed');
        $this->create_transaction($guest_user, $booking, 'paid');

        $this->actingAs($host_user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->dontSee('You Have No Upcomming Trip')
             ->dontSee('You Have no Travel Story')
             ->See('Upcomming Trips\' Join Summary')
             ->see('Pending Booking Request')
             ->see($guest_user->name)
             ->see($event->title)
             ->see('Accept')
             ->see('Deny');
        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_user('durbeen@gmail.com');
        $this->delete_event($event);
    }

    public function test_user_see_put_booking_information_after_accepted()
    {
        $host_user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $guest_user = $this->create_user('durbeen@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');

        $event = $this->create_event($host_user, 'upcomming', 1200);
        $booking = $this->create_booking($guest_user, $event, 'confirmed', 'paid');
        $this->create_transaction($guest_user, $booking, 'paid');
        $this->update_booking($booking, 'accepted');

        $this->actingAs($guest_user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->see('Next Trip You Plan to Go')
             ->see('contact with host')
             ->see($event->contact_information)
             ->see('Total Join');
        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_user('durbeen@gmail.com');
        $this->delete_event($event);
    }

    public function test_user_see_put_booking_information_after_denied()
    {
        $host_user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');
        $guest_user = $this->create_user('durbeen@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');

        $event = $this->create_event($host_user, 'upcomming', 1200);
        $booking = $this->create_booking($guest_user, $event, 'confirmed', 'paid');
        $this->update_booking($booking, 'denied');

        $this->actingAs($guest_user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->see('Reson of Denied');
        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_user('durbeen@gmail.com');
        $this->delete_event($event);
    }

    public function test_user_see_post_information_if_has_any()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, 'abcd.jpg', '01676352571', '01676352571');

        $post = $this->create_post($user, 'saved');

        $this->actingAs($user)
             ->visit('/user/dashboard')
             ->seePageIs('/user/dashboard')
             ->see('Travel Story List')
             ->see($post->title)
             ->see($post->status)
             ->see('views');

        $this->delete_user('biggaponvan@gmail.com');
        $this->delete_post($post);
    }

    public function test_create_host()
    {
        $user = $this->create_user('biggaponvan@gmail.com', 'Robin Marvels', 1, NULL, '01676352571', 'travel_organization');
        $this->create_host($user, 'dt');
        $event = $this->create_event($user);
        $this->create_event_date($event);
        $event = $this->create_event($user);
        $this->create_event_date($event, 'upcomming', 2);
        $event = $this->create_event($user);
        $this->create_event_date($event, 'old');
    }
}

