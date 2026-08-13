<?php
namespace App\Notifications;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class SubscriptionStatusNotification extends Notification
{
    use Queueable;
    public function __construct(public Subscription $subscription, public string $event, public ?string $temporaryPassword=null){}
    public function via($notifiable):array{return ['mail'];}
    public function toMail($notifiable):MailMessage
    {
        $s=$this->subscription;
        $mail=(new MailMessage)->subject('Suscripción RIMIS: '.(Subscription::STATUS_LABELS[$s->status]??$s->status))->greeting('Hola, '.$s->displayName().'.');
        if($this->event==='submitted') return $mail->line('Recibimos tu suscripción RIMIS correctamente.')->line('Estado: Enviada. Te notificaremos cuando comience la revisión.');
        if($this->event==='under_review') return $mail->line('Tu suscripción se encuentra en revisión.');
        if($this->event==='rejected') return $mail->line('Tu suscripción fue rechazada.')->line('Observaciones: '.$s->review_notes);
        return $mail->line('Tu suscripción fue aprobada y tu membresía RIMIS está activa.')
            ->line('Usuario: '.$s->email)->line('Contraseña temporal: '.$this->temporaryPassword)
            ->line('Por seguridad, deberás cambiarla al iniciar sesión por primera vez.')->action('Ingresar a RIMIS',route('login'));
    }
}
