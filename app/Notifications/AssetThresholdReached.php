<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetThresholdReached extends Notification
{
    use Queueable;

    protected $asset;
    protected $action;

    public function __construct($asset, $action)
    {
        $this->asset = $asset;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $actionText = $this->action === 'buy' ? 'Comprar' : 'Vender';
        return (new MailMessage)
                    ->subject("Acción requerida para {$this->asset->name}")
                    ->line("El valor de {$this->asset->name} ha alcanzado \${$this->asset->value}.")
                    ->line("Se recomienda {$actionText} según la estrategia definida.")
                    ->action('Ver Activo', route('assets.index', $this->asset->portfolio_id));
    }
}