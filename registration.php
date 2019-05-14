<?php

use Magento\Framework\Component\ComponentRegistrar;

require __DIR__ . '/vendor/autoload.php';

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Onlinepets_AutoLoginAdmin',
    __DIR__
);
