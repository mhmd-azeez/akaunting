<?php

namespace Modules\XtpPlugins\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Modules\XtpPlugins\Http\Middleware\XtpPluginMiddleware;

class Event extends Provider
{
    public function shouldDiscoverEvents()
    {
        return true;
    }

    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
