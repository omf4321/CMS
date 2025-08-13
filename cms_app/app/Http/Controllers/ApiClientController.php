<?php

namespace App\Http\Controllers;
use App\Client;
use App\Http\Controllers\payment\PaymentTrait;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\student;
use App\Model\Admin\student_attendance;
use App\Model\Admin\student_exam_mark;
use App\Model\Admin\student_payment;
use Auth;
use Cache;
use Carbon\Carbon;
use File;
use Hash;
use Illuminate\Http\Request;
use Intervention;
use DB;


class ApiClientController extends Controller
{
    
    use PaymentTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:client');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_schedule($student_id, $client_update_time)
    {        
        // declare all variable for on going class, update profile, allow join to class, payment alert ; 
        $on_going_class = new \StdClass;
        $updated = "false";
        $allow_join_class = "true";
        $payment_alert = "false";
        $payment_message = Null;
        $allow_attendance = "true";

        $student = student::where('id', $student_id)->select('id', 'reg_no', 'batch_id', 'name', 'client_id', 'mobile2', 'father_name', 'mother_name', 'address', 'date_of_admit', 'date_of_birth', 'photo', 'updated_at', 'student_type')->first();

        $student_batch_id = $student->batch_id;

        $daily_schedule = daily_schedule::where('batch_id', $student_batch_id)->whereNotNull('subject_id')->whereNotNull('schedule_type')->where('schedule_type', '!=', 'close')->with('subjects', 'teachers', 'batches')->whereDate('date', '>=', Carbon::today())->whereDate('date', '<=', Carbon::today()->addDays(7))->orderBy('date')->get(); 

        // today schedule ******************************
        $today_schedule = $daily_schedule->where('date', Carbon::today()->format('Y-m-d'));
        $today_schedule_array = [];        
        foreach ($today_schedule as $key => $item) {
            $object = new \StdClass;
            $object->subject = $item->subjects->name;
            $object->period = $this->ordinal($item);
            $object->class_info = $item->schedule_type == 'class' ? 'Offline Class' : ucwords((str_replace("_", " ", $item->schedule_type))) . ', ' . Carbon::parse($item->time)->format("h:i A");
            $object->teacher = $item->teachers->name;
            $object->online_class_url = $item->online_class_url;
            array_push($today_schedule_array, $object);
            // get on going class object
            if ($item->online_class_status == 'begin' && Carbon::now() <= Carbon::parse($item->time)->addMinutes(30)) {
                $on_going_class = $object;
            }
        }
        // next schedule ******************************
        $next_schedule = $daily_schedule->where('date', '>', Carbon::today()->format('Y-m-d'))->where('date', '<=', Carbon::today()->addDays(7)->format('Y-m-d'));

        $next_schedule_array = [];
        foreach ($next_schedule as $key => $item) {
            $object = new \StdClass;
            $object->subject = $item->subjects->name;
            $object->period = Carbon::parse($item->date)->format('d M');
            $object->class_info = $item->schedule_type == 'class' ? 'Offline Class' : ucwords((str_replace("_", " ", $item->schedule_type))) . ', ' . $this->ordinal($item) . ' Period, ' . Carbon::parse($item->time)->format("h:i A");
            $object->teacher = $item->teachers->name;
            array_push($next_schedule_array, $object);            
        }

        // check profile update time and send data of student profile ******************
        $client_update_time = Carbon::parse($client_update_time);
        $server_update_time = Carbon::parse($student->updated_at);
        $student_data = Null;
        if ($server_update_time->gt($client_update_time)) {
            $client = Client::find($student->client_id);
            $student->batch_name = $student->batches->name;
            $student->email = $client->email ? $client->email : "";
            $student->dob = Carbon::parse($student->date_of_birth)->format('d M y');
            $student->profile_pic_url = env('APP_URL') . '/storage/avatar/' . $student->batch_name . '/' . $student->photo;
            $updated = "true";
            $student_data = $student;
        }

        // check payment *********************
        $payment = $this->last_payment_of_student($student);
        if ($payment == "unpaid") {
            $payment_alert = "true";
            $payment_message = "You didn't pay for " . Carbon::now()->format('F Y') . '. Please pay as soon as possible.';
        }

        return response()->json(['today_schedule' => $today_schedule_array, 'next_schedule' => $next_schedule_array, 'on_going_class' => $on_going_class, 'updated' => $updated, 'user' => $student_data, 'payment_alert' => $payment_alert, 'payment_message' => $payment_message, 'allow_join_class' => $allow_join_class, 'allow_attendance' => $allow_attendance]);
    }

    protected function ordinal($item) {
        if ($item->online_class_status == 'begin') {
            return 'Start';
        }
        if ($item->online_class_status == 'finish') {
            return 'End';
        }
        $number = $item->period;
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)){
            return $number. 'th';
        }
        else{
            return $number. $ends[$number % 10];
        }
    }

    public function update_profile(Request $request, $student_id) {
        // return response()->json($request->all());
        $student = student::find($student_id);
        if ($request->request_type == 'update_profile') {
            // client info
            $user = Client::where('id', $student->client_id)->first();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();
            // student info
            $student->name = $request->name;
            $student->mobile2 = $request->mobile2;
            $student->date_of_birth = Carbon::parse($request->dob)->format('Y-m-d');
            $student->father_name = $request->father_name;
            $student->mother_name = $request->mother_name;
            $student->address = $request->address;
            $student->save();

            $student->email = $user->email;
            $student->dob = Carbon::parse($user->date_of_birth)->format('d M y');

            return response()->json(['message' => 'Update Successfully', 'user' => $student]);
        }

        if ($request->request_type == 'upload_profile_pic') {
            $image = $request->profile_pic;
            
            $image = base64_decode($image);
            $img = Intervention::make($image);
            
            if ($img->width()>155) {
                $img->resize(155, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $filename = str_random(16). '.jpg';
            //return response()->json(['message' => $filename]);
            $batch_name = $student->batches->name;
            $path = public_path('storage/avatar/'.$batch_name);
            if (!file_exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            File::delete($path . '/' . $student->photo);
            $img->save($path . '/' . $filename);
            student::where('id', $student_id)->update(['photo'=>$filename]);
            Cache::flush();

            return response()->json(['message' => 'Profile pic upload successfully', 'file_path' => env('APP_URL') . '/storage/avatar/' . $batch_name . '/' . $filename]);
        }
    }

    public function update_password(Request $request, $student_id)
    {
        // return $request->all();
        $student = student::find($student_id);
        $user = Client::find($student->client_id);

        if (!(Hash::check($request->get('current_password'), $user->password))) {
            // The passwords matches
            return response()->json(['message' => 'Current password does not matches with the password you provided. Please try again.', 'error' => "true"]);
        }
 
        if(strcmp($request->get('current_password'), $request->get('new_password')) == 0){
            //Current password and new password are same
            return response()->json(['message' => 'New Password cannot be same as your current password. Please choose a different password.', 'error' => "true"]);
        }
 
        $this->validate($request,[
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);
 
        //Change Password
        $user->password = bcrypt($request->get('new_password'));
        $user->password_text = $request->get('new_password');
        $user->save();
        Cache::flush();
        return response()->json(['message' => 'Password changed succesfully.', 'error' => "false"]);
    }

    public function take_attendance($student_id)
    {
        $attendance = student_attendance::where('student_id', $student_id)->whereDate('date', Carbon::today())->update(['attendance' => 1]);
        return response()->json($attendance);
    }

    public function get_performance($student_id)
    {
        $student = student::find($student_id);
        // ***** Attendance *********
        $attendance = student_attendance::where('student_id', $student_id)->whereDate('date', '>=', $student->running_month)->limit(7)->orderBy('date', 'desc')->select('id', 'student_id', DB::raw('CASE WHEN attendance = 0 THEN "Absent" ELSE "Present" END as attendance'), DB::raw('DATE_FORMAT(date, "%d %b %y") as date'))->get();

        // ******** exam record ======
        $exam = student_exam_mark::where('student_id', $student_id)->whereDate('date', '>=', $student->running_month)->limit(7)->orderBy('date', 'desc')->select('id', 'student_id', 'subject_id', 'ob_mark', 'sub_mark', DB::raw('DATE_FORMAT(date, "%d %b %y") as date'), DB::raw('CASE WHEN sub_mark IS NULL && ob_mark IS NULL THEN "Absent" ELSE "Present" END as attendance'))->with('subjects')->get();

        // ******** exam record ======
        $payment = student_payment::where('student_id', $student_id)->whereDate('date', '>=', $student->running_month)->limit(3)->orderBy('date', 'desc')->select('id', 'student_id', 'paid', 'amount', 'status', DB::raw('amount - paid as due'),  DB::raw('DATE_FORMAT(date, "%d %b %y") as date'), DB::raw('DATE_FORMAT(month, "%b %y") as month'), DB::raw('DATE_FORMAT(next_payment_date, "%d %b %y") as next_payment_date'))->get();

        // attendance filter options
        $attendance_filter = ['attendance_filter_option_1' => 'Previous Month', 'attendance_filter_option_2' => 'This Month'];
        // Exam filter options
        $exam_filter = ['exam_filter_option_1' => 'Previous Month', 'exam_filter_option_2' => 'This Month'];


        return response()->json(['attendance' => $attendance, 'exam' => $exam, 'payment' => $payment, 'attendance_filter' => $attendance_filter, 'exam_filter' => $exam_filter]);
    }

    
}
