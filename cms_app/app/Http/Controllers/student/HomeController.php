<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use \Carbon\Carbon;
use App\Model\Admin\teacher;
use App\Model\Admin\random_number;
use Cache;

// Contains
// User Login Success Home Page
// User Dashboard


class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:client');
        // $this->middleware('admin');
    }

    public function index()
    {
    	$user = Auth::guard('web')->user();
        $teacher = $user->teachers;
        $roles = $user->getRoleNames();   
        $authorise = 'authorised';    
        $branch = \App\Model\Admin\branch::find($user->branch_id)->get();
        $echelons = Cache::rememberForever("echelons_" . $user->branch_id, function () use ($user) {
            $result = \App\Model\Admin\echelon::where('branch_id', $user->branch_id)->get();
            return $result;
        }); 
        return view('user.user-home', compact('roles', 'teacher', 'authorise', 'user', 'branch', 'echelons'));
    }

    protected function authorise_teacher()
    {
        $current_time = Carbon::now();
        $authorise = 'false';
        // if has authorise time
        if ($teacher->authentication_time) {
            $authentication_time = Carbon::parse($teacher->authentication_time);
            // is authorise time is today and greater than current time he already authorised
            if ($authentication_time->gte(Carbon::today()) && $current_time->lt($authentication_time)) {
                $authorise = 'authorised';
            }
            else {
                $schedule = $teacher->todays_schedule()->with('batches')->get();
                if (sizeof($schedule)) {                    
                    $authorise = 'true';
                }
            }
        }
        // if authorise time is null
        if (!$teacher->authentication_time) {
            $schedule = $teacher->todays_schedule()->with('batches')->get();
            if (sizeof($schedule)) {                    
                $authorise = 'true';
            }
        }
        // if has schedule and authentication time expired
        if ($authorise == 'true') {
            $schedule = $teacher->todays_schedule()->with('batches')->get();     
            $batch_time = [];
            $today = Carbon::now()->format('Y-m-d');
            foreach ($schedule as $key => $sch) {
                foreach ($sch->batches as $key1 => $batch) {
                    $now_time = $today . ' ' . $batch->time;
                    array_push($batch_time, $now_time);
                }
            }

            rsort($batch_time);
            $now = Carbon::now();  
            foreach ($batch_time as $b_t => $time) {        
                if (Carbon::parse($time)->subHours(1)->lte($now)) {
                    $cal_time = Carbon::parse($time);
                    break;
                }
            }
            if (isset($cal_time) && $cal_time->addHours(3)->gt($current_time)) {                
                teacher::where('id', $teacher->id)->update(['authentication_time' => $cal_time->format('Y-m-d H:i:s')]);
                $authorise = 'authorised';
            }
        }

        if ($teacher->act_as_teacher == 1) {
            $authorise = 'authorised';
        }
    }

    public function update_authentication($code)
    {
        $random = random_number::where('random_number', $code)->first();        
        if ($random) {
            $user = Auth::guard('web')->user();
            $teacher = $user->teachers;
            $current_time = Carbon::now();
            teacher::where('id', $teacher->id)->update(['authentication_time' => $current_time->addHours(2)->format('Y-m-d H:i:s')]);
        }
        else {
            return response()->json(['msg'=>'Code do not match'], 422);
        }
    }

    public function act_as_teacher()
    {
        $user = Auth::guard('web')->user();
        $teacher = $user->teachers;
        if ($teacher->act_as_teacher == 1) {
            Auth::guard('web')->logout();            
            return redirect('/admin/login');
        }
    }
    
}
