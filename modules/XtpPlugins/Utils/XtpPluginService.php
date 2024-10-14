<?php

namespace Modules\XtpPlugins\Utils;
use Extism\Plugin;
use Extism\HostFunction;
use Extism\Manifest;
use Extism\Manifest\UrlWasmSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Extism\Manifest\ByteArrayWasmSource;

class XtpPluginService
{
    use XtpApi;

    public function createPlugin(string $url): \Extism\Plugin
    {

        $start = microtime(true);

        $db_query_run = new HostFunction('db_query_run', [\Extism\ExtismValType::I64], [\Extism\ExtismValType::I64], function (string $json) {
            try {
                $input = json_decode($json, true);

                $sql = $input['sql'];
                $bindings = (array) ($input['bindings'] ?? []);
                $result = \DB::select($sql, $bindings);

                $response = [
                    'rows' => $result,
                ];
            } catch (\Exception $e) {
                \Log::error('Error in db_query_run: ' . $e->getMessage());
                $response = [
                    'error' => $e->getMessage(),
                ];
            }

            return json_encode($response);
        });

        $db_prefix_get = new HostFunction('db_prefix_get', [\Extism\ExtismValType::I64], [\Extism\ExtismValType::I64], function ($_) {
            $connection = config('database.default', 'mysql');
            $db_prefix = config("database.connections.$connection.prefix", '');

            return $db_prefix;
        });

        $wasm = $this->getCachedWasmSource($url);
        $manifest = new Manifest($wasm);
        $plugin = new Plugin($manifest, true, [$db_query_run, $db_prefix_get]);

        $end = microtime(true);
        $execution_time = ($end - $start);

        \Log::info('Creating plugin took ' . $execution_time . ' seconds');


        return $plugin;
    }

    private function getCachedWasmSource(string $url): ByteArrayWasmSource
    {
        $cacheKey = 'wasm_file_' . md5($url);

        $wasm = Cache::remember($cacheKey, now()->addDays(30), function () use ($url, &$miss) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('XTP_API_KEY'),
            ])->withOptions([
                        'verify' => false,
                    ])->get($url);

            if ($response->successful()) {
                return new ByteArrayWasmSource($response->body());
            } else {
                throw new \Exception("Failed to fetch WASM file from URL: $url");
            }
        });

        return $wasm;
    }

    public function isXtpEnabled(): bool
    {
        return !is_null(env('XTP_API_KEY')) && !is_null(env('XTP_EXTENSION_POINT_ID'));
    }

    public function getPluginUrl()
    {
        $bindings = $this->getPluginBindings();
        if (empty($bindings)) {
            \Log::info('XtpPlugins::getWidgets() no bindings found');
            return null;
        }

        $binding = reset($bindings);

        $url = $this->getPluginContentUrl($binding->contentAddress);

        return $url;
    }
}
