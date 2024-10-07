<?php

namespace Modules\XtpPlugins\Utils;
use Extism\Plugin;
use Extism\HostFunction;
use Extism\Manifest;
use Extism\Manifest\UrlWasmSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Extism\Manifest\ByteArrayWasmSource;

class XtpPlugin
{
    public static function createPlugin(string $url): \Extism\Plugin {

        $start = microtime(true);

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

        $wasm = XtpPlugin::getCachedWasmSource($url);
        $manifest = new Manifest($wasm);
        $plugin = new Plugin($manifest, true, [$db_query_run, $db_prefix_get]);

        $end = microtime(true);
        $execution_time = ($end - $start);

        \Log::info('Creating plugin took ' . $execution_time . ' seconds');


        return $plugin;
    }

    private static function getCachedWasmSource(string $url): ByteArrayWasmSource
    {
        // print clock time in seconds

        $now = microtime(true);

        $cacheKey = 'wasm_file_' . md5($url);
        $miss = false;

        $wasm = Cache::remember($cacheKey, now()->addDays(30), function () use ($url, &$miss) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . setting('xtp-plugins.xtp_token'),
            ])->withOptions([
                'verify' => false,
            ])->get($url);

            \Log::info('fetching WASM file from URL: ' . $url);
            $miss = true;

            if ($response->successful()) {
                return new ByteArrayWasmSource($response->body());
            } else {
                throw new \Exception("Failed to fetch WASM file from URL: $url");
            }
        });

        $end = microtime(true);
        $execution_time = ($end - $now);

        \Log::info('Fetching plugin took ' . $execution_time . ' seconds. Cache miss: ' . ($miss ? 'true' : 'false'));
        return $wasm;
    }

}
