<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;

class OtpMailNotification extends Mailable
{
    use Queueable, SerializesModels;



    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->view('emails.password-reset')
                    ->subject('Your OTP for Password Reset')
                    ->with(['otp' => $this->otp]);
    }
}
