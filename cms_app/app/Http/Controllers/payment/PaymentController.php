<?php

namespace App\Http\Controllers\payment;

use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Admin\student;
use App\Model\Admin\teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Admin\student_payment;
use App\Model\Admin\teacher_payment;
use App\Http\Controllers\sms\sms_trait;
use App\Repositories\PaymentRepository;
use App\Http\Controllers\payment\PaymentTrait;
use App\Repositories\TeacherPaymentRepository;
use App\Model\Admin\schedule_teacher_attendance;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use PaymentTrait;
    protected $paymentRepo;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->middleware('auth:admin');
        $this->paymentRepo = $paymentRepo;
    }

    public function get_student_payment_list(Request $request)
    {
        return $this->paymentRepo->getStudentPaymentList($request);
    }

    public function edit_student_payment_list(Request $request, $id)
    {
        return $this->paymentRepo->editStudentPaymentList($request, $id);
    }

    public function get_student_payment($reg_no, $status)
    {
        return $this->paymentRepo->getStudentPayment($reg_no, $status);
    }

    public function save_student_payment(Request $request)
    {
        return $this->paymentRepo->saveStudentPayment($request);
    }

    public function delete_student_payment($id)
    {
        return $this->paymentRepo->deleteStudentPayment($id);
    }

    // teacher payment
    public function get_teacher_payment(Request $request, $id, TeacherPaymentRepository $repo){

        return response()->json($repo->getTeacherPayment($request, $id));
    }

    public function save_teacher_payment(Request $request, TeacherPaymentRepository $repo)
    {
        return response()->json($repo->saveTeacherPayment($request));
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
