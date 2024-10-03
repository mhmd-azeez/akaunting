<?php

namespace Modules\XtpPlugins\Utils;
use Extism\Plugin;
use Extism\HostFunction;
use Extism\Manifest;
use Extism\Manifest\UrlWasmSource;

class XtpPlugin
{
    public static function createPlugin(string $path): \Extism\Plugin {
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

        $db_prefix_get = new HostFunction('db_prefix_get', [\Extism\ExtismValType::I64], [\Extism\ExtismValType::I64], function ($_) {
            $connection = config('database.default', 'mysql');
            $db_prefix = config("database.connections.$connection.prefix", '');

            return $db_prefix;
        });

        $wasm = new UrlWasmSource($path);
        $wasm->headers->Authorization = 'Bearer ' . setting('xtp-plugins.xtp_token');
        $manifest = new Manifest($wasm);
        $plugin = new Plugin($manifest, true, [$db_query_run, $db_prefix_get]);

        return $plugin;
    }
}
