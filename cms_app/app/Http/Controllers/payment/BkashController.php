<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Model\Admin\invoice;
use App\Model\Admin\sms_balance;
use App\Model\Admin\sms_recharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class BkashController extends Controller
{
    private $base_url;
    private $app_key;
    private $app_secret;
    private $username;
    private $password;

    public function __construct()
    {
        // bKash Merchant API Information
        $this->app_key = "YZnGWPOgK3Kge8uoHwHffLiotc";
        $this->app_secret = "aRmIS44TCYyyiogc3gSE8h5UTNGPZYmnickcjnkEfeR3pN2Jic65";
        $this->username = "01326599338";
        $this->password = "7I5jP2=M)b@";
        $this->base_url = "https://tokenized.pay.bka.sh/v1.2.0-beta";
    }

    public function index($id)
    {
        $invoice = invoice::find($id);
        return view('bkash-payment', compact('invoice'));
    }

    public function sms_payment()
    {
        return view('bkash-payment-sms');
    }

    public function get_invoice()
    {        
        $invoices = invoice::orderBy('created_at', 'desc')->limit(12)->get();        
        return response()->json(['invoices' => $invoices]);
    }

    public function get_sms_recharge_history()
    {        
        sms_recharge::where('status', 'unpaid')->delete();
        $sms_history = sms_recharge::where('status', 'paid')->orderBy('created_at', 'desc')->limit(12)->get();
        $balance = sms_balance::first();
        return response()->json(['sms_history' => $sms_history, 'balance' => $balance->balance]);
    }

    public function get_invoice_by_id($id)
    {        
        $invoice = invoice::find($id);
        return view('invoice', compact('invoice'));
    }

    public function payment_complete_for_trxid(Request $request)
    {
        if ($request->product_type == 'sms') {
            $payment = sms_recharge::orderBy('id', 'desc')->first();
            $payment->type = 'sms';
        } else {
            $payment = invoice::orderBy('id', 'desc')->first();
            $payment->type = 'software';
        }
        $payment->status = $request->status;
        return view('payment_complete', compact('payment'));
    }

    public function payment_complete(Request $request)
    {
        if (session()->get('sms_payment_id') == $request->paymentID) {
            $payment = sms_recharge::where('payment_id', $request->paymentID)->where('status', 'unpaid')->first();
            $request->product_type = 'sms';
            $payment->type = 'sms';
        } else {
            $payment =  invoice::where('payment_id', $request->paymentID)->where('status', 'unpaid')->first();
            $request->product_type = 'software';
            $payment->type = 'software';
        }

        if ($payment && $request->status == 'success') {
            $request->invoice_id = $payment->id;
            $payment_execute = $this->executePayment($request);
            if ($payment_execute == 'successful') {                
                $payment->status = 'successful';
            } else {
                $payment->status = 'failure';
            }
            return view('payment_complete', compact('payment'));
        } else {
            return redirect()->intended('/admin/home/billing_invoice');
        }
    }

    public function getToken(Request $request)
    {
        // return $request->all();
        // return $this->searchTransaction($request);
        session()->forget('bkash_token');

        $request_data = array(
            'app_key'=> $this->app_key,
            'app_secret' => $this->app_secret
        );  

        $url = curl_init($this->base_url.'/tokenized/checkout/token/grant');
        $request_data_json=json_encode($request_data);
        $header = array(
                'Content-Type:application/json',
                "password:$this->password",
                "username:$this->username"
                );              
        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $resultdata = curl_exec($url);
        curl_close($url);

        $response = json_decode($resultdata, true);

        if (array_key_exists('errorCode', $response)) {
            return $response;
        }

        session()->put('bkash_token', $response['id_token']); 

        if ($request->payment_type == 'cash_in') {
            $searchTransaction = $this->searchTransaction($request);
            // return redirect()->intended('/payment-successful');
            return redirect()->route('bkash.payment.success', ['product_type' => $request->product_type, 'status' => $searchTransaction]);
            
        } else {
            $this->createPayment($request);
        }

    }

    protected function createPayment($request)
    {
        // return $this->createSMSRecharge($request, $obj);
        // session()->put('sms_payment_id', $obj['paymentID']);

        $auth = session()->get('bkash_token');
        
        $callbackURL='https://www.btcoaching.org/payment-complete';
        $requestbody = array(
            'mode' => '0011',
            'amount' => ceil($request->amount),
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => 'BT',
            'merchantInvoiceNumber' => rand(),
            'callbackURL' => $callbackURL
        );
        $url = curl_init($this->base_url.'/tokenized/checkout/create');                     
        $requestbodyJson = json_encode($requestbody);
        
        $header = array(
        'Content-Type:application/json',
        'Authorization:' . $auth,
        'X-APP-Key:' . $this->app_key
            );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);
        
        $obj = json_decode($resultdata, true);
        if ($obj['statusMessage'] != 'Successful') {
            $payment = invoice::where('id', $request->invoice_id)->first();
            $payment->status = 'failure';
            return view('payment_complete', compact('payment'));
        }
        if ($request->product_type == 'sms') {
            session()->forget('sms_payment_id');
            $this->createSMSRecharge($request, $obj);
            session()->put('sms_payment_id', $obj['paymentID']);
        }
        if ($request->product_type == 'software') {
            $this->updateInvoice($request, $obj);                
        }

        header("Location: " . $obj['bkashURL']);
            
    }

    protected function createSMSRecharge($request, $obj){

        $transaction_time = Null;
        if (isset($obj['completedTime'])) {
            $transaction_time = substr($obj['completedTime'],0,10) . " " .  substr($obj['completedTime'],11,8);
        }
        if (isset($obj['paymentExecuteTime'])) {
            $transaction_time = substr($obj['paymentExecuteTime'],0,10) . " " .  substr($obj['paymentExecuteTime'],11,8);
        }

        if (isset($obj['trxID'])) {
            $sms_recharge = sms_recharge::where('payment_id', $request->paymentID)->first();
        } else {
            $sms_recharge = new sms_recharge;
        }
        $sms_recharge->payment_id = isset($obj['paymentID']) ? $obj['paymentID'] : \DB::raw('payment_id');
        $sms_recharge->invoice_to = "Towhidul Islam<br/>Oxygen, Chattogram, <br />Chattogram, Chattogram, 4213<br />Bangladesh";
        $month = $request->sms_quantity > 1999 ? 6 : 3;
        $sms_recharge->validity_date = Carbon::today()->addMonths($month);
        $sms_recharge->validity_for = $month . ' Months';
        $sms_recharge->sms_quantity = $request->sms_quantity ? $request->sms_quantity : \DB::raw('sms_quantity');
        $sms_recharge->amount = isset($obj['trxID']) ? $obj['amount'] : $request->amount;
        $sms_recharge->status = isset($obj['trxID']) ? 'paid' : 'unpaid';
        $sms_recharge->trxid = isset($obj['trxID']) ? $obj['trxID'] : Null;
        $sms_recharge->payment_method = 'bkash';
        $sms_recharge->transaction_time = $transaction_time;
        $sms_recharge->merchant_invoice_number = isset($obj['merchantInvoiceNumber']) ? $obj['merchantInvoiceNumber'] : Null;
        $sms_recharge->payment_from = isset($obj['customerMsisdn']) ? $obj['customerMsisdn'] : Null;
        $sms_recharge->save();

        if ($sms_recharge->trxid) {
            $balance = sms_balance::first();
            $balance->balance = $balance->balance + $sms_recharge->amount;
            $balance->save();
        }

        return $sms_recharge;
    }

    protected function updateInvoice($request, $obj){
        $transaction_time = Null;
        if (isset($obj['completedTime'])) {
            $transaction_time = substr($obj['completedTime'],0,10) . " " .  substr($obj['completedTime'],11,8);
        }
        if (isset($obj['paymentExecuteTime'])) {
            $transaction_time = substr($obj['paymentExecuteTime'],0,10) . " " .  substr($obj['paymentExecuteTime'],11,8);
        }

        invoice::where('id', $request->invoice_id)->update
        ([  
            'status' => isset($obj['trxID']) ? 'paid' : 'unpaid', 
            'paid' => isset($obj['trxID']) ? $obj['amount'] : 0,
            'trxid'=> isset($obj['trxID']) ? $obj['trxID'] : Null, 
            'transaction_time' => $transaction_time, 
            'merchant_invoice_number' => isset($obj['merchantInvoiceNumber']) ? $obj['merchantInvoiceNumber'] : Null, 
            'payment_from' => isset($obj['customerMsisdn']) ? $obj['customerMsisdn'] : Null,
            'payment_id' => isset($obj['paymentID']) ? $obj['paymentID'] : \DB::raw('payment_id')
        ]);
    }


    public function executePayment($request)
    {
        $token = session()->get('bkash_token');
        $paymentID = $request->paymentID;

        $post_token = array(
           'paymentID' => $request->paymentID
        );

        $url = curl_init($this->base_url.'/tokenized/checkout/execute');       
        $posttoken = json_encode($post_token);
            
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $token,
            'X-APP-Key:' . $this->app_key
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);        
        curl_close($url);
        
        $obj = json_decode($resultdata, true);

        if ($obj['statusMessage'] == 'Successful') {
            if ($request->product_type == 'sms') {
                $this->createSMSRecharge($request, $obj);
            } else {
                $this->updateInvoice($request, $obj);
            }
            return 'successful';
        } else {
            return 'failure';
            // return $obj;
        }
    }

    public function queryPayment(Request $request)
    {
        $token = session()->get('bkash_token');
        $paymentID = $request->payment_info['payment_id'];

        // $url = curl_init("$this->base_url/checkout/payment/query/" . $paymentID);
        $url = curl_init($this->base_url.'/tokenized/checkout/payment/status');
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $token,
            'X-APP-Key:' . $this->app_key
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);
        return json_decode($resultdata, true);
    }

    public function searchTransaction($request)
    {
        $token = session()->get('bkash_token');

        $requestbody = array(
            'trxID' => $request->trxid
        );
        $requestbodyJson = json_encode($requestbody);

        $url = curl_init($this->base_url.'/tokenized/checkout/general/searchTransaction');

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $token,
            'X-APP-Key:' . $this->app_key
        );

        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        $resultdatax = curl_exec($url);
        curl_close($url);
        $obj = json_decode($resultdatax, true);        

        if ($obj['statusMessage'] == 'Successful') {
            if ($request->product_type == 'sms') {
                $payment = $this->createSMSRecharge($request, $obj);
            } else {
                $payment = invoice::where('status', '!=', 'paid')->first();
                $payment->status = 'paid';
                $payment->paid = $obj['amount'];
                $payment->trxid = $obj['trxID'];
                $payment->transaction_time = substr($obj['completedTime'],0,10) . " " .  substr($obj['completedTime'],11,8);
                $payment->payment_from = $obj['customerMsisdn'];
                $payment->save();
                if ($payment->amount > $obj['amount']) {
                    $payment_new = new invoice;
                    $payment_new->amount = $payment->amount - $obj['amount'];
                    $payment_new->status = 'unpaid';
                    $payment_new->due_date = $payment->due_date;
                    $payment_new->date_from = $payment->date_from;
                    $payment_new->date_to = $payment->date_to;
                    $payment_new->invoice_to = $payment->invoice_to;
                    $payment_new->paid = 0;
                    $payment_new->save();
                }
                // $this->updateInvoice($request, $obj);
            }
            return 'successful';            
        } else {
            return 'failure';
        }
    }

    public function bkashSuccess(Request $request)
    {
        // IF PAYMENT SUCCESS THEN YOU CAN APPLY YOUR CONDITION HERE
        if ('Noman' == 'success') {

            // THEN YOU CAN REDIRECT TO YOUR ROUTE

            session()->flash('successMsg', 'Payment has been Completed Successfully');

            return response()->json(['status' => true]);
        }

        session()->flash('error', 'Noman Error Message');

        return response()->json(['status' => false]);
    }
}