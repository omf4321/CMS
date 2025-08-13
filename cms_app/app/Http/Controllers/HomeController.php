<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\Model\Admin\random_number;
use App\Model\Admin\payment_confirm;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;
use Excel;
use App\Imports\ExamImport;


class HomeController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function add_question($rand)
    {        
        $random = random_number::first();
        if ($random && $random->updated_at < Carbon::now()->subHours(3)->toDateTimeString()) {
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        $random = random_number::where('random_number', $rand)->first();
        if ($random) {
            return view('user.layouts.question_home_layout');
        }
        return redirect('/');
    }

    public function test_data(Request $request)
    {
        // Excel::import(new ExamImport, storage_path('sample_50.xlsx'));
    }

    public function payment_confirm_form()
    {
        return view('payment_confirm');
    }

    public function payment_confirm(Request $request)
    {
        $this->validate($request,[
            'reg_no' => 'required',
            'pay_to' => 'required',
            'bkash_number' => 'required|max:11|min:4'
        ]);

        $payment = payment_confirm::where('reg_no', $request->reg_no)->where('month', $request->month)->first();

        if ($payment) {
            return redirect()->back()->with('error', 'Reg No Already Exist!');
        }

        $payment = payment_confirm::where('bkash_number', $request->bkash_number)->where('month', $request->month)->first();

        if ($payment) {
            return redirect()->back()->with('error', 'Records Already Exist!');
        }

        $payment = new payment_confirm;
        $payment->reg_no = $request->reg_no;
        $payment->bkash_number = $request->bkash_number;
        $payment->pay_to = $request->pay_to;
        $payment->month = $request->month;
        $payment->save();

        return redirect()->back()->with('message', 'Save Succesfully!');
    }
}
