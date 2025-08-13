<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\Client;
use App\Model\Admin\student;
use Hash;
use File;
use Intervention;

// Contains
// User Profile

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:client');
        // $this->middleware('admin');
    }
    public function profile()
    {
    	$user = Client::where('id', Auth::guard('client')->user()->id)->with('students.batches')->get();
        return response()->json(['user' => $user]);
    }
    public function update_profile(Request $request)
    {
        $id = Auth::guard('client')->user()->id;
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email|unique:clients,email,'.$id,
        ]);
        
        $user = Client::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $student = student::where('client_id', $id)->first();
        $student->name = $request->name;
        $student->gender = $request->students['gender'];
        $student->mobile2 = $request->students['mobile2'];
        $student->date_of_birth = $request->students['date_of_birth'];
        $student->father_name = $request->students['father_name'];
        $student->mother_name = $request->students['mother_name'];
        $student->address = $request->students['address'];
        $student->save();
        return response()->json(['message' => 'Updated Succesfully']);
    }
    
    public function update_password(Request $request)
    {
        // return $request->all();
        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            // The passwords matches
            return response()->json(['password' => 'Your current password does not matches with the password you provided. Please try again.'], 422);
        }
 
        if(strcmp($request->get('current_password'), $request->get('new_password')) == 0){
            //Current password and new password are same
            return response()->json(['password' => 'New Password cannot be same as your current password. Please choose a different password.'], 422);
        }
 
        $this->validate($request,[
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);
 
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new_password'));
        $user->password_text = $request->get('new_password');
        $user->save();
        return response()->json(['password' => 'Password changed succesfully.']);
    }
    
    public function upload_image(Request $request)
    {
        // return response()->json($batch_name, 422);
        $user = Auth::guard('client')->user();

        if($request->hasFile('photo')){

            $student = student::where('client_id', $user->id)->where('status', 'present')->first();
            $batch_name = $student->batches->name;
            
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
            File::delete($path . '/' . $student->photo);
            $img->save($path . '/' . $filename);
            student::where('client_id', $user->id)->where('status', 'present')->update(['photo'=>$filename]);
            return response()->json(['file_name' => $filename, 'date_of_admit' => $student->date_of_admit, 'id' => $student->id]);
        }
    }
}
