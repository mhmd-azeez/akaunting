<?php

namespace Modules\XtpPlugins\Widgets;

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
       // \Log::info('WasmWidgets::show(): ' . $this->default_name);

        $start = microtime(true);

        $plugin = \Modules\XtpPlugins\Utils\XtpPlugin::createPlugin($this->wasmPath);

        $response = $plugin->call('show', json_encode([
            'widgetName' => $this->default_name,
        ]));

        $response = json_decode($response, false);

        $data = get_object_vars($response->data);
        //\Log::info('WasmWidgets::show() data: ' . json_encode($data));

        $end = microtime(true);

        \Log::info('WasmWidgets::show() time: ' . ($end - $start) . ' seconds');

        return $this->view(name: $response->view, data: $data);
    }
}
