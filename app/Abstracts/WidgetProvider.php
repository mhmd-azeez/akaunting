<?php

namespace App\Abstracts;

abstract class WidgetProvider
{
    abstract  public function getWidgets(): array;
    abstract public function getWidget(string $widgetName, \App\Models\Common\Widget $model = null): Widget;
}
