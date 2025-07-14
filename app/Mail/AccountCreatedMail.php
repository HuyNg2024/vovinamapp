<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $phone;
    public $password;

    public function __construct($phone, $password)
    {
        $this->phone = $phone;
        $this->password = $password;
    }

    public function build()
    {
        return $this->view('emails.account_created')
                    ->subject('Thông tin tài khoản của bạn');
    }
}