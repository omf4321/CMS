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
use DB;
use App\Http\Controllers\sms\sms_trait;
use App\Http\Controllers\payment\PaymentTrait;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use PaymentTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function get_student_payment_list(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $role = 'manager';
        $from_date = Carbon::today();
        $to_date = Carbon::today();

        if ($request->reg_no) {
            $student_payment = student_payment::WhereHas('students', function($query) use ($request){
                $query->where('reg_no', $request->reg_no)->where('status', 'present');
            })->with('students.batches.echelons', 'users')->orderBy('date', 'desc')->get();
            return response()->json(['student_payment_list' => $student_payment]);
        }

        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $role = 'admin';
        }

        if (!$request->to_date && $request->from_date && $role == 'admin') {
            $from_date = $request->from_date;
            $to_date = '2050-12-01';
        }
        if ($request->to_date && !$request->from_date && $role == 'admin') {
            $from_date = '2019-12-01';
            $to_date = $request->to_date;
        }
        if ($request->to_date && $request->from_date && $role == 'admin') {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
        }
        $student_payment = student_payment::whereDate('date', '>=', $from_date)->whereDate('date', '<=', $to_date);

        if ($request->batch_id && $role == 'admin') {
            $student_payment = $student_payment->whereHas('students.batches', function($query) use ($request){
                $query->where('id', $request->batch_id);
            });
        }

        if (!$request->batch_id && $request->echelon_id && $role == 'admin') {
            $student_payment = $student_payment->whereHas('students.batches', function($query) use ($request){
                $query->where('echelon_id', $request->echelon_id);
            });
        }
        // not the id no
        $ids = [682,706];
        $student_payment = $student_payment->whereNotIn('student_id', $ids)->with('students.batches.echelons', 'users')->get();
        return response()->json(['student_payment_list' => $student_payment]);
    }

    public function edit_student_payment_list(Request $request, $id)
    {
        $user = Auth::guard('admin')->user();
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            return response()->json(['msg' => 'You are not authorised.'], 422);
        }
        $student_payment = student_payment::find($id);
        if ($student_payment->amount < $request->paid) {
            return response()->json(['msg' => 'Paid amount can not be greater than amount.'], 422);
        }
        if (!$user->hasRole('superadmin')) {
            return response()->json(['msg' => 'Previous recrod can not be updated'], 422);
        }
        $student_payment->paid = $request->paid;
        $student_payment->amount = $request->amount;
        if ($user->hasRole('superadmin')) {
            $student_payment->date = $request->date;
        }
        $student_payment->next_payment_date = $request->next_payment_date;
        $student_payment->month = $request->year . '-' . $request->month . '-01';
        $student_payment->save();
    }

    public function get_student_payment($reg_no, $status){
        // student object to use in where clause
        $student = student::where('reg_no', $reg_no)->where('status', $status)->first();
        // if not found student
        if (!$student) {
            return response()->json(['msg' => 'Student not found'], 422);
        }
        // dont limit paymens data if course fee
        $limit = 3;
        if ($student->course_fee) {
            $limit = 10;
        }
        if ($student && !$student->inactive_month) {
            $student->inactive_month = '2050-01-01';
        }
        // student with payment where ruuning month grether than and inactive month less than data
        $student_payment = student::where('reg_no', $reg_no)->where('status', $status)->with('batches.echelons')->with(array('payments'=>function($query) use ($student, $limit){
            $query->whereDate('month', '>=', $student->running_month)->whereDate('month', '<=', $student->inactive_month)
            ->orderby('id', 'desc')->limit($limit);
        }))->first();
        // define data
        $student_payment->month_of_pay = Carbon::now()->month;
        $student_payment->year = Carbon::now()->year;
        // initialize data
        $due = 0;
        $total_paid = 0;
        $admission_fee = 0;
        $last_month_of_pay = null;
        $now = Carbon::now();
        $current_due = null;
        foreach ($student_payment->payments as $key => $payment) {
            if ($payment->status=='unpaid') {
                $student_payment->payment_status = 'has_unpaid_invoice';
                $student_payment->invoice_id = $payment->id;
                $student_payment->paid = $payment->paid;
                $student_payment->amount = $payment->amount;
                $student_payment->next_payment_date = $payment->next_payment_date;
                $student_payment->month_of_pay = Carbon::parse($payment->month)->month;
                $student_payment->year = Carbon::parse($payment->month)->year;
                $last_month_of_pay = Carbon::parse($payment->month)->subMonths($payment->month_count);
                $student_payment->payment_month_remain = $now->diffInMonths($last_month_of_pay);
            }
            if ($key == 0 && $student_payment->monthly_fee && $payment->status!='unpaid') {
                $data = $payment->month;
                $due = $payment->amount - $payment->paid;
                // last month of pay
                $last_month_of_pay = new Carbon($payment->month);
                $current_due = $last_month_of_pay->month == $now->month && $due > 0 ? true : false;
            }
            if ($student_payment->course_fee) {
                $total_paid = $total_paid + $payment->paid;
            }
        }
                
        if ($student_payment->monthly_fee && $student_payment->payment_status != 'has_unpaid_invoice') {
            $student_payment->payment_month_remain = $last_month_of_pay ? $now->diffInMonths($last_month_of_pay) : 100;
            $student_payment->payment_month_remain = $last_month_of_pay && $last_month_of_pay->gt($now) ? -$student_payment->payment_month_remain : $student_payment->payment_month_remain;
            // return response()->json($student_payment->payment_month_remain, 422);
            if ($student_payment->payment_month_remain == 100) {
                $student_payment->payment_status = "no_record";
                $student_payment->payment_month_remain = 1;
                $admission_fee = $student_payment->admission_fee;
                $last_month_of_pay = Carbon::parse($student_payment->running_month);
                $student_payment->month_of_pay = $last_month_of_pay->month;
                $student_payment->year = $last_month_of_pay->year;
            }
            if ($student_payment->payment_month_remain == 1 && $student_payment->payment_status != 'no_record') {
                $student_payment->payment_status = "unpaid";
            }
            // unpaid more than 1 month
            if ($student_payment->payment_month_remain > 1) {
                $student_payment->payment_status = "unpaid_multiple";
            }
            // advance pay
            if ($student_payment->payment_month_remain <= 0 && $student_payment->payment_status != "no_record" && !$current_due) {
                $student_payment->payment_status = "advance";
                $student_payment->payment_month_remain = 1;
                $student_payment->month_of_pay = $last_month_of_pay->month;
                $student_payment->year = $last_month_of_pay->year;
            }
            // due of this month
            if ($current_due) {
                $student_payment->due_payment = 1;
                $student_payment->payment_status = "current_due";
            }
            // calculat amount
            $student_payment->amount = ($student_payment->monthly_fee * $student_payment->payment_month_remain) + $due + $admission_fee;
            $student_payment->next_payment_date = Carbon::parse($student_payment->year. '-' . $student_payment->month_of_pay . '-01')->addMonths(1)->format('Y-m-d');
            $student_payment->last_month_of_pay = $last_month_of_pay->year . '-' . $last_month_of_pay->month . '-01';
            // if new student amount equal monthly fee
            if ($student_payment->amount == 0) {
                $student_payment->amount = $student_payment->monthly_fee;
            }
        }
        if ($student_payment->course_fee && $student_payment->payment_status != 'has_unpaid_invoice') {
            if (sizeof($student_payment->payments)==0) {
                $student_payment->payment_status = "no_record";
            }
            $student_payment->amount = $student_payment->course_fee + $student_payment->admission_fee - $total_paid;
            if ($student_payment->amount==0) {
                $student_payment->payment_status = "paid";
            }
        }
        return response()->json(['student_payment' => $student_payment]);
    }

    public function save_student_payment(Request $request)
    {
        // return response()->json($request->all(), 422);
        $payable_month = Carbon::parse($request->current_year. '-' . $request->current_month . '-01');
        $paid_month = Carbon::parse($request->year . '-' . $request->month . '-' . '01');
        $payable_remain_month = $request->payment_status== 'no_record' ? 1 : $request->payment_month_remain;

        if ($request->payment_status=='has_unpaid_invoice') {
            $student_payment = student_payment::find($request->invoice_id);
        }
        else {
            $student_payment = new student_payment;
        }
        $student_payment->student_id = $request->id;
        $student_payment->received_id = Auth::guard('admin')->user()->id;
        // $student_payment->received_id = 8;
        $student_payment->amount = $request->amount;
        $student_payment->paid = $request->paid;
        $student_payment->month = $request->year . '-' . $request->month . '-' . '01';
        $student_payment->status = $request->status;        
        $student_payment->date = Carbon::now()->format('Y-m-d');
        $student_payment->month_count = $paid_month->diffInMonths($payable_month) + $payable_remain_month;
        $student_payment->next_payment_date = Carbon::parse($request->show_next_payment_date)->format('Y-m-d');
        $student_payment->save();
        // sms from payment trait ============
        if ($request->sms_check) {
           $this->payment_sms($request->id, $request->month, $student_payment);
        }

        return response()->json($student_payment);
    }

    public function delete_student_payment($id)
    {
        $user = Auth::guard('admin')->user();
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            return response()->json(['msg' => 'You are not authorised.'], 422);
        }
        student_payment::find($id)->delete();
    }

    // teacher payment

    public function get_teacher_payment(Request $request, $id){

        $teacher = teacher::find($id);
        // teacher attendance list
        $first_of_month = Carbon::parse($request->year . '-' . $request->month . '-01')->format('Y-m-d');
        $end_of_month = Carbon::parse($first_of_month)->endOfMonth();

        $teacher_attendance = schedule_teacher_attendance::where('teacher_id', $id)->whereHas('schedules', function($query) use ($first_of_month, $end_of_month){
            $query->whereDate('date', '>=', $first_of_month)->whereDate('date', '<=', $end_of_month);
        })->orderBy('type')->with('batches', 'exam_lists')->get();

        // teacher previous payments if not due of month
        $teacher_payment = teacher_payment::where('teacher_id', $teacher->id)->orderBy('id', 'desc')->whereDate('month', '>=', '2021-01-01')->limit(3)->get();

        $paid_amount = 0;
        foreach ($teacher_payment as $key => $payment) {                
            if ($payment->month == $first_of_month) {
                $paid_amount = $paid_amount + $payment->paid;
            }
        }
        
        // neccesary variable
        $class_payment = [];
        $invigilator_payment = [];
        $script_payment = [];
        $class_payment_total = 0;
        $invigilator_payment_total = 0;
        $script_payment_total = 0;    
        // due and paid
        $due_amount = sizeof($teacher_payment) ? $teacher_payment[0]->amount - $teacher_payment[0]->paid : 0;
        $previous_due = sizeof($teacher_payment) > 1 ? $teacher_payment[1]->amount - $teacher_payment[1]->paid : 0;
        $payment_status = sizeof($teacher_payment) && $teacher_payment[0]->month == $first_of_month ? 'due'  : 'regular';

        $payment_msg = '';
        if ($payment_status == 'due' && $due_amount > 0) {
            $payment_msg = 'It is a due payment';
        }
        if ($payment_status == 'regular' && $due_amount > 0) {
            $payment_msg = 'Has previous month due.';
        }
        // loop each record and reform
        foreach ($teacher_attendance as $key => $teacher_list) {
            if ($teacher_list->type == 'teacher') {
                $index = -1;
                // find batch has not normal payment (find index in per_class payment table)
                foreach($teacher->per_class_payments as $p_c_p => $per_class_payment) {
                    if ( !$per_class_payment->batch_id ){$index = $p_c_p;}
                    if ( $per_class_payment->batch_id === $teacher_list->batch_id ){
                        $index = $p_c_p; 
                        break;
                    }
                }
                // find this payment amount info already push a
                $p_index = -1;
                foreach($class_payment as $c_p => $list) {
                    if ($list->new_batch_id == $teacher->per_class_payments[$index]->batch_id) {
                        $p_index = $c_p;
                    }
                }
                if ($p_index == -1) {
                    $teacher_list->new_batch_id = $teacher->per_class_payments[$index]->batch_id;
                    $teacher_list->amount = $teacher->per_class_payments[$index]->amount;
                    $teacher_list->count_class = 1;
                    $teacher_list->batch_name = $teacher_list->new_batch_id ? $teacher_list->batches->name : '';
                    $class_payment_total = $class_payment_total + $teacher->per_class_payments[$index]->amount; 
                    array_push($class_payment, $teacher_list);
                } else {
                    $class_payment[$p_index]->amount = $class_payment[$p_index]->amount + $teacher->per_class_payments[$index]->amount;
                    $class_payment[$p_index]->count_class = $class_payment[$p_index]->count_class + 1;
                    $class_payment_total = $class_payment_total + $teacher->per_class_payments[$index]->amount;
                }
            }
            if ($teacher_list->type == 'invigilator') {
                $hours = floor($teacher_list->invigilator_minute / 60);
                $minutes = $teacher_list->invigilator_minute % 60;
                $half_hour = floor($minutes / 30);
                $quarter_hour = $minutes % 30;
                // if minutes greater than 15 count half_hour
                if ($quarter_hour > 15) {
                    $half_hour = $half_hour + 1;
                }
                $total_hours = $hours + (0.5 * $half_hour);
                
                if (sizeof($invigilator_payment)>0) {
                    $invigilator_payment[0]->amount = $invigilator_payment[0]->amount + ($teacher->invigilator_payments ? $teacher->invigilator_payments->per_hour_amount * $total_hours : 0);
                    $invigilator_payment[0]->count_class = $invigilator_payment[0]->count_class + 1;
                    $invigilator_payment[0]->total_minutes = $invigilator_payment[0]->total_minutes + $teacher_list->invigilator_minute;
                    $invigilator_payment_total = $invigilator_payment_total + ($teacher->invigilator_payments ? $teacher->invigilator_payments->per_hour_amount * $total_hours : 0);
                }
                else {
                    $teacher_list->amount = $teacher->invigilator_payments ? $teacher->invigilator_payments->per_hour_amount * $total_hours : 0;
                    $teacher_list->count_class = 1;
                    $teacher_list->total_minutes = $teacher_list->invigilator_minute;
                    array_push($invigilator_payment, $teacher_list);
                    $invigilator_payment_total = $invigilator_payment_total + $teacher_list->amount;
                }
            }
            if ($teacher_list->type == 'scripter') { 
                // mark for script
                $mark = $teacher_list->exam_lists->sub_full_mark; 
                // order script payment by mark into marks range
                $script_payment_by_mark = $teacher->script_payments()->orderby('marks_range')->get();                
                // find marks rannge
                $s_index = -1;
                foreach($script_payment_by_mark as $s_p => $list) {
                    if ($list->marks_range == $mark) {
                        $s_index = $s_p;
                        break;
                    }
                    if ($list->marks_range > $mark) {
                        $s_index = $s_p != 0 ? $s_p - 1 : 0;
                        break;
                    }
                }
                // if no marks range found
                if ($s_index == -1) {
                    $amount = 0;
                    $teacher_list->mark = $mark;
                    $teacher_list->amount = $amount;
                } else {                    
                    $amount = $script_payment_by_mark[$s_index]->amount * $teacher_list->script_quantity;
                    $teacher_list->mark = $mark;
                    $teacher_list->amount = $amount;
                    $teacher_list->count_script = $teacher_list->script_quantity;
                }
                // find script payment of this marks already push
                $index = -1;
                foreach($script_payment as $s_p => $list) {
                    if ($list->mark == $mark) {
                        $index = $s_p;
                    }
                }
                if ($index>-1) {
                    $script_payment[$index]->amount = $script_payment[$index]->amount + $amount;
                    $script_payment[$index]->count_script = $script_payment[$index]->count_script + $teacher_list->script_quantity;
                }
                else {
                    array_push($script_payment, $teacher_list);
                }
                $script_payment_total = $script_payment_total + $amount;
            }
        }

        // final total
        if (sizeof($teacher_payment) && $teacher_payment[0]->amount == $teacher_payment[0]->paid) {
            $final_total = 0;
        }
        elseif ($payment_status == 'due') {
            $final_total = ($class_payment_total + $invigilator_payment_total + $script_payment_total + $previous_due) - $paid_amount;
        } else {
            // on regular $due_amount will be the previous due
            $final_total = $class_payment_total + $invigilator_payment_total + $script_payment_total + $due_amount;
        }

        return response()->json(['teacher_payment' => $teacher_payment, 'class_payment' => $class_payment, 'invigilator_payment' => $invigilator_payment, 'script_payment' => $script_payment, 'due_amount' => $due_amount, 'class_payment_total' => $class_payment_total, 'invigilator_payment_total' => $invigilator_payment_total, 'script_payment_total' => $script_payment_total, 'final_total' => $final_total, 'payment_msg' => $payment_msg]);
    }

    public function save_teacher_payment(Request $request){
        $user = Auth::guard('admin')->user();
        $from = $request->date_from;
        $to = $request->date_to;
        // $payment = teacher_payment::where('teacher_id', $request->teacher_id)->whereBetween('date_from', [$from, $to])->whereBetween('date_to', [$from, $to])->first();
        // if ($payment) {
        //     return response()->json(['msg' => 'Payment of given date already exist.'], 422);
        // }
        $payment = new teacher_payment;
        $payment->month = $request->year. '-' . $request->month . '-01';
        // $payment->date_to = $to;
        $payment->teacher_id = $request->teacher_id;
        $payment->payer_id = $user->id;
        $payment->amount = $request->amount;
        $payment->paid = $request->paid;
        $payment->status = $request->status;
        $payment->save();
    }

    public function get_unpaid_student()
    {
        $now_month = Carbon::now()->month;
        $now_year = Carbon::now()->year;            
        $now_date = $now_year . '-' . $now_month . '-' . '01';
        // where('inactive_month', '>=', Carbon::now()->startOfMonth())->orWhereNull('inactive_month')
        $unpaid_student_1 = student::whereDate('running_month', '<=', Carbon::today())->where('status', 'present')
        ->whereHas('payments', function($query){
            $query->whereDate('month', '>=', Carbon::now()->startOfMonth()->subMonths(4))->whereDate('month', '<=', Carbon::now()->startOfMonth())
            ->where(function($query){
                $query->OrWhereDate('next_payment_date', '<=', Carbon::now()->startOfMonth());
            });
        })->with(array('payments' => function($query){
                $query->whereDate('month', '>=', Carbon::now()->startOfMonth()->subMonths(3))->orderBy('id', 'DESC');
        }))->with('batches.echelons')->where('student_type', 1)->select('id', 'reg_no', 'name', 'mobile', 'mobile2', 'monthly_fee', 'course_fee', 'student_type', 'status', 'running_month', 'inactive_month', 'batch_id')->get();

        $unpaid_student_2 = student::WhereDoesntHave('payments')->where('student_type', 1)->where('status', 'present')->select('id', 'reg_no', 'name', 'mobile', 'mobile2', 'monthly_fee', 'course_fee', 'student_type', 'status', 'running_month', 'inactive_month', 'batch_id')->with('batches.echelons')->get();

        $unpaid_student = [];
        foreach ($unpaid_student_1 as $key => $student) {
            array_push($unpaid_student, $student);
        }
        foreach ($unpaid_student_2 as $key => $student) {
            array_push($unpaid_student, $student);
        }  
              
        $students = [];
        $month_diff = 0;

        foreach ($unpaid_student as $key => $student) {
            $due = 0;
            if (sizeof($student->payments)) {                
                $month_diff = Carbon::now()->month - Carbon::parse($student->payments[0]->month)->month;
                $month_diff = $month_diff;
                $student->month_diff = $month_diff;
                if ($student->monthly_fee) {
                    $student->amount = $student->monthly_fee + ($student->payments[0]->amount - $student->payments[0]->paid);
                }
                if ($student->course_fee) {
                    $student->amount = $student->payments[0]->amount - $student->payments[0]->paid;
                } 
                if ($month_diff == 0 && $student->monthly_fee) {                
                   $due = $student->payments[0]->amount - $student->payments[0]->paid;
                   $student->amount = $due;
                }               
            } else {
                $month_diff = 1;
                $student->month_diff = 1;
                if ($student->monthly_fee) {
                    $student->amount = $student->monthly_fee;
                }
                if ($student->course_fee) {
                    $student->amount = $student->course_fee;
                }
            }
            if ($month_diff>0 || $due > 0) {
                array_push($students, $student);
            }
        }

        return response()->json(['unpaid_student' => $students]);
    }

    public function send_sms_to_unpaid(Request $request)
    {
        $student = [];
        foreach ($request->payment_by_batch as $key => $payment) {
            foreach ($payment['payments'] as $p => $list) {
                array_push($student, $list);
            }
        }        
        // create sms json array
        $json_array = [];
        // $sms_single_month = "Dear guardian, April masher payment bokeya thakai amader karjokrom behoto hoye poreche. Tai druto beton porishod korar jonno bishesh onurod korchi. BT.";
        $sms_single_month = "Dear Guardian, ai masher beton porishod kora hoini. Druto beton porishod korar onurodh kora hocche.";
        $sms_multiple_month_pre_text = "Dear Guardian, ";
        $sms_multiple_month_next_text = " masher beton porishod kora hoini. Druto beton porishod korar onurodh kora hocche.";
        $text = explode("[student_name]", $request->sms);
        foreach ($student as $key => $std) {
            $json_data = new \stdClass;
            $json_data->MobileNumber =  $std['mobile'];
            $json_data->SmsText = $std['month_diff'] > 1 ? $sms_multiple_month_pre_text . $std['month_diff'] . $sms_multiple_month_next_text : $sms_single_month;
            $json_data->Type = "TEXT";
            array_push($json_array, $json_data);
        }
        // return $json_array;
        if (sizeof($json_array)==0) {
            return response()->json(['sms' => 'none']);        
        }        
        $sms_report = $this->onnorokom_list_sms($student, $request, $json_array);
        $balance = $this->onnorokom_balance_for_list();
        return response()->json(['sms_report' => $sms_report, 'balance' => $balance, 'sms' => 'sms']);
    }

    public function payment_assign_to_admin($invoice_id, $admin_id){
        // s - 6, f-1
        if (Auth::guard('admin')->user()->hasRole('superadmin')) {
            $payment = student_payment::find($invoice_id);
            $payment->received_id = $admin_id;
            $payment->save();
        }
    }

    // 10615, 9205
}
