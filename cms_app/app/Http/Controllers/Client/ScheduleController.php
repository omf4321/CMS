<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\exam_import;
use App\Model\Admin\exam_question_list;
use App\Model\Admin\exam_question_type;
use App\Model\Admin\question;
use App\Model\Admin\student;
use App\Model\Admin\online_exam_detail;
use App\Model\Admin\student_online_exam;
use App\User;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Intervention;
use App\Http\Controllers\attendance\AttendanceTrait;
use App\Http\Controllers\payment\PaymentTrait;

// Contains
// User Login Success Home Page
// User Dashboard


class ScheduleController extends Controller
{
    use AttendanceTrait, PaymentTrait;

    public function __construct()
    {
        $this->middleware('auth:client');
        // $this->middleware('admin');
    }

    public function get_schedule()
    {
        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)->first();
        $student_batch_id = $student->batch_id;
        $daily_schedule = daily_schedule::where('batch_id', $student_batch_id)->whereNotNull('subject_id')->whereNotNull('schedule_type')->where('schedule_type', '!=', 'close')->with('subjects', 'teachers', 'batches')->whereDate('date', '>=', Carbon::today())->whereDate('date', '<=', Carbon::today()->addDays(7))->get(); 
        $payment = $this->last_payment_of_student($student);       
        return response()->json(['daily_schedule' => $daily_schedule, 'payment' => $payment]);
    }

    public function get_question($type, $limit)
    {
        $limit = 5;
        $question = question::whereHas('chapters', function($query){
            $query->where('id', 424);
        })->where('question_type', $type)->limit($limit)->get();
        return response()->json(['question' => $question]);
    }

    public function get_question_type($id){

        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)
        ->with(array('online_exams' => function($query) use ($id){
            $query->where('schedule_id', $id);
        }))->first();

        $question = exam_question_list::whereHas('schedules', function($query) use($id){
            $query->where('id', $id);
        })->with('exam_question_types.questions', 'exam_types')
        ->with(array('schedules' => function($query) use ($id){
            $query->where('id', $id)->with('subjects');
        }))->first();

        return response()->json(['question' => $question, 'student' => $student]);
    }

    public function upload_script(Request $request)
    {
        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)->first();
        if($request->hasFile('file')){
            $files = $request->file('file');
            $image = $files;
            $filename = str_random(16). '.' . $image->getClientOriginalExtension();
            $img = Intervention::make($image);
            if ($img->height()>$img->width() && $img->height()>960) {
                $img->resize(null, 960, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            elseif ($img->height() <= $img->width() && $img->width()>650) {
                $img->resize(650, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $path = public_path('image/script');
            if (!file_exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            $img->save($path . '/' . $filename );

            $online_exam = student_online_exam::where('student_id', $student->id)->where('schedule_id', $request->schedule_id)->first();

            $exam_import = new online_exam_detail;
            $exam_import->student_online_exam_id = $online_exam->id;
            $exam_import->filename = '/image/script/' . $filename;
            $exam_import->type = 'cq';
            $exam_import->save();
            return $filename;
        }
    }

    public function finish_mcq_exam(Request $request)
    {
        // return $request->all();
        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)->first();

        $online_exam = student_online_exam::where('student_id', $student->id)->where('schedule_id', $request->schedule_id)->first();
        if (!$online_exam) {
            $online_exam = new student_online_exam;
        }
        $online_exam->schedule_id = $request->schedule_id;
        $online_exam->student_id = $student->id;
        $online_exam->status = 'mcq_finish';
        $online_exam->save();
        // $schedule = daily_schedule::where('id', $request->schedule_id)->update(['online_exam_status' => 'mcq_finish']);
        foreach ($request->questions as $key => $question) {
            $exam_import = online_exam_detail::firstOrCreate(
                ['student_online_exam_id' => $online_exam->id, 'question_id' => $question['id']],
                ['student_online_exam_id' => $online_exam->id, 'question_id' => $question['id'], 'question_no' => $key + 1, 'option' => $question['given_answer'], 'key' => $question['answer'], 'marks' => 1, 'type' => 'mcq']
            );
        }
    }

    public function submit_script(Request $request)
    {
        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)->first();
        $online_exam = student_online_exam::where('student_id', $student->id)->where('schedule_id', $request->schedule_id)->first();

        foreach ($request->fileinfolist as $key => $file) {
            $exam_import = online_exam_detail::where('student_online_exam_id', $online_exam->id)->where('filename', 'like', '%' . $file['response'] . '%')->first();
            $exam_import->question_no = $file['page_number'];
            $exam_import->save();
        }

        $online_exam->status = 'submit';
        $online_exam->save();
        // $schedule = daily_schedule::where('id', $request->schedule_id)->update(['online_exam_status' => 'submit']);
    }

    public function save_attendance()
    {
        $user = Auth::guard('client')->user();
        $student = student::where('client_id', $user->id)->first();        
        $this->save_single_student_attendance($student, 1);
        return 'attendance ok';
    }


}
