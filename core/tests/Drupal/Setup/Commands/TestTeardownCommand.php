<?php

namespace Drupal\Setup\Commands;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\Setup\TestInstallationSetup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tears down a test Drupal.
 *
 * @internal
 */
class TestTeardownCommand extends Command {

  /**
   * The used PHP autoloader.
   *
   * @var object
   */
  protected $autoloader;

  /**
   * Constructs a new TestInstallationSetupCommand.
   *
   * @param string $autoloader
   *   The used PHP autoloader.
   * @param string|null $name
   *   The name of the command. Passing NULL means it must be set in
   *   configure().
   */
  public function __construct($autoloader, $name = NULL) {
    parent::__construct($name);

    $this->autoloader = $autoloader;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('teardown-drupal-test')
      ->addArgument('db_prefix')
      ->addOption('db_url', NULL, InputOption::VALUE_OPTIONAL, 'URL for database or SIMPLETEST_DB', getenv('SIMPLETEST_DB'))
      ->addOption('base_url', NULL, InputOption::VALUE_OPTIONAL, 'Base URL for site under test or SIMPLETEST_BASE_URL', getenv('SIMPLETEST_BASE_URL'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $db_url = $input->getOption('db_url');
    $db_prefix = $input->getArgument('db_prefix');
    $base_url = $input->getOption('base_url');
    putenv("SIMPLETEST_DB=$db_url");
    putenv("SIMPLETEST_BASE_URL=$base_url");

    $this->bootstrapDrupal($db_prefix);

    // Manage site fixture.
    $test = new TestInstallationSetup();
    $test->teardown($db_prefix);
  }

  protected function bootstrapDrupal($db_prefix) {
    $request = Request::createFromGlobals();
    $_COOKIE['SIMPLETEST_USER_AGENT'] = drupal_generate_test_ua($db_prefix);

    $kernel = DrupalKernel::createFromRequest($request, $this->autoloader, $this->getApplication()->getName());
    DrupalKernel::bootEnvironment($kernel->getAppRoot());

    Settings::initialize(
      dirname(dirname(dirname(dirname(__DIR__)))),
      DrupalKernel::findSitePath($request),
      $this->autoloader
    );
  }

}
