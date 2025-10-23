<?php

namespace App\Http\Controllers;

use App\Mail\EmailApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    //

    public function sendEmail(){

        $toEmail = "clifordmillan2025@gmail.com";
        $message = "testng rako aprt";
        $subject = 'tagum city';

       $response =  Mail::to($toEmail)->send(new EmailApi($message, $subject));

       dd($response);
    }
}
