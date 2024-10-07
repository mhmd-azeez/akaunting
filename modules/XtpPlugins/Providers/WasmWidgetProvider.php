<?php

namespace Modules\XtpPlugins\Providers;
use App\Traits\Permissions;

use Modules\XtpPlugins\Utils\XtpApi;
use Modules\XtpPlugins\Widgets\WasmWidgets;
use App\Abstracts\Widget;

class WasmWidgetProvider extends \App\Abstracts\WidgetProvider
{
    use Permissions;
    use XtpApi;

    public function getWidgets(): array
    {
        $url = $this->getPluginContentAddress();

        $plugin = \Modules\XtpPlugins\Utils\XtpPlugin::createPlugin($url);
        $response = $plugin->call('widgets', '');

        $widgets = json_decode($response, true);

        //\Log::info('XtpPlugins::getWidgets() widgets: ' . json_encode($widgets));

        $permissions = [];
        foreach ($widgets as $widget) {
            $name = \App\Utilities\Widgets::getPermission('Modules\\XtpPlugins\\Providers\\WasmWidgetProvider:' . $widget);
            $name = str_replace('read-', '', $name);

            //\Log::info('XtpPlugins::getWidgets() widget: ' . $widget . ' permission: ' . $name);
            $permissions[$name] = 'r';
        }

        // TODO: perhaps this is too heavy handed
        $this->attachPermissionsToAdminRoles($permissions);

        return $widgets;
    }

    public function getWidget(string $widgetName, \App\Models\Common\Widget $model = null): Widget
    {
        return new WasmWidgets($this->getPluginContentAddress(), $widgetName, $model);
    }

    private function getPluginContentAddress()
    {
        $bindings = $this->getPluginBindings();
        if (empty($bindings)) {
            \Log::info('XtpPlugins::getWidgets() no bindings found');
            return null;
        }

        $binding = reset($bindings);

        $url = $this->getPluginContentUrl($binding->contentAddress);

       // \Log::info('XtpPlugins::getWidgets() url: ' . $url);

        return $url;
    }
}
