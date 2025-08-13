<?php

namespace Tests\Feature;

use App\Model\author;
use App\Model\host;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Auth;
use File;
use Faker\Factory as Faker;
use Carbon\Carbon;

trait supportClass
{
    

    protected function create_student($request)
    {
        if ($request->student_add_type == 'new') {            
            DB::table('student_payments')->delete();
            DB::table('students')->delete();
        }
        $faker = Faker::create();
        $now = Carbon::now();
        if ($request->payment_type=='unpaid') {
            $admit_month = $now->subMonth(1);
        }
        if ($request->payment_type=='unpaid_multiple') {
            $admit_month = $now->subMonth(2);
        }
        if ($request->payment_type=='advance' || $request->payment_type == 'due') {
            $admit_month = $now;
        }
        $min_date = $admit_month->year. '-' . $admit_month->month . '-01';
        $max_date = $admit_month->year. '-' . $admit_month->month . '-15';
        if ($request->payment_date) {
            $min_date = Carbon::parse($request->payment_date)->format('Y-m-d');
            $max_date = Carbon::parse($request->payment_date)->format('Y-m-d');
        }
        $mobile = [];
        if ($request->mobile) {
            $mobile = explode(",", $request->mobile);
        }
        for ($i=0; $i < $request->size ; $i++) { 
            $item                 = new \App\Model\Admin\student;
            $item->name           = $faker->name;
            $item->gender         = $faker->randomElement(['male', 'female']);
            $item->reg_no         = $request->reg_no + $i;
            $item->branch_id      = 1;
            $item->batch_id       = sizeof($request->batches)>0 ? $request->batches[0]['value'] : $faker->randomElement([1,2,3,4,5]);
            $item->institution_id = $faker->randomElement([14,15,16,17,18]);
            $item->school_roll    = $faker->randomElement([1,2,5,9,10]);
            $item->date_of_birth  = $faker->dateTimeBetween('2000-01-01', '2008-12-31');
            $item->date_of_admit  = $faker->date($min_date, $max_date);
            $item->first_admission  = $item->date_of_admit;
            $item->father_name    = $faker->name('male');
            $item->mother_name    = $faker->name('female');
            $item->mobile         = array_key_exists($i, $mobile) ? $mobile[$i] : '01676352571';
            $item->address        = 'oxygen';
            if ($request->fee == 'monthly_fee') {
                $item->monthly_fee    = $faker->randomElement([1200,1400,1500,1700,1000]);
            }
            if ($request->fee == 'course_fee'){
                $item->course_fee     = $faker->randomElement([6500,5500,9800,8000,7500]);
            }
            $item->status         = $request->status;
            $item->running_month   = $item->date_of_admit;
            $item->save();
            $this->create_student_payment($item, $request->payment_status, $request->payment_size, $request->payment_type);
        }
        return $item;
    }

    protected function create_student_payment($item, $payment_status, $payment_size, $payment_type)
    {
        $faker = Faker::create();
        for ($i=0; $i < $payment_size; $i++) {             
            $student_payment = new \App\Model\Admin\student_payment;        
            $student_payment->student_id = $item->id;
            $student_payment->received_id = 1;
            $student_payment->amount = $item->monthly_fee ? $item->monthly_fee : $item->course_fee;
            $student_payment->paid = $item->monthly_fee ? $item->monthly_fee : $item->course_fee;
            if ($payment_type == 'due') {
                $student_payment->paid = $item->monthly_fee ? $item->monthly_fee - 500 : $item->course_fee - 2000;
            }
            $student_payment->month = Carbon::parse($item->date_of_admit)->addMonths($i)->format('Y-m-d');
            $student_payment->status = $payment_status;        
            $student_payment->date = $student_payment->month;
            $student_payment->month_count = 1;
            $student_payment->next_payment_date = Carbon::parse($student_payment->month)->addMonths(1)->format('Y-m-d');
            $student_payment->save();
        }
    }

    protected function create_visiting_list($request)
    {
        $faker = Faker::create();
        $mobile = [];
        if ($request->mobile) {
            $mobile = explode(",", $request->mobile);
        }
        \App\Model\Admin\visiting_list::where('id', '>', 0)->delete();
        $visiting_list = new \App\Model\Admin\visiting_list;
        $visiting_list->name = $faker->name;
        $visiting_list->branch_id = 1;
        $visiting_list->echelon_id = $request->echelon_id ? $request->echelon_id : $faker->randomElement([3,4,5,6,7,8]);;
        $visiting_list->gender = $faker->randomElement(['male', 'female']);
        $visiting_list->institution_id = $faker->randomElement([14,15,16,17,18]);
        $visiting_list->school_roll = $faker->randomElement([1,2,5,9,10]);
        $visiting_list->father_name = $faker->name('male');
        $visiting_list->mother_name = $faker->name('female');
        $visiting_list->mobile = array_key_exists(0, $mobile) ? $mobile[0] : '01676352571';
        $visiting_list->address = 'oxygen';
        $visiting_list->save();
    }

    protected function change_teacher_subject_to_schedule($date, $change_status_null = false, $delete_teacher_schedule = false, $teacher_id, $subject_id, $schedule_type = 'class')
    {
        // echelon 3,4,5,6,7,8
        if ($change_status_null) {
            \App\Model\Admin\daily_schedule::where('id','>',0)->update(['status'=> null]);
        }
        $s = \App\Model\Admin\daily_schedule::whereDate('date', $date)->where('echelon_id', 4)->first();
        
        // delete teacher schedule
        if ($delete_teacher_schedule) {
            \App\Model\Admin\schedule_teacher_attendance::where('schedule_id', $s->id)->where('teacher_id', $s->teacher_id)->delete(); 
        }
        $s->status = 'contented';
        $s->schedule_type = $schedule_type;
        if ($teacher_id>0) {
            $s->teacher_id = $teacher_id;
        }
        if ($subject_id>0) {            
            $s->subject_id = $subject_id;
        }
        $s->save();
    }

    protected function create_daily_schedule($request)
    {
        $faker = Faker::create();
        \App\Model\Admin\schedule_teacher_attendance::where('id', '>', 0)->delete();
        

        $subjects = [];
        foreach ($request->subjects as $key => $subject) {
            array_push($subjects, $subject['value']);
        }
        $teachers = [];
        foreach ($request->teachers as $key => $teacher) {
            array_push($teachers, $teacher['value']);
        }
        $lecture_sheet = null;
        if ($request->lecture_sheet) {
            $lecture_sheet = 1;
        }
        foreach ($request->batches as $key => $batch) {
            $daily_schedule = new \App\Model\Admin\daily_schedule;
            $daily_schedule->batch_id = $batch['value'];
            $daily_schedule->subject_id = array_key_exists($key, $subjects) ? $subjects[$key] : $subjects[0];
            $daily_schedule->teacher_id = array_key_exists($key, $teachers) ? $teachers[$key] : $teachers[0];
            if ($request->content_type == 'contented') {
                $daily_schedule->topic = str_random(20);
                $daily_schedule->chapter_text = str_random(10);
            }
            $daily_schedule->schedule_type = $request->schedule_type;
            $daily_schedule->lecture_sheet = $lecture_sheet;
            $daily_schedule->period = 1;
            $daily_schedule->date = $request->date;

            
            $daily_schedule->status = $request->schedule_status;
            if ($request->content_type == 'contented') {
                $daily_schedule->status = 'contented';
            }
            $daily_schedule->save();
        }
        // attendance add
        if ($request->attendance_status || $request->take_attendance) {
            \App\Model\Admin\attendance_list::whereDate('date', Carbon::today())->delete();
            if ($request->attendance_status == 'both' || $request->take_attendance) {
                $status = 'completed';
            }
            else {
                $status = $request->attendance_status;
            }
            foreach ($request->batches as $b_key => $batch) {
                $attendance_list = new \App\Model\Admin\attendance_list;
                $attendance_list->batch_id = $batch['value'];
                $attendance_list->date = Carbon::today()->format('Y-m-d');
                $attendance_list->status = $status;
                $attendance_list->sms_status = $status == 'sms' ? 1: 0;
                $attendance_list->save();
                if ($request->take_attendance) {
                    $this->take_attendance($batch['value'], $attendance_list->id);
                }
                if ($request->attendance_status == 'both') {
                    $status = $status == 'completed' ? 'sms' : 'completed';
                }
            }

        }
    }

    protected function take_attendance($batch_id, $attendance_list_id)
    {
        $student_list = \App\Model\Admin\student::where('batch_id', $batch_id)->where('status', 'present')->get();
        foreach ($student_list as $key => $student) {
            $std = new \App\Model\Admin\student_attendance;
            $std->student_id = $student->id;
            $std->date = Carbon::today()->format('Y-m-d');
            $std->attendance_list_id = $attendance_list_id;
            $std->attendance = rand(0,1);
            $std->save();
        }
    }
    protected function teacher_done($request, $schedule_id, $teachers)
    {
        foreach ($request->batches as $b_key => $batch) {
            $teacher = new \App\Model\Admin\schedule_teacher_attendance;
            $teacher->schedule_id = $schedule_id;
            $teacher->teacher_id = $teachers[0];
            $teacher->batch_id = $batch['value'];
            $teacher->save();
        }
    }
    
}

