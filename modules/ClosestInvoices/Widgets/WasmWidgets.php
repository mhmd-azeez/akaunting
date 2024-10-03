<?php

namespace Modules\ClosestInvoices\Widgets;

use App\Abstracts\Widget;

class WasmWidgets extends Widget
{
    public function __construct(string $wasmPath, string $widgetName, $model = null)
    {
        parent::__construct($model);

        $this->wasmPath = $wasmPath;
        $this->default_name = $widgetName;
    }

    public $default_name;
    private $wasmPath;

    public function show()
    {
        \Log::info('ClosestPayables::show()');

        $plugin = \Modules\ClosestInvoices\Utils\XtpPlugin::createPlugin($this->wasmPath);

        $response = $plugin->call('show', json_encode([
            'widgetName' => 'ClosestPayables',
        ]));

        $response = json_decode($response, false);

        $data = get_object_vars($response->data);
        \Log::info('ClosestPayables::show() data: ' . json_encode($data));

        return $this->view(name: $response->view, data: $data);
    }
}
