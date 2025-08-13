<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Model\Admin\user_message;
use App\Model\Admin\teacher;
use Auth;

class MessageController extends Controller
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
    
    public function message_list()
    {
        $message_list = user_message::with('teachers')->get();
        return response()->json(['message_list'=>$message_list]);
    }

    public function message_add(Request $request)
    {
        $this->validate($request, [
        	'message'=> 'required'
        ]);

        if ($request->type=='all') {
            $teachers = teacher::where('act_as_teacher', 0)->get();
        }
        if ($request->type != 'all' && $request->type != 'single') {
            $teachers = teacher::where('act_as_teacher', 0)->whereHas('users.roles', function($query) use ($request){
                $query->where('name', $request->type)->where('guard_name', 'web');
            })->get();
        }

        if ($request->type != 'single') {
            foreach ($teachers as $key => $teacher) {
                $message = new user_message;
                $message->teacher_id = $teacher->id;
                $message->message = $request->message;
                $message->due_date = $request->due_date;
                $message->save();
            }
        } else {            
            $message = new user_message;
            $message->teacher_id = $request->teacher_id;
            $message->message = $request->message;
            $message->due_date = $request->due_date;
            $message->save();
        }

    }

    public function message_edit(Request $request, $id)
    {
        $message = user_message::find($id);
        $message->teacher_id = $request->teacher_id;
        $message->message = $request->message;
        $message->due_date = $request->due_date;
        $message->status = null;
        $message->save();
    }

    public function message_delete($id)
    {
        user_message::where('id',$id)->delete();
    }
}
