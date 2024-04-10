<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMainRequestDetailsToExpert extends Mailable
{
    use Queueable, SerializesModels;
    public $emailData;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData)
    {   
        $this->emailData = $emailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->emailData);
        return $this->view('email.SendMainRequestDetailsExpert')->subject('Maintenance Details')->with($this->emailData);

    }
}
