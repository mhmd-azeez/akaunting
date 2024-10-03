<?php

namespace Modules\ClosestInvoices\Providers;

use Modules\ClosestInvoices\Widgets\WasmWidgets;
use App\Abstracts\Widget;

class WasmWidgetProvider extends \App\Abstracts\WidgetProvider
{
    public function getWidgets(): array
    {
        return [
            'ClosestPayables',
        ];
    }

    public function getWidget(string $widgetName, \App\Models\Common\Widget $model = null): Widget
    {
        return new WasmWidgets($model);
    }
}
