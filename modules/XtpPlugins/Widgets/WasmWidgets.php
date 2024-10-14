<?php

namespace Modules\XtpPlugins\Widgets;

use App\Abstracts\Widget;
use Extism\Plugin;

class WasmWidgets extends Widget
{
    public function __construct(Plugin $plugin, string $widgetName, $model = null)
    {
        parent::__construct($model);

        $this->plugin = $plugin;
        $this->default_name = $widgetName;
    }

    public $default_name;
    private $plugin;

    public function show()
    {
        $response = $this->plugin->call('showWidget', json_encode([
            'widgetName' => $this->default_name,
        ]));

        $response = json_decode($response, false);

        $data = get_object_vars($response->data);

        if (request_is_api()) {
            return $data;
        }

        return \Blade::render($response->view, array_merge(['class' => $this], (array) $data));
    }
}
