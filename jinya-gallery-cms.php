<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
// read https://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'prod');

$kernel = new AppKernel($env, false);
$kernel->handle(Request::createFromGlobals())->send();