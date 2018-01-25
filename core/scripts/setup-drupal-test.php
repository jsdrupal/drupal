#!/usr/bin/env php
<?php

/**
 * @file
 * A command line application to install drupal for tests.
 */

use Drupal\Setup\Commands\TestInstallationSetupApplication;

if (PHP_SAPI !== 'cli') {
  return;
}

// Bootstrap.
$autoloader = require __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../tests/bootstrap.php';

$app = new TestInstallationSetupApplication($autoloader);
$app->run();
