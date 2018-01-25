<?php

namespace Drupal\Setup\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Application wrapper for TestInstallationSetupCommand.
 *
 * @internal
 */
class TestInstallationSetupApplication extends Application {

  /**
   * The used PHP autoloader.
   *
   * @var object
   */
  protected $autoloader;

  /**
   * SetupDrupalApplication constructor.
   *
   * @param string $autoloader
   *   The used PHP autoloader.
   */
  public function __construct($autoloader) {
    $this->autoloader = $autoloader;
    parent::__construct('setup-drupal-test', '0.0.1');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultCommands() {
    // Even though this is a single command, keep the HelpCommand (--help).
    $default_commands = parent::getDefaultCommands();
    $default_commands[] = new TestInstallationSetupCommand($this->autoloader);
    $default_commands[] = new TestTeardownCommand($this->autoloader);
    return $default_commands;
  }

}
