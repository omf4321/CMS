<?php

namespace Tests\Feature;

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
use Tests\Feature\supportClass;
use Carbon\Carbon;

class EventCrudTest extends TestCase
{
    use supportClass;    

    // vendor\bin\phpunit --filter 'tests\\Feature\\EventCrudTest::test_something
    // vendor\bin\phpunit --bootstrap vendor/autoload.php tests\Feature\EventCrudTest
    public function test_something()
    {
        // teacher_id = 21, schedule_id = 55(nine), 56(six), echelon_id = 4 (six), 7(nine)
        // subject id six = 7, 8, 
        // subject_id nine = 16, 22
        // change_teacher_subject_to_schedule($date, $change_status_null = false, $delete_teacher_schedule = false, $teacher_id, $subject_id, $schedule_type)
        // teacher, subject id 0 for false
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::today()->addDays(1)->format('Y-m-d');
        $other_date = Carbon::today()->addDays(2)->format('Y-m-d');
        // $this->change_teacher_subject_to_schedule($today, false, false, 0, 0, 'class');

        // create_student($reg_no, $size = 1, $status = 'present', $monthly_fee = true, $course_fee = false, $payment_status='paid', $payment_size= 1, $payment_type = 'unpaid', $payment_today = false)
        // payment_type = unpaid, unpaid_multiple, advance, due
        $this->create_student(9101, 2, 'present', true, false, 'paid', 1, 'unpaid', true);
    }
}
