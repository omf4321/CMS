<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\Admin\sms_balance;
use App\Model\Admin\teacher;
use App\admin;
use App\admin_role;
use App\menu;
use App\user;
use Auth;
use DB;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\ResponseCache\Facades\ResponseCache;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        // $this->middleware('admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admin_role = Auth::guard()->user('admin')->roles->first();
        $admin_permissions = $admin_role->permissions;
        // return response()->json($permissions);
        return view('admin.admin-home', compact('admin_role', 'admin_permissions'));
    }

    // ====================== Admin User =====================================
    // =======================================================================
    public function admin_role_list()
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')) {
            $admin_role_list = Role::where('name', '!=', 'superadmin')->get();
        }
        if ($user->hasRole('admin')) {
            $admin_role_list = Role::where('name', '!=', 'superadmin')->where('name', '!=', 'admin')->where('guard_name', 'admin')->get();
        }
        return response()->json(['admin_role_list' => $admin_role_list]);
    }

    public function admin_permission_list()
    {
        $user = Auth::guard('admin')->user();
        if ($user->can('manage_role') || $user->hasRole('superadmin') || $user->hasRole('admin')) {
            $admin_permission_list = Permission::get();
            $permission_list = [];
            $permission_data = config('setting.permissions');
            foreach ($permission_data as $p_key => $data) {
                $found = 0;
                foreach ($admin_permission_list as $key => $permission) {
                    if ($data[0] == $permission->name) {
                        $permission->name = ucfirst(str_replace('_', ' ', $data[0]));
                        $permission->serial_no = $p_key + 1;
                        array_push($permission_list, $permission);
                        $found = 1;
                    }
                }
                if ($found == 0) {
                    $permission = new permission;
                    $permission->name = $data[0];
                    $permission->description = $data[1];
                    $permission->guard_name = 'admin';
                    $permission->save();
                    $permission->serial_no = $p_key + 1;
                    $permission->name = ucfirst(str_replace('_', ' ', $data[0]));
                    array_push($permission_list, $permission);
                }
            }
            usort($permission_list, function($a, $b) {
                return ($a->serial_no) - ($b->serial_no);
            });
            return response()->json(['admin_permission_list' => $permission_list]);
        }
    }

    public function admin_user_list()
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $admin_user_list = admin::with('roles', 'branches')
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'superadmin');
            })->where('id', '!=', 1)->get();
        }
        return response()->json(['admin_user_list' => $admin_user_list]);
    }

    public function admin_user_add(Request $request)
    {
        // return response()->json(['request' => $request->all()]);
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')) {
            $this->validate($request,[
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'password' => 'required_with:confirm_password|same:confirm_password|string|max:255',
                'admin_role_id' => 'required',
                'branch_id' => 'required'
            ]);

            $role = Role::find($request->admin_role_id);
            
            $password = bcrypt($request->password);
            $admin_user = new admin;
            $admin_user->name = $request->name;
            $admin_user->email = $request->email;
            $admin_user->password = $password;
            $admin_user->active = 1;
            $admin_user->branch_id = $request->branch_id;
            $admin_user->save();
            $admin_user->assignRole($role->name);
            
            return response()->json(['admin_user_add' => $admin_user]);
        }
       
    }

    public function admin_user_edit(Request $request, $id)
    {
        // return response()->json(['request' => $request->all()]);
        $this->validate($request,[
            'admin_role_id' => 'required',
            'branch_id' => 'required'
        ]);

        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $role = Role::find($request->admin_role_id);
            $admin_user = admin::find($id);
            if ($user->hasRole('superadmin') && $request->change_password) {
                $password = bcrypt($request->change_password);
                $admin_user->password = $password;
            }
            if ($user->hasRole('superadmin') && $request->email) {
                $admin_user->email = $request->email;
            }

            $admin_user->branch_id = $request->branch_id;
            $admin_user->save();
            $admin_user->assignRole($role->name);
        }
       
    }

    public function admin_user_delete($id)
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            admin::where('id',$id)->delete();
        } else {
            abort(403);
        }
    }

    // ====================== Admin Role =====================================
    // =======================================================================

    public function admin_permissions_by_role($id)
    {
        $admin_permissions_by_role = Role::find($id)->permissions;
        return response()->json(['admin_permissions_by_role' => $admin_permissions_by_role]);
    }

    public function admin_role_add(Request $request)
    {
        // return response()->json(['request' => $request->all()]);
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            
            $this->validate($request,[
                'name' => 'required|string|max:255',
            ]);
            $name = trim(preg_replace('/-+/', '_', $request->name), '_');
            $name = trim(preg_replace("/ {2,}/", " ", $name));
            $name = strtolower(str_replace(" ", "_", $name));
            $role = Role::create(['guard_name' => 'admin', 'name' => $name]);
            $role->syncPermissions($request->permission);
        }
    }

    public function admin_role_edit(Request $request, $id)
    {
        // return response()->json(['request' => $request->all()]);
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            
            $this->validate($request,[
                'name' => 'required|string|max:255',
            ]);

            $name = trim(preg_replace('/-+/', '_', $request->name), '_');
            $name = trim(preg_replace("/ {2,}/", " ", $name));
            $name = strtolower(str_replace(" ", "_", $name));
            
            $role = Role::find($id);
            $role->name = $name;
            $role->save();
            $role->syncPermissions($request->permission);
        }
    }

    public function admin_role_delete($id)
    {
        // return response()->json(['request' => $id]);
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            Role::where('id',$id)->delete();
        } else {
            abort(403);
        }
    }

    public function act_as_teacher()
    {
        $admin = Auth::guard('admin')->user();
        $user = \App\User::where('email', $admin->email)->first();
        if (!$user) {
            DB::beginTransaction();
            try {
                // $password = bcrypt(mt_rand(100000,999999));
                $user = new User;
                $user->name = $admin->name;
                $user->email = $admin->email;
                $user->password = bcrypt('123456');
                $user->status = 'active';
                $user->branch_id = $admin->branch_id;
                $user->save();
                $user->assignRole(['teacher', 'script_checker', 'invigilator']);

                $teacher = new teacher;
                $teacher->name = $admin->name;
                $teacher->gender = 'male';
                $teacher->branch_id = $admin->branch_id;
                $teacher->user_id = $user->id;
                $teacher->mobile = '01676258587';
                $teacher->status = 'active';
                $teacher->act_as_teacher = 1;
                $teacher->roles = 'teacher,script_checker,invigilator';   
                $teacher->join_date = \Carbon\Carbon::now()->format('Y-m-d');
                $teacher->save();

                // Auth::guard('admin')->logout();
                Auth::guard('web')->login($user);
                DB::commit();
                return redirect('/user/home');
            } catch (Exception $e) {
                DB::rollback();
                return response()->json($e->getMessage());
            }
        }
        else {
            // Auth::guard('admin')->logout();
            Auth::guard('web')->login($user);
            return redirect('/user/home');
        }
    }

    public function clear()
    {
        ResponseCache::flush();
        \Cache::flush();
        return redirect()->back();
    }

    public function recharge_sms($amount)
    {
        $admin = Auth::guard('admin')->user();
        if ($admin->hasRole('superadmin')) {
            $sms_balance = sms_balance::where('id', 1)->first();            
            $sms_balance->balance = $sms_balance->balance + $amount;
            $sms_balance->save();
            return $sms_balance->balance;
        } else {
            abort(403);
        }
    }


    public function test()
    {
        \App\Model\Admin\invoice::where('id', 8)->update(['payment_id' => Null, 'merchant_invoice_number' => Null, 'payment_from' => Null, 'trxid' => Null, 'transaction_time' => Null, 'amount' => 10000, 'paid' => 0, 'status' => 'unpaid']);
        return 'true';
    }

}

