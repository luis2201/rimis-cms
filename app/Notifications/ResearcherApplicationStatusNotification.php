<?php

namespace App\Notifications;

use App\Models\ResearcherApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResearcherApplicationStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public ResearcherApplication $application, public string $event)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $labels = [
            'submitted' => 'Postulación enviada',
            'under_review' => 'Revisión iniciada',
            'observed' => 'Postulación observada',
            'approved' => 'Postulación aprobada',
            'rejected' => 'Postulación rechazada',
            'withdrawn' => 'Postulación retirada',
        ];
        $message = (new MailMessage)
            ->subject(($labels[$this->event] ?? 'Actualización de postulación').' | RIMIS')
            ->greeting('Hola, '.$notifiable->name)
            ->line('El estado de tu postulación es: '.($this->application::STATUS_LABELS[$this->application->status] ?? $this->application->status).'.');

        if ($this->event === 'approved') {
            $message->line('Tu postulación fue aprobada. Ya formas parte de la Red de Investigadores RIMIS.');
        }
        if (in_array($this->event, ['observed', 'rejected'], true) && $this->application->review_notes) {
            $message->line('Observación: '.$this->application->review_notes);
        }

        return $message->action('Ver mi postulación', route('applications.show'));
    }
}
