<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewTenantSignup extends Mailable
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
        return $this->view('email.pm_get_new_tenant_details')->subject('New Tenant Signup')->with($this->emailData);

    }
}
