<?php
namespace App\Http\Controllers\Admin;
use Auth;
use Cache;
use App\User;
use App\Admin;
use Carbon\Carbon;
use App\Model\Admin\batch;
use App\Model\Admin\branch;
use App\Model\Admin\echelon;
use App\Model\Admin\invoice;
use App\Model\Admin\student;
use App\Model\Admin\teacher;
use Illuminate\Http\Request;
use Tests\Feature\supportClass;
use App\Model\Admin\organization;
use App\Model\Admin\random_number;
use Spatie\Permission\Models\Role;
use App\Model\Admin\daily_schedule;
use App\Http\Controllers\Controller;
use App\Model\Admin\student_payment;
use Illuminate\Support\Facades\File;
use App\Model\Admin\student_attendance;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

/* Contains
Admin Dashboard
Admin Login Success Page*/

class HomeController extends Controller
{
    use supportClass;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	    $this->middleware('auth:admin');
	}


    public function index()
    {
        // return response()->json($permissions);
        $admin = Auth::guard('admin')->user();
        $permissions = $admin->getAllPermissions();
        $role = $admin->getRoleNames()->first();

        // dynamic by organization ================
        $dynamic_data = $this->get_dynamic_data($admin);
        // dynamic by organization
        
        $echelons = [];
        $batches = [];
        $years = [];

        for ($i= Carbon::now()->year; $i >= 2015 ; $i--) { 
            array_push($years, $i);
        }

        if ($role == 'admin') {
            $branch = branch::all();
            if (sizeof($branch) == 1) {
                if (in_array('echelon', $dynamic_data['search_by'])) {                
                    $this->get_echelons_by_branch($branch[0]->id);
                }  
                $this->get_batches_by_branch($branch[0]->id);                             
            }
        } else {
            $branch = branch::find($admin->branch_id)->get();
            if (in_array('echelon', $dynamic_data['search_by'])) {                
                $this->get_echelons_by_branch($admin->branch_id);
            }               
            $this->get_batches_by_branch($admin->branch_id);
        }
        if ($dynamic_data['enable_echelon_field']) {            
            $echelons = \App\Model\Admin\echelon::where('branch_id', $branch[0]->id)->get();
        }
        $random = random_number::first();
        if(!$random){
            $random = new random_number;
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        if ($random && $random->updated_at < Carbon::now()->subHours(2)->toDateTimeString()) {
            $random->random_number = rand(1000, 10000);
            $random->save();
        }

        $latest_unpaid = invoice::orderBy('id', 'desc')->first();
        // return $latest_unpaid;
        if ($latest_unpaid) {            
            if ($latest_unpaid->status == 'paid') {
                // create new invoice 7 days ago of due date
                $date_to = Carbon::parse($latest_unpaid->date_to);
                if (Carbon::today()->addDays(7)->gte($date_to)) {
                    // return $latest_unpaid;
                    $new_invoice = new invoice;
                    $new_invoice->invoice_to = "Towhidul Islam<br/>Oxygen, Chattogram, <br />Chattogram, Chattogram, 4213<br />Bangladesh";

                    $date_from = Carbon::parse($latest_unpaid->date_to)->addDays(1);
                    $date_today = Carbon::today();
                    $diff = $date_from->diffInMonths($date_today) + 1;
                    $date_to = Carbon::parse($latest_unpaid->date_to)->addMonth($diff);

                    $new_invoice->due_date = Carbon::parse($latest_unpaid->date_to)->addDays(5)->format('Y-m-d');
                    $new_invoice->date_from = $date_from->format('Y-m-d');
                    $new_invoice->date_to = $date_to->format('Y-m-d');


                    $new_invoice->amount = 5000 * $diff;
                    $new_invoice->paid = 0;
                    $new_invoice->status = 'unpaid';
                    $new_invoice->save();
                    $latest_unpaid = $new_invoice;
                }
            }

            if ($latest_unpaid->status != 'paid') {
                $due_date = Carbon::today()->format('Y-m-d') > $latest_unpaid->due_date ? "<span class='red--text'>OverDue</span>" : $latest_unpaid->due_date;
                $latest_unpaid->message = "You have a/an <span class='red--text m-r-5 m-l-5'>" . strtoupper($latest_unpaid->status) . "</span> Bill - #00" . $latest_unpaid->id . ". Due Date: " . $due_date . ".";
            }
            
            $latest_unpaid->overdue = 'false';
            $due_date = Carbon::parse($latest_unpaid->due_date);
            if ($latest_unpaid->status != 'paid' && Carbon::today()->format('Y-m-d') > $latest_unpaid->due_date) {
                $latest_unpaid->overdue = 'true';
            }
        }
        
        return view('admin.admin-home', compact('role', 'permissions', 'branch', 'echelons', 'batches', 'years', 'latest_unpaid', 'admin', 'dynamic_data'));
    }

    protected function get_dynamic_data($admin){
        $search_by = config('setting.search_student_by');
        $search_label = config('setting.search_label');
        $batch_label = config('setting.batch_label');
        // $allow_field = config('setting.allow_field');
        $reg_no_label = config('setting.reg_no_label');
        $enable_echelon_field = config('setting.enable_echelon_field');
        // $student_type = config('setting.student_type');

        if($admin->branches->organizations->system_type == 'school'){
            $batch_label = "Section";
        }
        if($admin->branches->organizations->system_type == 'single'){
            $search_by = ["batch"];
            $search_label = "Batch";
        }

        return ['search_by' => $search_by, 'search_label' => $search_label, 'batch_label' => $batch_label, 'reg_no_label' => $reg_no_label, 'enable_echelon_field' => $enable_echelon_field];
    }

    protected function get_batches_by_branch($branch_id)
    {
        $batches = Cache::rememberForever("batches_" . $branch_id, function () use ($branch_id) {
            $result = batch::where('branch_id', $branch_id)->get();
            return $result;
        });
        return $batches;
    }

    protected function get_echelons_by_branch($branch_id)
    {
        $echelons = Cache::rememberForever("echelons_" . $branch_id, function () use ($branch_id) {
            $result = echelon::where('branch_id', $branch_id)->get();
            return $result;
        });
        return $echelons;
    }

    public function get_admin_dashboard($branch_id, $batch_id){
        $admin = Auth::guard('admin')->user();
        if ($branch_id == 'all') {
            $branch_id = 0;
            $operator = '>';
        } else {
            $operator = '=';
        }
        $role = $admin->getRoleNames()->first();
        if ($role == 'admin' || $role == 'superadmin') {
            $batch = batch::where('active', 1)->get();
        }

        if ($admin->can('view_dashboard')) {
            $batch = batch::where('active', 1)->where('branch_id', $admin->branch_id)->get();
        }
            // get batch id
            $batch_ids = [];
            if ($batch_id == 'all') {
                foreach ($batch as $key => $btc) {
                    array_push($batch_ids, $btc->id);
                }
            } else {
                $batch_ids = [$batch_id];
            }
            $student = student::where('status', 'present')->where('branch_id', $operator, $branch_id)->whereIn('batch_id', $batch_ids)->get();
            $teacher = teacher::where('branch_id', $operator, $branch_id)->count();
            $attendance = student_attendance::whereDate('date', Carbon::today())->whereHas('students', function($query) use ($branch_id, $operator, $batch_ids){
                $query->where('status', 'present')->where('branch_id', $operator, $branch_id)->whereIn('batch_id', $batch_ids);
            })->count();
            $fee = student_payment::whereHas('students', function($query) use ($branch_id, $operator, $batch_ids){
                $query->where('status', 'present')->where('branch_id', $operator, $branch_id)->whereIn('batch_id', $batch_ids);
            })->sum('paid');
            // fee collection
            $total_fee = 0;
            foreach ($student as $key => $std) {
                $total_fee = $total_fee + $std->monthly_fee;
            }

            $schedule = [];
            // $schedule = daily_schedule::whereDate('date', Carbon::today())->with('subjects', 'batches.branches', 'teachers')->whereHas('batches', function($query) use ($branch_id, $operator){
            //     $query->where('branch_id', $operator, $branch_id);
            // })->whereIn('batch_id', $batch_ids)->get();
            $latest_unpaid = invoice::where('status', '!=', 'paid')->orderBy('created_at', 'desc')->first();
            if ($latest_unpaid) {
                $latest_unpaid->message = "You have a/an <span class='red--text m-r-5 m-l-5'>" . strtoupper($latest_unpaid->status) . "</span> Bill - #00" . $latest_unpaid->id . ". Due Date " . $latest_unpaid->due_date . ".";
            }


            return response()->json(['student' => $student, 'teacher' => $teacher, 'attendance' => $attendance, 'fee' => $fee, 'total_fee' => $total_fee, 'schedule' => $schedule, 'batch' => $batch, 'latest_unpaid' => $latest_unpaid]);
    
    }

    public function menu_role_permission(){
        $menus = menu::with('submenus')->get();
        return response()->json(['menus'=>$menus]);
    }
    public function change_random_number()
    {        
        $random = random_number::first();
        if(!$random){
            $random = new random_number;
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        if ($random && $random->updated_at < Carbon::now()->subHours(3)->toDateTimeString()) {
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        return response()->json(['random_number' => $random]);
    }

    public function firstItemSetup()
    {
        $student_setup_field = config('setting.first_login_setup_field');
        return view('admin.setup.first-time-setup', compact('student_setup_field'));
    }

    public function SetupSave(Request $request)
    {
        // return $request->all();
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'branch_quantity' => 'required|integer|min:1',
            'system_type' => 'required|in:school,coaching,single',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'admission_fields' => 'nullable|array',
            'admission_fields.*' => 'string',
        ]);
        $user = Auth::guard('admin')->user();
        $organization = organization::firstOrNew(['id' => $user->branches->organization_id]);

        // Assign fields
        $organization->name = $validated['organization_name'];
        $organization->branch_quantity = $validated['branch_quantity'];
        $organization->system_type = $validated['system_type'];

        // Handle logo upload if exists
        if ($request->hasFile('logo')) {
            // পুরানো ফাইল ডিলিট করতে চাইলে নিচে uncomment করো
            $destinationPath = public_path('storage/');
            // if (File::exists($filePath)) {
            // return $destinationPath.$organization->logo;
            File::delete($destinationPath.$organization->logo);
            // }

            $path = $request->file('logo')->store('logos', 'public');
            $organization->logo = $path;
        }

        // Save admission_fields as JSON string
        $organization->admission_fields = json_encode($validated['admission_fields'] ?? []);
        // return $organization;
        $organization->is_first_login = 0;
        $organization->save();
        
        return redirect('/admin/home');
        // return view('admin.setup.first-time-setup', compact('student_setup_field'));
    }

    public function save_test_data(Request $request)
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            // return response()->json('not local', 422);
        }

        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::today()->addDays(1)->format('Y-m-d');
        $other_date = Carbon::today()->addDays(2)->format('Y-m-d');
        // $this->change_teacher_subject_to_schedule($today, false, false, 0, 0, 'class');

        // create_student($reg_no, $size = 1, $status = 'present', $monthly_fee = true, $course_fee = false, $payment_status='paid', $payment_size= 1, $payment_type = 'unpaid', $payment_today = false)
        // payment_type = unpaid, unpaid_multiple, advance, due
        if ($request->action_type == 'add_student') {
            $this->create_student($request);
        }
        if ($request->action_type == 'add_visiting_list') {
            $this->create_visiting_list($request);
        }
        if ($request->action_type == 'add_schedule') {
            $this->create_daily_schedule($request);
        }
    }

    public function get_confirmation()
    {
        $confirmation = \App\Model\Admin\payment_confirm::get();
        return view('admin_payment_confirm', compact('confirmation'));
        // return response()->json(['confirm' => $confirmation]);
    }
}
