#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Pdpaola\CoffeeMachine\Console\MakeDrinkCommand;
use Pdpaola\CoffeeMachine\Console\OrderService;
use Pdpaola\CoffeeMachine\Console\ShowEarningsCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new MakeDrinkCommand());
$application->add(new ShowEarningsCommand(new OrderService()));

$application->run();
