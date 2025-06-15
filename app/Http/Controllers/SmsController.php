<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    public function sendMessage()
    {

        // $sid = getenv("TWILIO_SID");
        // $token = getenv("TWILIO_TOKEN");
        // $twilio = new Client($sid, $token);

        // $validation_request = $twilio->validationRequests->create(
        //     "+819070422499", // PhoneNumber
        //     ["friendlyName" => "My Home Phone Number"]
        // );
        // print $validation_request->accountSid;
        // dd(123);



        $receiverNumber = '+819072664003'; // Replace with the recipient's phone number
        $message = 'hi testing';

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $fromNumber = env('TWILIO_NUMBER');
        try {
            $client = new Client($sid, $token);
            $client->messages->create($receiverNumber, [
                'from' => $fromNumber,
                'body' => $message
            ]);

            return 'SMS Sent Successfully.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
