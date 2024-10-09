<?php

namespace Modules\XtpPlugins\Providers;
use App\Traits\Permissions;

use Modules\XtpPlugins\Utils\XtpPluginService;
use Modules\XtpPlugins\Utils\XtpApi;
use Modules\XtpPlugins\Widgets\WasmWidgets;
use App\Abstracts\Widget;

class WasmWidgetProvider extends \App\Abstracts\WidgetProvider
{
    use Permissions;

    public function getWidgets(): array
    {
        $service = new XtpPluginService();
        $url = $service->getPluginUrl();
        $plugin = $service->createPlugin($url);
        $response = $plugin->call('getWidgets', '');

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
        $service = new XtpPluginService();
        $url = $service->getPluginUrl();
        $plugin = $service->createPlugin($url);

        return new WasmWidgets($plugin , $widgetName, $model);
    }

}
