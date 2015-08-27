<?php

use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;

return [
    EmitterInterface::class => get(SapiEmitter::class),
];
