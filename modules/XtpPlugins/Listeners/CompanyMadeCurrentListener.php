<?php

namespace Modules\XtpPlugins\Listeners;

use App\Events\Common\CompanyMadeCurrent;
use Modules\XtpPlugins\Providers\WasmWidgetProvider;
use Illuminate\Support\Facades\Event;
use Modules\XtpPlugins\Utils\XtpPluginService;

class CompanyMadeCurrentListener
{
    private static $additionalListenerRegistered = false;

    /**
     * Handle the event.
     *
     * @param  CompanyMadeCurrent $event
     * @return void
     */
    public function handle(CompanyMadeCurrent $event)
    {
        if (!self::$additionalListenerRegistered) {
            $this->registerAdditionalListener();
            self::$additionalListenerRegistered = true;
        }
    }

    private function registerAdditionalListener()
    {
        $service = new XtpPluginService();
        if (!$service->isXtpEnabled()) {
            return;
        }

        $url = $service->getPluginUrl();

        $plugin = $service->createPlugin($url);

        $listeners = json_decode($plugin->call('getEventListeners', ''), true);

        foreach ($listeners as $listener) {
            if (str_contains($listener, '*')) {
                Event::listen($listener, function ($eventName, $event) use ($plugin) {
                    return $this->handlePluginEvent($plugin, $eventName, $event);
                });
            } else {
                Event::listen($listener, function ($event) use ($plugin, $listener) {
                    return $this->handlePluginEvent($plugin, $listener, $event);
                });
            }
        }
    }

    private function handlePluginEvent($plugin, string $eventName, $eventData)
    {
        $response = json_decode($plugin->call('handleEvent', json_encode([
            'event' => $eventData,
            'eventName' => $eventName
        ])));

        $this->updateModelAttributes($eventData, $response->event);

        return $response->stopPropagation ?? true;
    }

    private function updateModelAttributes($model, $responseEvent): void
    {
        $fillable = $model->getFillable();
        foreach ($fillable as $attribute) {
            if (isset($responseEvent->$attribute)) {
                $model->$attribute = $responseEvent->$attribute;
            }
        }
    }
}
