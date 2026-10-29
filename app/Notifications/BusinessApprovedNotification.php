<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessApprovedNotification extends Notification
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
            ->subject('Negocio aprobado — DescuentoNic')
            ->greeting('Hola '.$notifiable->name)
            ->line('¡Buenas noticias! "'.$this->business->name.'" ya está aprobado.')
            ->line('Ya podés publicar cupones en el panel de negocios.')
            ->action('Publicar cupones', url('/negocio'))
            ->salutation('Equipo DescuentoNic');
    }
}
