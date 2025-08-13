<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Model\Admin\student;
use App\Model\Admin\student_payment;
use App\Model\Admin\teacher;
use App\Model\Admin\schedule_teacher_attendance;
use App\Model\Admin\teacher_payment;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\sms\sms_trait;



trait PaymentTrait
{
    use sms_trait;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function last_payment_of_student($student){
        $student_payment = student_payment::where('student_id', $student->id)->latest()->first();
        // return $student_payment;
        $today = Carbon::today();
        $days_5 = Carbon::now()->firstOfMonth()->addDays(5);
        if ($days_5->gt($today)) {
            return 'paid';
        }
        if ($student->student_type == 2) {
            return 'paid';
        }
        if (!$student_payment) {
            return 'unpaid';
        }
        if ($student_payment->month >= Carbon::today()->firstOfMonth()->format('Y-m-d')) {
            return 'paid';
        } else {
            return 'unpaid';
        }
    }

    public function payment_sms($student_id, $month, $student_payment){
        $request = new \stdClass();
        $bangla_month = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগষ্ট','সেপ্েটম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        $bangla_digit = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $reg_arr = [];
        $student = student::find($student_id);
        $reg = (string) $student->reg_no;
        for ($i=0; $i < strlen($reg); $i++) { 
            array_push($reg_arr, $bangla_digit[(int)$reg[$i]]);
        }

        if ($student_payment->amount > $student_payment->paid) {
            $amount = $student_payment->amount - $student_payment->paid;
            $amount = (string) $amount;
            $bangla_amount = [];
            for ($i=0; $i < strlen($amount); $i++) { 
                array_push($bangla_amount, $bangla_digit[(int)$amount[$i]]);
            }
            $request->sms = 'রেজি-'. join("",$reg_arr) . '। ' . $bangla_month[$month - 1] . ' মাসের বেতন নেয়া হয়েছে। ' . join("",$bangla_amount) . ' বকেয়া। সহপাঠ';
        }  else {
            $request->sms = 'রেজি নং- '. join("",$reg_arr) . '। ' . $bangla_month[$month - 1] . ' মাসের বেতন পরিশোধ হয়েছে। সহপাঠ।';
        }
        // return $request->sms;
        $request->mobile = $student->mobile;
        $request->campaign_name = '';
        $this->onnorokom_one_to_one($request);
    }
    
}
