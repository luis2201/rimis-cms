<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyResearcherEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirma tu correo electrónico | RIMIS')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Gracias por registrarte en la plataforma RIMIS.')
            ->line('Confirma tu correo electrónico para verificar que la cuenta está operativa y acceder al área privada.')
            ->action('Confirmar correo electrónico', $this->verificationUrl($notifiable))
            ->line('Este enlace de confirmación vencerá en '.config('auth.verification.expire', 60).' minutos.')
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.');
    }
}
