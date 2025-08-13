<?php

namespace App\Http\Controllers\sms;

use App\Http\Controllers\Controller;
use App\Model\Admin\sms_balance;
use App\Model\Admin\sms_report;
use App\Model\Admin\student;
use App\Model\Admin\student_payment;
use Auth;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use stdClass;

trait sms_trait
{    


    // greenweb sms system ==========================
    protected function gw_many_to_many($student, $type, $destination_number)
    {        
        // if student array empty
        if (!sizeof($student)) {
            return ['sms_report' => [], 'balance' => $this->get_balance() . " (No Student Found)"];
        }
        // has insufficient balance
        $has_sufficient_balance = $this->has_sufficient_balance(sizeof($student));
        if ($has_sufficient_balance != 'sufficient') {
            return $has_sufficient_balance;
        }

        $url = 'http://api.greenweb.com.bd/api.php';
        $token = "10136234233169721895303cb07fdc7517676b0332f13585511f5";
        $balance_url = 'http://api.greenweb.com.bd/g_api.php';

        foreach ($student as $key => $std) {
            if ($destination_number == 'student') {
               $std["mobile"] = $std["mobile2"];
            }
            $number = $std["mobile"];
            if ($type == 'attendance_sms') {                
                // $std->message = 'Dear guardian, today (' . Carbon::today()->format('d-M') . ') ' . $std->name . ' is' . ($std->todays_attendance[0]->attendance ? ' present' : ' absent') . '. Sohopath.';
                $std->message = $std->name . ' is' . ($std->todays_attendance[0]->attendance ? ' present' : ' absent ') . 'today (' . Carbon::today()->format('d-M') . '). BT-GEC.';
            }
            if ($type == 'bangla') {
                $std['message'] = rawurlencode($std['message']);
            }
            $json_smsdata[]= ['to'=>$number,'message'=>$std['message']];
        }
        $smsdata = json_encode($json_smsdata);
        $data= array(
            'smsdata'=>"$smsdata",
            'token'=>"$token"
        );
       
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        $smsresult = strip_tags($smsresult, "</br>");
        $smsresult = explode("\n", $smsresult, -1);
        // dd($smsresult);
        
        // has server error
        if (!sizeof($smsresult)) {
            return ['sms_report' => [], 'balance' => "Server Fail. Please contact with the provider"];
        }
        
        // change balance 1st if rest will fail
        $this->calculate_balance($smsresult);
        
        $sms_report = [];
        foreach ($student as $key => $std) {
            $sms_object = new \stdClass;
            $sms_object->student_id = $std['id'];
            $sms_object->reg_no = $std['reg_no'];
            $sms_object->name = $std['name'];
            $sms_object->sms = substr($std['message'], 0, 30);
            $sms_object->sms_text = $std['message'];
            foreach ($smsresult as $key1 => $result) {
                if (str_contains($result, $std["mobile"])) {                    
                    $sms_object->status = str_contains($result, 'Ok:') ? 'OK' : $result;
                    break;
                }                
            }
            array_push($sms_report, $sms_object);
            //insert to databse
            $this->save_sms_report($sms_object);
        }
        return ['sms_report' =>  $sms_report, 'balance' => $this->get_balance()];
    }

    protected function save_sms_report($object)
    {
        sms_report::create(
            ['student_id' => $object->student_id,
            'reg_no' => $object->reg_no,
            'name' => $object->name,
            'sms_text' => $object->sms_text,
            'status' => $object->status]
        );
    }

    protected function calculate_balance($sms_report)
    {
        $new_report_array = array_filter($sms_report, function ($var) {
            return (str_contains($var, 'Ok:'));
        });
        $sms_balance = sms_balance::where('id', 1)->first();

        $expense = sizeof($new_report_array) * $sms_balance->sms_rate;
        // change balance
        $sms_balance->balance = $sms_balance->balance - $expense;
        $sms_balance->save();
        return $sms_balance->balance;
    }

    protected function get_balance()
    {        
        $balance = sms_balance::where('id', 1)->first();
        $bal = number_format((float)$balance->balance, 2, '.', '');
        return $bal;
    }

    protected function has_sufficient_balance($student_qty)
    {
        $balance = sms_balance::where('id', 1)->first();
        if ($student_qty * $balance->sms_rate > $balance->balance) {
            $bal = number_format((float)$balance->balance, 2, '.', '');
            return ['sms_report' => [], 'balance' => $bal . " (You don't have sufficient balance)"];
            // return $bal . " (You don't have sufficient balance)";
        }
        return $balance = 'sufficient';
    }

    protected function gw_sent_to_number($request)
    {        
        $url = 'http://api.greenweb.com.bd/api.php';
        $token = "10136234233169721895303cb07fdc7517676b0332f13585511f5";
        $balance_url = 'http://api.greenweb.com.bd/g_api.php';

        $json_smsdata[]= ['to'=>$request->mobile,'message'=>rawurlencode($request->sms)];

        $smsdata = json_encode($json_smsdata);
        $data= array(
            'smsdata'=>"$smsdata",
            'token'=>"$token"
        );
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);

        $smsresult = strip_tags($smsresult, "</br>");

        $smsresult = explode("\n", $smsresult, -1);
        $sms_report = [];

        return $smsresult;
    }

    protected function gw_balance()
    {        
        $url = 'http://api.greenweb.com.bd/g_api.php';
        $token = "87b6f97f07738e152414fd3d67418333";
        $balance_url = $url . '?token='. $token . '&balance';

        // $client = new \GuzzleHttp\Client();
        // $res = $client->get($balance_url . '?token='. $token . '&balance');
        // return $res->getBody()->getContents();

        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $balance_url);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $balance = curl_exec($ch);
        return $balance;
    }


} //end class
