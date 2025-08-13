<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\User;
use App\Model\Admin\teacher;
use Hash;


// Contains
// User Profile

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
        // $this->middleware('admin');
    }
    public function profile()
    {
    	$user = User::where('id', Auth::guard('web')->user()->id)->with('teachers.subjects.echelons')->get();
        return response()->json(['user' => $user]);
    }
    public function update_profile(Request $request)
    {
        $id = Auth::guard('web')->user()->id;
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
        ]);
        
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $teacher = teacher::where('user_id', $id)->first();
        $teacher->name = $request->name;
        $teacher->gender = $request->teachers['gender'];
        $teacher->mobile = $request->teachers['mobile'];
        $teacher->mobile2 = $request->teachers['mobile2'];
        $teacher->address = $request->teachers['address'];
        $teacher->save();
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
        $user->save();
        return response()->json(['password' => 'Password changed succesfully.']);
    }
    public function update_image(Request $request)
    {
        // return response()->json($request->all());
        $user = Auth::user();
        $old_image_name = $user->image;
        $new_image_name = $user->id.'_'.$request->file_name.'.'.$request->extension;
        //save database new image name;
        $profiles = user::find($user->id);
        $profiles->image = $new_image_name;
        $profiles->save();

        //initialize path
        $path = storage_path('app/public').'/image/avatar';
        $destinationPath = storage_path('app/public').'/image/avatar/';

        //create directory if not exist
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        //store files to destination path
        $file_save = $request->file->move($destinationPath, $new_image_name);
        $img = Intervention::make($destinationPath.$new_image_name);
        if ($img->width()>240 || $img->filesize()>400000) {
            $img = Intervention::make($destinationPath.$new_image_name)->resize(240, null, function ($constraint) {
            $constraint->aspectRatio();
            });
            $img->save($destinationPath.$new_image_name);
        }
        //delete old_file
        $destinationPath = storage_path('app/public').'/image/avatar/'.$old_image_name;
        File::delete($destinationPath);

        return response()->json(['image' => $new_image_name]);
    }
}
