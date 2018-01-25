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
 * Symfony console command to setup Drupal.
 *
 * @internal
 */
class TestInstallationSetupCommand extends Command {

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
    $this->setName('setup-drupal-test')
      ->addOption('setup_class', NULL, InputOption::VALUE_OPTIONAL)
      ->addOption('db_url', NULL, InputOption::VALUE_OPTIONAL, 'URL for database or SIMPLETEST_DB', getenv('SIMPLETEST_DB'))
      ->addOption('base_url', NULL, InputOption::VALUE_OPTIONAL, 'Base URL for site under test or SIMPLETEST_BASE_URL', getenv('SIMPLETEST_BASE_URL'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $db_url = $input->getOption('db_url');
    $base_url = $input->getOption('base_url');
    putenv("SIMPLETEST_DB=$db_url");
    putenv("SIMPLETEST_BASE_URL=$base_url");

    $this->bootstrapDrupal();

    // Manage site fixture.
    $test = new TestInstallationSetup();
    $test->setup('testing', $input->getOption('setup_class'));

    $output->writeln(drupal_generate_test_ua($test->getDatabasePrefix()));
  }

  protected function bootstrapDrupal() {
    $request = Request::createFromGlobals();
    $kernel = DrupalKernel::createFromRequest($request, $this->autoloader, $this->getApplication()->getName());
    DrupalKernel::bootEnvironment($kernel->getAppRoot());

    Settings::initialize(
      dirname(dirname(dirname(dirname(__DIR__)))),
      DrupalKernel::findSitePath($request),
      $this->autoloader
    );
  }

}
