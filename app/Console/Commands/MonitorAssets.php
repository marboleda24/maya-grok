<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Services\AssetValueService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AssetThresholdReached;

class MonitorAssets extends Command
{
    protected $signature = 'assets:monitor';
    protected $description = 'Monitor asset values and send notifications';

    public function handle()
    {
        $service = new AssetValueService();
        $assets = Asset::with('strategy')->get();

        foreach ($assets as $asset) {
            $currentValue = $service->getCurrentValue($asset->symbol);
            if ($currentValue) {
                // Actualizar valor actual
                $asset->value = $currentValue;

                // Actualizar máximo
                if (!$asset->max_value || $currentValue > $asset->max_value) {
                    $asset->max_value = $currentValue;
                }

                // Actualizar base (solo si no está definida aún)
                if (!$asset->base_value || $currentValue < $asset->base_value) {
                    $asset->base_value = $currentValue;
                }

                $asset->save();

                // Verificar estrategia
                if ($strategy = $asset->strategy) {
                    if ($currentValue <= $strategy->buy_threshold) {
                        Notification::route('mail', $asset->portfolio->user->email)
                            ->notify(new AssetThresholdReached($asset, 'buy'));
                    } elseif ($currentValue >= $strategy->sell_threshold) {
                        Notification::route('mail', $asset->portfolio->user->email)
                            ->notify(new AssetThresholdReached($asset, 'sell'));
                    }
                }
            }
        }

        $this->info('Assets monitored successfully.');
    }
}