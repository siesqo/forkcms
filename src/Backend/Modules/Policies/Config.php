<?php

namespace Backend\Modules\Policies;

use Backend\Core\Engine\Base\Config as BaseConfig;

final class Config extends BaseConfig
{
    public const MODULE_NAME = 'Policies';

    protected $defaultAction = 'Settings';
}
