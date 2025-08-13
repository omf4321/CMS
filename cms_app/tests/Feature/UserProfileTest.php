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

class UserProfileTest extends TestCase
{
    public function test_guest_user_cannot_visit_profile()
    {
        $this->visit('/user/profile')
        ->seePageIs('/login');
    }

    public function test_authenticated_user_can_visit_profile()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see($user->email);
        User::where('email', 'panda@animal.com')->delete();
    }
    public function test_unverified_user_see_verification_message()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo'),
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your email is not verified yet');
        User::where('email', 'panda@animal.com')->delete();
    }

    public function test_verified_user_dont_see_verification_message()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo'),
            'verify_email'=> 1
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('New Notifications')
             ->dontSee('Your email is not verified yet');
        User::where('email', $user->email)->delete();
    }

    public function test_verification_email_success()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo'),
            'verify_token'=> str_random(40)
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $this->visit('/verification/'.$user->id.'/'.$user->verify_token)
        ->see("Verified Successfully");
    }

    public function test_verification_token_mismatch_with_incorrect()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo'),
            'verify_token'=> str_random(40)
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $this->visit('/verification/'.$user->id.'/dfdfdjfkjdfk')
        ->see("Token mismatch");
    }

    public function test_verification_sent_on_resend_click()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo'),
            'verify_token'=> str_random(40)
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        Auth::login($user);
        $this->call('POST', '/verification/resend');

        $user1 = User::where('email', $user->email)->first();
        $this->visit('/verification/'.$user1->id.'/'.$user1->verify_token)
        ->see("Verified Successfully");
    }

    public function test_update_profile()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your Profile')
             ->type('hello','name')
             ->type('gorilla@gmail.com','email')
             ->type('female', 'gender')
             ->type('01676352572', 'phone_number')
             ->type('1988-02-01', 'date_of_birth')
             ->type('Bonani', 'address')
             ->type('Software Engineer', 'profession')
             ->type('HSC', 'education')
             ->press('Save')
             ->assertResponseOk()
             ->see('Update Succesfully');
        User::where('email', 'gorilla@gmail.com')->delete();
    }

    public function test_cannot_update_profile_with_existing_email()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user1= User::create([
            'name' => "panda",
            'email' => "horse@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user1->name, 'host_link'=>str_random(8), 'user_id'=>$user1->id, 'user_type'=>'user']);

        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your Profile')
             ->type($user1->email,'email')
             ->press('Save')
             ->see('The email has already been taken');
             
        User::where('email', $user->email)->delete();
        User::where('email', $user1->email)->delete();
    }

    public function test_cannot_update_profile_without_11digit_phone_number()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your Profile')
             ->type('0167635','phone_number')
             ->press('Save')
             ->see('The phone number must be at least 11 characters');
             
        User::where('email', $user->email)->delete();
    }

    public function test_update_host_profile()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your Host Details')
             ->type('host name example','host_name')
             ->type('host link example','host_link')
             ->type('host detail example', 'host_detail')
             ->type('facebook of host', 'facebook')
             ->type('twitter of host', 'twitter')
             ->type('instagram of host', 'instagram')
             ->type('google plus of host', 'google_plus')
             ->press('Save Host')
             ->assertResponseOk()
             ->see('Update Succesfully');
        User::where('email', $user->email)->delete();
    }

    public function test_see_author_profile_when_has_author_privilige()
    {
        User::where('email', 'panda@animal.com')->delete();
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        author::create(['author_name'=>$user->name, 'author_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $user->assignRole('author');

        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your Author Details');

        $user->removeRole('author');
        User::where('email', $user->email)->delete();
    }

    public function test_update_author_profile()
    {
        $user = User::where('email', 'panda@animal.com')->first(); 
        if ($user) {
            author::where('user_id', $user->id)->delete();     
            $user->removeRole('author');
            $user->delete();
        }

        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        author::create(['author_name'=>$user->name, 'author_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $user->assignRole('author');

        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Your author Details')
             ->type('author name example','author_name')
             ->type('author link example','author_link')
             ->type('author detail example', 'author_detail')
             ->type('facebook of author', 'facebook')
             ->type('twitter of author', 'twitter')
             ->type('instagram of author', 'instagram')
             ->type('google plus of author', 'google_plus')
             ->press('Save Author')
             ->assertResponseOk()
             ->see('Update Succesfully');

        author::where('user_id', $user->id)->delete();     
        $user->removeRole('author');
        User::where('email', $user->email)->delete();
    }

    public function test_update_payment_detail()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Payment Details')
             ->type('bkash','payment_method')
             ->type('01976352585','account_number')
             ->press('Save Payment')
             ->assertResponseOk()
             ->see('Update Succesfully');
        User::where('email', $user->email)->delete();
    }

    public function test_cannot_update_payment_with_invalid_account_number()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Payment Details')
             ->type('bkash','payment_method')
             ->type('016762575','account_number')
             ->press('Save Payment')
             ->see('The account number must be at least 11 characters');
        User::where('email', $user->email)->delete();
    }

    public function test_cannot_update_payment_with_existing_account_number()
    {
        User::where('email', 'panda@animal.com')->delete();
        User::where('email', 'amanda@animal.com')->delete();

        $user1= User::create([
            'name' => "panda",
            'email' => "amanda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        $host = host::create(['host_name'=>$user1->name, 'host_link'=>str_random(8), 'user_id'=>$user1->id, 'user_type'=>'user', 'account_number' => '01676857595']);

        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => bcrypt('bamboo')
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Payment Details')
             ->type('bkash','payment_method')
             ->type($host->account_number,'account_number')
             ->press('Save Payment')
             ->see('The account number has already been taken');

        User::where('email', $user->email)->delete();
        User::where('email', $user1->email)->delete();
    }

    public function test_update_password()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Change Password')
             ->type('bamboo','current_password')
             ->type('sticker','new_password')
             ->type('sticker','new_password_confirmation')
             ->press('Save Password')
             ->assertResponseOk()
             ->see('Update Succesfully');
        User::where('email', $user->email)->delete();
    }

    public function test_update_password_and_login_with_this_password()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Change Password')
             ->type('bamboo','current_password')
             ->type('sticker','new_password')
             ->type('sticker','new_password_confirmation')
             ->press('Save Password')
             ->assertResponseOk()
             ->see('Update Succesfully');

        $this->visit(route('logout'));
        
        $this->visit(route('login'))
            ->type($user->email, 'email')
            ->type('sticker', 'password')
            ->press('Login')
            ->seePageIs('/');

        User::where('email', $user->email)->delete();
    }

    public function test_cannot_update_password_without_valid_current_password()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Change Password')
             ->type('dfdfdf','current_password')
             ->type('sticker','new_password')
             ->type('sticker','new_password_confirmation')
             ->press('Save Password')
             ->see('Your current password does not matches with the password you provided');
        User::where('email', $user->email)->delete();
    }
    public function test_cannot_update_password_with_same_new_password()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Change Password')
             ->type('bamboo','current_password')
             ->type('bamboo','new_password')
             ->type('bamboo','new_password_confirmation')
             ->press('Save Password')
             ->see('New Password cannot be same as your current password');
        User::where('email', $user->email)->delete();
    }

    public function test_cannot_update_password_with_invalid_password()
    {
        User::where('email', 'panda@animal.com')->delete(); 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);
        
        $this->actingAs($user)
             ->visit('/user/profile')
             ->seePageIs('/user/profile')
             ->see('Change Password')
             ->type('bamboo','current_password')
             ->type('bamb','new_password')
             ->type('bamb','new_password_confirmation')
             ->press('Save Password')
             ->see('The new password must be at least 6 characters');
        User::where('email', $user->email)->delete();
    }

    public function test_upload_photo()
    {
        $u = User::where('email', 'panda@animal.com')->first();
        if ($u) {
            author::where('user_id', $u->id)->delete();
            $u->delete();
        } 
        
        $user= User::create([
            'name' => "panda",
            'email' => "panda@animal.com",
            'password' => 'bamboo'
        ]);
        host::create(['host_name'=>$user->name, 'host_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        author::create(['author_name'=>$user->name, 'author_link'=>str_random(8), 'user_id'=>$user->id, 'user_type'=>'user']);

        $user->assignRole('author');

        Auth::login($user);
        
        $file = "data:image/jpeg;base64,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";

        $response = $this->call('POST', '/user/upload_image', [
            'profile_image' => $file
        ]);
        $this->assertResponseOk();
        $user1 = User::where('email', $user->email)->first();
        $Path = public_path('storage').'/image/avatar/'.$user1->image;
        File::delete($Path);

        $response = $this->call('POST', '/user/upload_image', [
            'host_feature_image' => $file
        ]);
        $this->assertResponseOk();
        $Path = public_path('storage').'/image/host/feature/'.$user1->hosts->feature_image;
        File::delete($Path);
        User::where('email', $user->email)->delete();

        $response = $this->call('POST', '/user/upload_image', [
            'author_feature_image' => $file
        ]);
        $this->assertResponseOk();
        $Path = public_path('storage').'/image/author/feature/'.$user1->authors->feature_image;
        File::delete($Path);
        $user->removeRole('author');
        User::where('email', $user->email)->delete();
    }
}

