<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use \Carbon\Carbon;
use App\Model\Admin\teacher;
use App\Model\Admin\teacher_payment;
use App\Model\Admin\schedule_teacher_attendance;
use App\Model\Admin\random_number;
use Cache;

// Contains
// User Login Success Home Page
// User Dashboard


class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
        // $this->middleware('admin');
    }    

    public function get_payment_list()
    {
        $user = Auth::guard('web')->user();
        $teacher = $user->teachers;
        $payment = teacher_payment::where('teacher_id', $teacher->id)->OrderBy('id', 'desc')->with('teachers')->limit(3)->get();
        return response()->json(['payment_list' => $payment]);
    }

    public function get_payment_detail(Request $request){

        $user = Auth::guard('web')->user();
        $teacher = $user->teachers;
        // teacher attendance list
        $teacher_attendance = schedule_teacher_attendance::where('teacher_id', $teacher->id)->whereHas('schedules', function($query) use ($request){
            $query->whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to);
        })->orderBy('type')->with('batches', 'exam_lists')->get();
        // teacher previous payments
        $teacher_payment = teacher_payment::where('teacher_id', $teacher->id)->where('id','<', $request->id)->orderBy('id', 'desc')->first();
        
        // neccesary variable
        $class_payment = [];
        $invigilator_payment = [];
        $script_payment = [];
        $class_payment_total = 0;
        $invigilator_payment_total = 0;
        $script_payment_total = 0;        
        $due_amount = $teacher_payment ? $teacher_payment->amount - $teacher_payment->paid : 0;

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
                // find this payment amount info already push
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
                    $teacher_list->batch_name = $list->new_batch_id ? $teacher_list->batches->name : ''; 
                    array_push($class_payment, $teacher_list);
                } else {
                    $class_payment[$p_index]->amount = $class_payment[$p_index]->amount + $teacher->per_class_payments[$index]->amount;
                    $class_payment[$p_index]->count_class = $class_payment[$p_index]->count_class + 1;
                }
                $class_payment_total = $class_payment_total + $teacher_list->amount;
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
                }
                else {
                    $teacher_list->amount = $teacher->invigilator_payments ? $teacher->invigilator_payments->per_hour_amount * $total_hours : 0;
                    $teacher_list->count_class = 1;
                    $teacher_list->total_minutes = $teacher_list->invigilator_minute;
                    array_push($invigilator_payment, $teacher_list);
                }
                $invigilator_payment_total = $invigilator_payment_total + $teacher_list->amount;
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
        return response()->json(['teacher_payment' => $teacher_payment, 'class_payment' => $class_payment, 'invigilator_payment' => $invigilator_payment, 'script_payment' => $script_payment, 'due_amount' => $due_amount, 'class_payment_total' => $class_payment_total, 'invigilator_payment_total' => $invigilator_payment_total, 'script_payment_total' => $script_payment_total]);
    }

    public function confirm_payment($id){
        $payment = teacher_payment::where('id', $id)->update(['status' => 'confirmed']);
    }
    
}
