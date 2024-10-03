<?php

namespace Modules\XtpPlugins\Listeners;

use App\Events\Widget\ClassesCreated as Event;
use Modules\XtpPlugins\Providers\WasmWidgetProvider;

class WidgetsCreated
{
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        $provider = new WasmWidgetProvider();
        $widgets = $provider->getWidgets();

        foreach ($widgets as $widget) {
            \Log::info('Adding widget: ' . $widget);
            $event->list->add('Modules\XtpPlugins\Providers\WasmWidgetProvider:' . $widget);
        }
    }
}
