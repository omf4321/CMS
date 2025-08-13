<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Admin;
use App\Model\Admin\student;
use App\Model\Admin\schedule_teacher_attendance;
use App\Model\Admin\student_payment;
use App\Model\Admin\teacher_payment;
use App\Model\Admin\transaction_category;
use App\Model\Admin\transaction;
use App\Model\Admin\account;
use App\Model\Admin\bill;
use App\Model\Admin\biller;
use App\Model\Admin\salary;
use App\Model\Admin\salary_transaction;
use App\Model\Admin\salary_of_month;
use App\Model\Admin\bank_account;
use App\Model\Admin\bank_transaction;
use App\Model\Admin\fund;
use App\Model\Admin\fund_transaction;
use App\Model\Admin\balance_signature;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use SoapClient;

class TransactionController extends Controller
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

    public function get_todays_transaction_list()
    {
        $user = Auth::guard('admin')->user();
        $accounts = account::where('branch_id', $user->branch_id)->get();
        if (sizeof($accounts)==1) {            
            $categories = transaction_category::where('account_id', $accounts[0]->id)->get();
            $biller = biller::where('account_id', $accounts[0]->id)->get();

            $bill_list = bill::whereHas('billers', function($query) use ($accounts){
                $query->where('account_id', $accounts[0]->id);
            })->whereRaw('amount != paid')->with('billers')->get();

            $transactions = transaction::whereHas('categories', function($query) use ($accounts){
                $query->where('account_id', $accounts[0]->id);
            })->whereDate('created_at', Carbon::today()->format('Y-m-d'))->orderBy('id', 'desc')->with('categories')->get();

            return response()->json(['bill_list' => $bill_list, 'categories' => $categories, 'transactions' => $transactions, 'biller' => $biller, 'accounts' => $accounts]);
        }
    }

    public function get_transaction_list(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if (!$request->from_date) {
            $from_date = Carbon::today();
        }
        if (!$request->to_date) {
            $to_date = Carbon::today();
        }
        $user = Auth::guard('admin')->user();
        $accounts = account::where('branch_id', $user->branch_id)->get();
        if (sizeof($accounts)==1) {            
            $categories = transaction_category::where('account_id', $accounts[0]->id)->get();
            $transactions = transaction::whereHas('categories', function($query) use ($accounts){
                $query->where('account_id', $accounts[0]->id);
            })->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->orderBy('created_at')->with('categories', 'bills', 'users')->get();
            return response()->json(['categories' => $categories, 'transactions' => $transactions]);
        }
    }

    public function get_bill_list(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if (!$request->from_date) {
            $from_date = Carbon::today()->subDays(15);
        }
        if (!$request->to_date) {
            $to_date = '2050-01-01';
        }
        $user = Auth::guard('admin')->user();
        $accounts = account::where('branch_id', $user->branch_id)->get();
        if (sizeof($accounts) == 1) {
            $bills = bill::whereHas('billers', function($query) use ($accounts){
                $query->where('account_id', $accounts[0]->id);
            })->whereDate('due_date', '>=', $from_date)->whereDate('due_date', '<=', $to_date)->orderBy('due_date')->with('billers')->get();
            return response()->json(['bills' => $bills]);
        }
    }

    public function edit_transaction_list(Request $request, $id)
    {        
        $transaction = transaction::find($id);
        $transaction->detail = trim($request->detail);
        $transaction->category_id = $request->category_id;
        $transaction->bill_id = $request->bill_id;
        $transaction->save();
        return response()->json(['transaction' => transaction::where('id', $transaction->id)->with('categories')->first()]);
    }

    public function edit_bill_list(Request $request, $id)
    {        
        $bill = bill::find($id);
        $bill->voucher_detail = trim($request->voucher_detail);
        $bill->name = trim($request->name);        
        $bill->biller_id = $request->biller_id;
        $bill->amount = $request->amount;
        $bill->paid = $request->paid;
        $bill->due_date = $request->due_date;
        $bill->save();
        return response()->json(['bill' => bill::where('id', $bill->id)->first()]);
    }

    public function delete_bill_list($id)
    {        
        $bill = bill::find($id);
        $bill->delete();
    }

    public function get_biller_list()
    {        
        $billers = biller::with('accounts')->get();
        return response()->json(['billers' => $billers]);
    }

    public function edit_biller_list(Request $request, $id)
    {        
        $biller = biller::find($id);
        $biller->biller_detail = trim($request->biller_detail);
        $biller->name = trim($request->name); 
        $biller->save();
        return response()->json(['biller' => biller::where('id', $biller->id)->first()]);
    }

    public function delete_biller_list($id)
    {        
        $biller = biller::find($id);
        $biller->delete();
    }

    public function add_bill(Request $request)
    {
        if (isset($request->biller_id['text'])){
            $biller_id = $request->biller_id['value'];
        } else {
            $biller = new biller;
            $biller->name = $request->biller_id;
            $biller->biller_detail = $request->biller_detail;
            $biller->account_id = $request->account_id;
            $biller->save();
            $biller_id = $biller->id;
        }
        $bill = new bill;
        $bill->amount = $request->amount;
        $bill->name = $request->name;
        $bill->biller_id = $biller_id;
        $bill->voucher_detail = $request->voucher_detail;
        $bill->paid = 0;
        $bill->due_date = $request->due_date;
        $bill->save();
    }

    public function add_transaction(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $transaction = new transaction;
        $transaction->detail = trim($request->detail);
        $transaction->amount = $request->transaction_amount;
        $transaction->category_id = $request->category_id;
        $transaction->type = $request->type;
        if ($request->bill_id) {
            $bill = bill::find($request->bill_id);
            $bill->paid = $request->transaction_amount;
            $bill->save();
            $transaction->bill_id = $request->bill_id;
        }
        $transaction->inputer_id = $user->id;
        $transaction->save();
        return response()->json(['transaction' => transaction::where('id', $transaction->id)->with('categories')->first()]);
    }

    public function delete_transaction($id)
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $transaction = transaction::find($id)->delete();
        } else {
            $transaction = transaction::where('id', $id)->where('inputer_id', $user->id)->delete();
        }
    }


    // ============================ salary ========================================
    //=============================================================================
    public function get_admin()
    {        
        $admin = admin::WhereHas('salaries', function($query){
            $query->where('status', 'active');
        })->get();
        return response()->json(['admin' => $admin]);
    }

    public function get_salary($id)
    {        
        $salaries = salary_of_month::where('admin_id', $id)->orderBy('month', 'desc')->with('users')->limit(3)->get();
        $transaction_list = salary_transaction::where('admin_id', $id)->with('users')->orderBy('created_at', 'desc')->limit(10)->get();
        return response()->json(['salaries' => $salaries, 'transaction_list' => $transaction_list]);
    }

    public function add_salary(Request $request)
    {
        $salary = salary::where('admin_id', $request->admin_id)->first();
        $salary_of_month = salary_of_month::where('admin_id', $request->admin_id)->orderBy('month', 'desc')->first(); 

        if (!$salary_of_month) {
            $new_salary_month = new salary_of_month;
            $new_salary_month->admin_id = $request->admin_id;
            $new_salary_month->amount = $request->amount;
            $new_salary_month->bonus = $request->bonus;
            $new_salary_month->month = Carbon::now()->subMonth(1)->StartOfMonth();
            $new_salary_month->save();
            
        } else {

            if (!$request->bonus && $request->amount + $salary_of_month->amount > $salary->amount) {
                $remaining_current_amount = $salary->amount - $salary_of_month->amount;
                $amount = $salary_of_month->amount + $remaining_current_amount;
                $extra_amount = $request->amount - $remaining_current_amount;
                $salary_of_month->amount = $amount;
                // save current
                $salary_of_month->save();
                $month = Carbon::parse($salary_of_month->month)->addMonth(1)->StartOfMonth();
                while($extra_amount > 0) {
                    $amount = $salary->amount - $extra_amount >= 0 ? $extra_amount : $salary->amount;
                    $this->add_month_of_salary($request->admin_id, $amount, $month, Null);
                    $extra_amount = $salary->amount - $extra_amount > 0 ? 0 : $extra_amount - $salary->amount;
                    $month = Carbon::parse($month)->addMonth(1)->StartOfMonth();
                }
            }  
            if (!$request->bonus && $request->amount + $salary_of_month->amount <= $salary->amount) {
                $amount = $request->amount + $salary_of_month->amount;
                $salary_of_month->amount = $amount;
                $salary_of_month->save();
            }   


            if ($request->bonus && $request->amount + $salary_of_month->amount > $salary->amount) {
                $remaining_current_amount = $salary->amount - $salary_of_month->amount;
                $amount = $salary_of_month->amount + $remaining_current_amount;
                $extra_amount = $request->amount - $remaining_current_amount;
                $cut_from_bonus = $salary->amount - $extra_amount < $request->bonus ? $salary->amount - $extra_amount : $request->bonus; 
                $bonus = $request->bonus - $cut_from_bonus;
                // save current
                $salary_of_month->amount = $amount;
                $salary_of_month->bonus = !$extra_amount ? $bonus : Null;
                $salary_of_month->save();
                $month = Carbon::parse($salary_of_month->month)->addMonth(1)->StartOfMonth();
                while($extra_amount > 0) {
                    $amount = $salary->amount - $extra_amount >= 0 ? $extra_amount + $cut_from_bonus : $salary->amount;
                    $bon = $salary->amount - $extra_amount >= 0 ? $bonus : Null;
                    $this->add_month_of_salary($request->admin_id, $amount, $month, $bon);
                    $extra_amount = $salary->amount - $extra_amount > 0 ? 0 : $extra_amount - $salary->amount;
                    $month = Carbon::parse($month)->addMonth(1)->StartOfMonth();
                }           
            }          
            if ($request->bonus && $request->amount + $salary_of_month->amount <= $salary->amount) {
                $amount = $request->amount + $salary_of_month->amount;
                $cut_from_bonus = $salary->amount - $amount < $request->bonus ? $salary->amount - $amount : $request->bonus;
                $bonus = $request->bonus - $cut_from_bonus; 
                $salary_of_month->amount = $amount + $cut_from_bonus;
                $salary_of_month->bonus = $bonus == 0 ? Null : $bonus;
                $salary_of_month->save();     
            }
        }       


        $salary_transaction = new salary_transaction;
        $salary_transaction->admin_id = $request->admin_id;
        $salary_transaction->amount = $request->amount;
        if ($request->bonus) {
            $salary_transaction->amount = $request->amount + $request->bonus;
        }
        $salary_transaction->save();
    }

    protected function add_month_of_salary($admin_id, $amount, $month, $bonus)
    {
        $new_salary_month = new salary_of_month;
        $new_salary_month->admin_id = $admin_id;
        $new_salary_month->amount = $amount;
        $new_salary_month->bonus = $bonus;
        $new_salary_month->month = $month;
        $new_salary_month->save();
    }

    public function delete_salary($id)
    {
        $salary_transaction = salary_transaction::where('id', $id)->first();
        $salary = salary::where('admin_id', $salary_transaction->admin_id)->first();
        $salary_of_month = salary_of_month::where('admin_id', $salary_transaction->admin_id)->orderBy('month', 'desc')->first();

        if ($salary_transaction->amount >= $salary_of_month->amount + $salary_of_month->bonus) {
            $extra_amount = $salary_transaction->amount - ($salary_of_month->amount  + $salary_of_month->bonus);
            $last_salary_amount = $salary_of_month->amount + $salary_of_month->bonus;
            $salary_of_month->delete();
            if ($extra_amount) {                
                while($extra_amount >= $salary->amount) {
                    $salary_of_month = salary_of_month::where('admin_id', $salary_transaction->admin_id)->orderBy('month', 'desc')->first();
                    $salary_of_month->delete();
                    $extra_amount = $extra_amount > $salary->amount ? $extra_amount - $salary->amount : $extra_amount;
                }

                if ($extra_amount < $salary->amount) {    
                    $salary_of_month = salary_of_month::where('admin_id', $salary_transaction->admin_id)->orderBy('month', 'desc')->first();   
                    $bonus =  $salary_of_month->bonus < $extra_amount ? 0 : $salary_of_month->bonus - $extra_amount; 
                    $cut_from_bonus = $salary_of_month->bonus >= $extra_amount ? 0 : $extra_amount - $salary_of_month->bonus;             
                    $salary_of_month->amount = $salary_of_month->amount - $cut_from_bonus;
                    $salary_of_month->bonus = $bonus;
                    $salary_of_month->save();
                }
            }
        }
        else {
            $cut_from_bonus = $salary_transaction->amount < $salary_of_month->bonus ? 0 : $salary_transaction->amount - $salary_of_month->bonus;
            $salary_of_month->bonus = $salary_transaction->amount >= $salary_of_month->bonus ? 0 : $salary_of_month->bonus - $salary_transaction->amount;
            $salary_of_month->amount = $salary_of_month->amount - $cut_from_bonus;
            $salary_of_month->save();
        }
        $salary_transaction->delete();
    }

    // ============================================================================
    // ============================ bank transaction ==============================
    // ============================================================================
    
    public function get_bank_account()
    {
        $bank_account = bank_account::all();
        return response()->json(['bank_account' => $bank_account]);
    }

    public function get_bank_transaction($id)
    {
        $bank_transaction = bank_transaction::where('bank_account_id', $id)->OrderBy('created_at', 'desc')->limit(5)->get();
        return response()->json(['bank_transaction' => $bank_transaction]);
    }

    public function add_bank_transaction(Request $request)
    {
        $bank_transaction = new bank_transaction;
        $bank_transaction->name = $request->name;
        $bank_transaction->amount = $request->amount;
        $bank_transaction->status = $request->status;
        $bank_transaction->bank_account_id = $request->bank_account_id;
        $bank_transaction->save();
        return response()->json(['bank_transaction' => $bank_transaction]);
    }

    public function delete_bank_transaction($id)
    {
        $bank_transaction = bank_transaction::find($id);
        $bank_transaction->delete();
    }

    // fund
    public function get_fund_list()
    {
        $fund_list = fund::all();
        return response()->json(['fund_list' => $fund_list]);
    }

    public function add_fund(Request $request)
    {
        $fund = new fund;
        $fund->name = $request->name;
        $fund->description = $request->description;
        $fund->save();
        return response()->json(['fund' => $fund]);
    }

    public function edit_fund(Request $request, $id)
    {
        $fund = fund::find($id);
        $fund->name = $request->name;
        $fund->description = $request->description;
        $fund->save();
        return response()->json(['fund' => $fund]);
    }

    public function delete_fund($id)
    {
        $fund = fund::find($id);
        $fund->delete();
    }

    public function get_fund_transaction($id)
    {
        $fund_transaction = fund_transaction::where('fund_id', $id)->with('funds')->OrderBy('created_at', 'desc')->limit(5)->get();
        return response()->json(['fund_transaction' => $fund_transaction]);
    }

    public function add_fund_transaction(Request $request)
    {
        $fund_transaction = new fund_transaction;
        $fund_transaction->amount = $request->amount;
        $fund_transaction->status = $request->status;
        $fund_transaction->fund_id = $request->fund_id;
        $fund_transaction->save();
        return response()->json(['fund_transaction' => fund_transaction::where('id', $fund_transaction->id)->with('funds')->first()]);
    }

    public function delete_fund_transaction($id)
    {
        $fund_transaction = fund_transaction::find($id);
        $fund_transaction->delete();
    }

    // ========================================== Reports ============================
    public function get_balance_report(Request $request)
    {        
        $date_from = $request->date_from;
        $date_to = $request->date_to;        
        if ($request->day == 'today') {
            $date_from = Carbon::today()->format('Y-m-d');
            $date_to = Carbon::today()->format('Y-m-d');
            $prev_date = Carbon::today()->subDays(1)->format('Y-m-d');
        }
        if ($request->day == 'yesterday') {
            $date_from = Carbon::today()->subDays(1)->format('Y-m-d');
            $date_to = Carbon::today()->subDays(1)->format('Y-m-d');
            $prev_date = Carbon::today()->subDays(2)->format('Y-m-d');
        }
        if ($date_from == $date_to) {            
            $prev_date = Carbon::parse($date_from)->subDays(1)->format('Y-m-d');
        }
        if (!$date_from) {
            return response()->json('false', 422);
        }

        // get student payment
        $student_payment = student_payment::whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to)->select(DB::raw('DATE(date) as date'), DB::raw('sum(paid) as amount'))->groupBy('date')->get();
        // return $student_payment;
        // get teacher payment
        $teacher_payment = teacher_payment::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->with('teachers')->get();
        // get transaction
        $transaction = transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to);
        $transaction_expense = transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->where('type', 'expense')->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))->groupBy('date')->get();
        $transaction_income = transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->where('type', 'income')->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))->groupBy('date')->get();
        // get salary
        $salary_transaction = salary_transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))->groupBy('date')->get();
        // hand cash
        if ($request->day || $date_from == $date_to) {
            $hand_cash = balance_signature::whereDate('date', $prev_date)->first();
            $yesterday_hand_cash = $hand_cash ? $hand_cash->hand_cash: 0;
        } else {
            $yesterday_hand_cash = 0;
        }
        // bank amount
        $bank_withdrawal = bank_transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->where('status', 'withdrawal')->sum('amount');
        $bank_deposit = bank_transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->where('status', 'deposit')->sum('amount');
        // funding
        $fund_transaction = fund_transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->where('status', 'deposit')->sum('amount');
        // signature
        $signature = balance_signature::whereDate('date', $date_from)->first();
        $sign = !$signature ? 'true' : 'false';
        if ($signature && sizeof($signature->admin_id) < 2) { 
            $sign = 'true';           
            foreach ($signature->admin_id as $key => $admin) {
                if ($admin['id'] == Auth::guard('admin')->user()->id) {
                    $sign = 'false';
                }
            }
        }

        return response()->json(['student_payment' => $student_payment, 'teacher_payment' => $teacher_payment, 'salary' => $salary_transaction, 'transaction_expense' => $transaction_expense, 'transaction_income' => $transaction_income, 'bank_withdrawal' => $bank_withdrawal, 'bank_deposit' => $bank_deposit, 'fund_transaction' => $fund_transaction, 'yesterday_hand_cash' => $yesterday_hand_cash, 'sign' => $sign]);
    }

    public function balance_signature(Request $request)
    {
        if ($request->day == 'today') {
            $date = Carbon::today()->format('Y-m-d');
        }
        if ($request->day == 'yesterday') {
            $date = Carbon::today()->subDays(1)->format('Y-m-d');
        }
        $user = Auth::guard('admin')->user();
        $signature = balance_signature::where('date', $date)->first();
        if (!$signature) {
            $signature = new balance_signature;
            $admin = [];
            $arr = ['id' => $user->id, 'name' => $user->name];
            array_push($admin, $arr);
            $signature->admin_id = $admin;
            $signature->hand_cash = $request->final_total;
            $signature->date = $date;
        }
        else {
            $prev_arr = [];
            if (sizeof($signature->admin_id) == 1 &&  $signature->admin_id[0]['id'] != $user->id) {
                $arr1 = ['id' => $signature->admin_id[0]['id'], 'name' => $signature->admin_id[0]['name']];
                $arr2 = ['id' => $user->id, 'name' => $user->name];
                array_push($prev_arr, $arr1);
                array_push($prev_arr, $arr2);
                $signature->admin_id = $prev_arr;
            }    
        }
        $signature->hand_cash = $request->final_total;
        $signature->save();
    }

    public function get_balance_analysis(Request $request)
    {
        if (!$request->date_from || !$request->date_to) {
            return response()->json('false', 422);
        }
        $date_from = $request->date_from;
        $date_to = $request->date_to;
        // get student net payment
        $students = student::where('status', 'present')->whereNotNull('monthly_fee')->where('student_type', 1)->sum('monthly_fee');        
        // get teacher net payment
        $teacher_attendance = schedule_teacher_attendance::whereHas('schedules', function($query) use ($date_from, $date_to){
            $query->whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to);
        })->with('teachers.per_class_payments', 'exam_lists')->get();

        $class_payment = 0;
        $script_payment = 0;
        $invigilator_payment = 0;
        foreach ($teacher_attendance as $key => $teacher) {
            if ($teacher->type =='teacher' && sizeof($teacher->teachers->per_class_payments)) {                
                $class_payment += $teacher->teachers->per_class_payments[0]->amount;
            }
            if ($teacher->type =='invigilator') {   
                $hour = floor($teacher->invigilator_minute / 60);              
                $min = $teacher->invigilator_minute % 60;
                $minute_amount  = 0;
                if ($min / 60 > .25) {
                    $minute_amount = 50;
                }
                if ($min / 60 > .75) {
                    $minute_amount = 100;
                }                
                $invigilator_payment = $invigilator_payment + ($hour * 100) + $minute_amount;
            }
            if ($teacher->type =='scripter') {   
                if ($teacher->exam_lists->sub_full_mark >=70) {
                    $script_payment = $script_payment + ($teacher->script_quantity * 8);
                }
                if ($teacher->exam_lists->sub_full_mark >=50) {
                    $script_payment = $script_payment + ($teacher->script_quantity * 6);
                }
                if ($teacher->exam_lists->sub_full_mark >=40) {
                    $script_payment = $script_payment + ($teacher->script_quantity * 5);
                }
                if ($teacher->exam_lists->sub_full_mark >=30) {
                    $script_payment = $script_payment + ($teacher->script_quantity * 4);
                }
                if ($teacher->exam_lists->sub_full_mark >=10) {
                    $script_payment = $script_payment + ($teacher->script_quantity * 3);
                }
            }
        }

        $teacher_payment = $class_payment + $invigilator_payment + $script_payment;
        // get last transaction
        $transaction = transaction::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to)->get();

        // get fixed cost
        $salaries = salary::where('status', 'active')->get();
        $salary = 0;
        foreach ($salaries as $key => $sal) {
            $salary = $salary + $sal->amount;
        }
        $house_rent = 39000; //house rent

        return response()->json(['student_payment' => $students, 'class_payment'=>$class_payment, 'invigilator_payment'=>$invigilator_payment, 'script_payment' => $script_payment, 'salary' => $salary, 'house_rent' => $house_rent]);

    }


}
