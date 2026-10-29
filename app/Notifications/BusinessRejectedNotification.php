<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public Business $business) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitud de negocio rechazada — DescuentoNic')
            ->greeting('Hola '.$notifiable->name)
            ->line('La solicitud del negocio "'.$this->business->name.'" no fue aprobada.')
            ->line('Motivo: '.($this->business->rejection_reason ?: 'No especificado.'))
            ->line('Podés actualizar los datos y volver a solicitar revisión desde el panel de negocios.')
            ->action('Ir al panel', url('/negocio'))
            ->salutation('Equipo DescuentoNic');
    }
}
