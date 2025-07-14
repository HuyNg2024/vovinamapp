<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTP extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    //build email format
    public function build()
    {
        #->from('lehoang652002@gmail.com', 'Your App Name')
        return $this->subject('Mã OTP để đăng ký câu lạc bộ mới')
                    ->view('emails.otp')
                    ->with(['otp' => $this->otp]);
    }

    /**
     * Get the message envelope.
     */

    //default of file system
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Reset Password O T P',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
