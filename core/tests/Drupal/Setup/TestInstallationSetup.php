<?php

namespace Drupal\Setup;

use Drupal\Core\Database\Database;
use Drupal\Core\Test\FunctionalTestSetupTrait;
use Drupal\Core\Test\TestDatabase;
use Drupal\Core\Test\TestSetupTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\SessionTestTrait;

/**
 * Provides a class used by setup-drupal-test.php to install Drupal for tests.
 *
 * @internal
 */
class TestInstallationSetup {

  use FunctionalTestSetupTrait;
  use RandomGeneratorTrait;
  use SessionTestTrait;
  use TestSetupTrait;

  /**
   * The install profile to use.
   *
   * @var string
   */
  protected $profile;

  /**
   * Time limit in seconds for the test.
   *
   * @var int
   */
  protected $timeLimit = 500;

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  /**
   * Creates a test drupal installation.
   *
   * @param string $profile
   *   (optional) The installation profile to use.
   * @param string $setup_class
   *   (optional) Setup class. A PHP class to setup configuration used by the
   *   test.
   */
  public function setup($profile = 'testing', $setup_class = NULL) {
    $this->profile = $profile;
    $this->setupBaseUrl();
    $this->prepareEnvironment();
    $this->installDrupal();

    if ($setup_class) {
      $this->executeSetupClass($setup_class);
    }
  }

  /**
   * Removes a given instance by deleting all the database tables.
   *
   * @param string $db_prefix
   *   The used database prefix.
   */
  public function teardown($db_prefix) {
    $tables = Database::getConnection()->schema()->findTables('%');
    foreach ($tables as $table) {
      if (Database::getConnection()->schema()->dropTable($table)) {
        unset($tables[$table]);
      }
    }

    // Delete test site directory.
    $test_database = new TestDatabase($db_prefix);
    file_unmanaged_delete_recursive($test_database->getTestSitePath(), [$this, 'filePreDeleteCallback']);
  }

  /**
   * Ensures test files are deletable within file_unmanaged_delete_recursive().
   *
   * Some tests chmod generated files to be read only. During
   * BrowserTestBase::cleanupEnvironment() and other cleanup operations,
   * these files need to get deleted too.
   *
   * @param string $path
   *   The file path.
   */
  public static function filePreDeleteCallback($path) {
    // When the webserver runs with the same system user as phpunit, we can
    // make read-only files writable again. If not, chmod will fail while the
    // file deletion still works if file permissions have been configured
    // correctly. Thus, we ignore any problems while running chmod.
    @chmod($path, 0700);
  }

  /**
   * Gets the database prefix.
   *
   * @return string
   */
  public function getDatabasePrefix() {
    return $this->databasePrefix;
  }

  /**
   * Installs Drupal into the Simpletest site.
   */
  protected function installDrupal() {
    $this->initUserSession();
    $this->prepareSettings();
    $this->doInstall();
    $this->initSettings();
    $container = $this->initKernel(\Drupal::request());
    $this->initConfig($container);
    $this->installModulesFromClassProperty($container);
    $this->rebuildAll();
  }

  /**
   * Uses the setup file to configure Drupal.
   *
   * @param string $class
   *   The full qualified class name, which should setup Drupal for tests. One common need for
   *   example would be to create the required content types and fields.
   *   The class needs to implement \Drupal\Setup\TestSetupInterface
   *
   * @see \Drupal\Setup\TestSetupInterface
   */
  protected function executeSetupClass($class) {
    if (!class_exists($class)) {
      throw new \InvalidArgumentException("There was a problem loading {$class}");
    }

    if (!is_subclass_of($class, TestSetupInterface::class)) {
      throw new \InvalidArgumentException(sprintf('You need to define a class implementing \Drupal\Setup\TestSetupInterface'));
    }

    /** @var \Drupal\Setup\TestSetupInterface $instance */
    $instance = new $class;
    $instance->setup();
  }

  /**
   * {@inheritdoc}
   */
  protected function installParameters() {
    $connection_info = Database::getConnectionInfo();
    $driver = $connection_info['default']['driver'];
    $connection_info['default']['prefix'] = $connection_info['default']['prefix']['default'];
    unset($connection_info['default']['driver']);
    unset($connection_info['default']['namespace']);
    unset($connection_info['default']['pdo']);
    unset($connection_info['default']['init_commands']);
    $parameters = [
      'interactive' => FALSE,
      'parameters' => [
        'profile' => $this->profile,
        'langcode' => 'en',
      ],
      'forms' => [
        'install_settings_form' => [
          'driver' => $driver,
          $driver => $connection_info['default'],
        ],
        'install_configure_form' => [
          'site_name' => 'Drupal',
          'site_mail' => 'simpletest@example.com',
          'account' => [
            'name' => $this->rootUser->name,
            'mail' => $this->rootUser->getEmail(),
            'pass' => [
              'pass1' => $this->rootUser->pass_raw,
              'pass2' => $this->rootUser->pass_raw,
            ],
          ],
          // form_type_checkboxes_value() requires NULL instead of FALSE values
          // for programmatic form submissions to disable a checkbox.
          'enable_update_status_module' => NULL,
          'enable_update_status_emails' => NULL,
        ],
      ],
    ];
    return $parameters;
  }

}
