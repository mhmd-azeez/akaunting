<?php

namespace Modules\XtpPlugins\Providers;

use Modules\ClosestInvoices\Widgets\WasmWidgets;
use App\Abstracts\Widget;

class WasmWidgetProvider extends \App\Abstracts\WidgetProvider
{
    private $path = "D:\\x\\php\\akaunting-plugins\\XtpPlugins\\dist\\plugin.wasm";

    public function getWidgets(): array
    {
        $plugin = \Modules\XtpPlugins\Utils\XtpPlugin::createPlugin($this->path);
        $response = $plugin->call('widgets', '');

        $widgets = json_decode($response, true);

        \Log::info('XtpPlugins::getWidgets() widgets: ' . json_encode($widgets));

        return $widgets;
    }

    public function getWidget(string $widgetName, \App\Models\Common\Widget $model = null): Widget
    {
        return new WasmWidgets($this->path, $widgetName, $model);
    }
}
