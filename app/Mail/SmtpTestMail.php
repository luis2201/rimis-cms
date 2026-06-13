<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build(): self
    {
        return $this
            ->subject('Prueba de correo RIMIS')
            ->view('emails.smtp-test');
    }
}
