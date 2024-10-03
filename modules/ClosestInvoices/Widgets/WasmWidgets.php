<?php

namespace Modules\ClosestInvoices\Widgets;

use App\Abstracts\Widget;
use App\Models\Document\Document;
use App\Utilities\Date;

use Extism\ExtismValType;
use Extism\HostFunction;
use Extism\Plugin;
use Extism\Manifest;
use Extism\Manifest\PathWasmSource;

class WasmWidgets extends Widget
{
    public $default_name = 'Wasm widgets';

    public function show()
    {
        \Log::info('ClosestPayables::show()');

        $db_query_run = new HostFunction('db_query_run', [\Extism\ExtismValType::I64], [\Extism\ExtismValType::I64], function (string $json) {
            $input = json_decode($json, true);

            $sql = $input['sql'];
            $bindings = (array) ($input['bindings'] ?? []);
            $result = \DB::select($sql, $bindings);

            $response = [
                'rows' => $result,
            ];

            return json_encode($response);
        });

        $db_prefix_get = new HostFunction('db_prefix_get', [], [\Extism\ExtismValType::I64], function () {
            $connection = config('database.default', 'mysql');
            $db_prefix = config("database.connections.$connection.prefix", '');

            return $db_prefix;
        });

        $wasm = new PathWasmSource('D:\\x\\php\\akaunting-plugins\\ClosestInvoices\\dist\\plugin.wasm');
        $manifest = new Manifest($wasm);
        $plugin = new Plugin($manifest, true, [$db_query_run, $db_prefix_get]);

        $response = $plugin->call('show', json_encode([
            'widgetName' => 'ClosestPayables',
        ]));

        $response = json_decode($response, false);

        $data = get_object_vars($response->data);
        \Log::info('ClosestPayables::show() data: ' . json_encode($data));

        return $this->view(name: $response->view, data: $data);
    }
}
