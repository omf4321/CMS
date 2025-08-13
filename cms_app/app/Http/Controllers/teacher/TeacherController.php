<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Model\Admin\invigilator_payment;
use App\Model\Admin\per_class_payment;
use App\Model\Admin\script_payment;
use App\Model\Admin\teacher;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function teacher_list()
    {        
        $teacher_list = teacher::with('branches', 'users.roles', 'per_class_payments', 'script_payments', 'invigilator_payments')->orderby('name', 'asc')->get();
        return response()->json(["teacher_list" => $teacher_list]);
    }

    public function teacher_add(Request $request)
    {
        // return response()->json($request->all());
        // return in_array('teacher', $request->roles);
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => 'required',
            'mobile' => 'required',
            'roles' => 'required'
        ]);

        DB::beginTransaction();
        try {
            // $password = bcrypt(mt_rand(100000,999999));
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->status = 'active';
            $user->branch_id = $request->branch_id;
            $user->save();
            $user->assignRole($request->roles);

            $teacher = new teacher;
            $teacher->name = $request->name;
            $teacher->gender = $request->gender;
            $teacher->branch_id = $request->branch_id;
            $teacher->user_id = $user->id;
            $teacher->mobile = $request->mobile;
            $teacher->mobile2 = $request->mobile2;
            $teacher->address = $request->address;
            $teacher->salary_type = $request->salary_type;
            $teacher->status = $request->status;
            $teacher->roles = implode (", ", $request->roles);
            if ($request->salary_type == 'monthly') {
                $teacher->monthly_salary = $request->monthly_salary;
                $teacher->salary_start = $request->salary_start . '.' . $request->year;
            }
            $teacher->join_date = $request->join_date;
            $teacher->save();

            if (in_array('teacher', $request->roles)) {
                foreach ($request->payments as $key => $payment) {
                    $per_class_payment = new per_class_payment;
                    $per_class_payment->teacher_id = $teacher->id;
                    $per_class_payment->amount = $payment['amount'];
                    if ($key>0) {
                        $per_class_payment->echelon_id = $payment['echelon_id'];
                        $per_class_payment->batch_id = $payment['batch_id'];
                        $per_class_payment->payment_type = 'special';
                    }
                    else {
                        $per_class_payment->payment_type = 'general';
                    }
                    $per_class_payment->save();
                }
            }

            if (in_array('script_checker', $request->roles)) {
                foreach ($request->script_payments as $key => $payment) {
                    $script_payment = new script_payment;
                    $script_payment->teacher_id = $teacher->id;
                    $script_payment->marks_range = $payment['marks_range'];
                    $script_payment->amount = $payment['amount'];
                    $script_payment->save();
                }
            }

            if (in_array('invigilator', $request->roles)) {
                $invigilator_payment = new invigilator_payment;
                $invigilator_payment->teacher_id = $teacher->id;
                $invigilator_payment->per_hour_amount = $request->invigilator_payments['per_hour_amount'];
                // $invigilator_payment->per_batch_amount = $request->invigilator_payments['per_batch_amount'];
                $invigilator_payment->save();
            }            
            DB::commit();
            \Cache::clear();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = student::find($id)->where('id', $id)->first();
        return response()->json(['item' => $item]);
    }

    public function teacher_edit(Request $request, $id)
    {
        // return response()->json($request->all());
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'gender' => 'required',
            'mobile' => 'required',
            'roles' => 'required'
        ]);

        DB::beginTransaction();
        try {
            if ($request->password) {
                $user = User::find($request->user_id);
                $user->email = $request->email;
                $user->password = bcrypt($request->password);
                $user->save();
            }

            $teacher = teacher::find($id);
            $teacher->name = $request->name;
            $teacher->gender = $request->gender;
            $teacher->branch_id = $request->branch_id;
            $teacher->mobile = $request->mobile;
            $teacher->mobile2 = $request->mobile2;
            $teacher->address = $request->address;
            $teacher->salary_type = $request->salary_type;
            $teacher->roles = implode (", ", $request->roles);
            $teacher->status = $request->status;
            if ($request->salary_type == 'monthly') {
                $teacher->monthly_salary = $request->monthly_salary;
                if ($request->status=='active' && $request->salary_start) {
                    $teacher->salary_start = $request->salary_start . '.' . $request->year;
                    $teacher->salary_end = Null;
                }
                if ($request->status=='inactive' && $request->salary_end) {
                    $teacher->salary_end = $request->salary_end . '.' . $request->year;
                }
            }
            $teacher->join_date = $request->join_date;
            $teacher->save();

            $teacher->users->syncRoles($request->roles);

            if (in_array('teacher', $request->roles)) {
                $exiting_id = [];
                $teacher_id = 0;
                foreach ($request->payments as $key => $payment) {
                    if (isset($payment['id'])) {
                        $per_class_payment = per_class_payment::find($payment['id']);
                        $per_class_payment->amount = $payment['amount'];
                        if ($key>0) {
                            $per_class_payment->echelon_id = $payment['echelon_id'];
                            $per_class_payment->batch_id = $payment['batch_id'];
                            $per_class_payment->payment_type = 'special';
                        } else {
                            $per_class_payment->payment_type = 'general';
                        }
                        $per_class_payment->save();
                    }
                    else {
                        $per_class_payment = new per_class_payment;
                        $per_class_payment->teacher_id = $teacher->id;
                        $per_class_payment->amount = $payment['amount'];
                        if ($key>0) {
                            $per_class_payment->echelon_id = $payment['echelon_id'];
                            $per_class_payment->batch_id = $payment['batch_id'];
                            $per_class_payment->payment_type = 'special';
                        }
                        else {
                            $per_class_payment->payment_type = 'general';
                        }
                        $per_class_payment->save();
                    }
                    $teacher_id = $per_class_payment->teacher_id;
                    array_push($exiting_id, $per_class_payment->id);
                }
                $per_class_payment = per_class_payment::whereNotIn('id', $exiting_id)->where('teacher_id', $teacher_id)->delete();
            }

            if (in_array('script_checker', $request->roles)) {
                $exiting_id = [];
                foreach ($request->script_payments as $key => $payment) {
                    if (isset($payment['id'])) {
                        $script_payment = script_payment::find($payment['id']);
                        $script_payment->marks_range = $payment['marks_range'];
                        $script_payment->amount = $payment['amount'];
                        $script_payment->save();
                    }
                    else {
                        $script_payment = new script_payment;
                        $script_payment->teacher_id = $teacher->id;
                        $script_payment->marks_range = $payment['marks_range'];
                        $script_payment->amount = $payment['amount'];
                        $script_payment->save();
                    }
                    array_push($exiting_id, $script_payment->id);
                }
                $script_payment = script_payment::whereNotIn('id', $exiting_id)->delete();
            }

            if (in_array('invigilator', $request->roles)) {
                $invigilator_payment = invigilator_payment::where('teacher_id', $teacher->id)->first();
                if ($invigilator_payment) {
                    $invigilator_payment->per_hour_amount = $request->invigilator_payments['per_hour_amount'];
                    // $invigilator_payment->per_batch_amount = $request->invigilator_payments['per_batch_amount'];
                    $invigilator_payment->save();
                }
                else {
                    $invigilator_payment = new invigilator_payment;
                    $invigilator_payment->teacher_id = $teacher->id;
                    $invigilator_payment->per_hour_amount = $request->invigilator_payments['per_hour_amount'];
                    // $invigilator_payment->per_batch_amount = $request->invigilator_payments['per_batch_amount'];
                    $invigilator_payment->save();
                }
            }
            DB::commit();
            \Cache::clear();
            // return $per_class_payment;
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage());
        }
    }

    public function teacher_delete($id)
    {
        $teacher = teacher::where('id',$id)->first();
        $user = \App\User::where('id', $teacher->user_id)->delete();
        $teacher->delete();
        \Cache::clear();
    }

    public function upload_image(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if($request->hasFile('photo')){

            $student = student::where('reg_no', $request->reg_no)->where('status', 'present')->first();
            if (!$student) {
               return response()->json(['student_check'=>'Student not found of this reg no'], 422); 
            }
            $batch_name = $student->batches->first()->name;

            $width = intval($request->width);
            $height = intval($request->height);
            $left = intval($request->left);
            $top = intval($request->top);
            $image = $request->file('photo');
            $img = Intervention::make($image);
            $img->crop($width, $height, $left, $top);
            if ($img->width()>155) {
                $img->resize(155, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $filename = str_random(16). '.' . $image->getClientOriginalExtension();
            $path = public_path('storage/avatar/'.$batch_name);
            if (!file_exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            $img->save($path . '/' . $filename );
            student::where('reg_no', $request->reg_no)->where('status', 'present')->update(['photo'=>$filename]);            
            // return response()->json('storage/avatar/'.$batch_id.'/'.$filename);
        }
    }

}
