<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class ContentSubmissionStatusNotification extends Notification
{
    use Queueable;
    public function __construct(public object $submission, public string $typeLabel, public string $event, public ?string $notes = null) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $labels=['submitted'=>'Publicación enviada','under_review'=>'Revisión iniciada','observed'=>'Aporte observado','approved'=>'Aporte aprobado','rejected'=>'Aporte rechazado','published'=>'Aporte publicado','unpublished'=>'Aporte despublicado'];
        $mail=(new MailMessage)->subject(($labels[$this->event] ?? 'Actualización de aporte').' | RIMIS')->greeting('Hola, '.$notifiable->name)->line("{$this->typeLabel}: {$this->submission->title}")->line('Estado: '.($labels[$this->event] ?? $this->event).'.');
        if ($this->notes) $mail->line('Observación: '.$this->notes);
        if ($this->event === 'published') {
            $route = match (class_basename($this->submission)) { 'Event' => 'events.show', 'Bulletin' => 'bulletins.show', 'ResearchPublication' => 'research-publications.show', default => 'calls.show' };
            $mail->line('Enlace público: '.route($route, $this->submission->slug));
        }
        $isResearchPublication = class_basename($this->submission) === 'ResearchPublication';
        return $mail->action($isResearchPublication ? 'Ver mis publicaciones' : 'Ver mis aportes', route($isResearchPublication ? 'researcher.publications.index' : 'researcher.submissions.index'));
    }
}
