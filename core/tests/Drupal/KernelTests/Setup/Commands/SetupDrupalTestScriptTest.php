<?php

namespace Drupal\KernelTests\Setup\Commands;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Setup\Commands\TestInstallationSetupApplication;
use Drupal\Setup\SetupDrupalTestScript;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Tests setup-drupal-test.php.
 *
 * @group Setup
 *
 * @todo Move this to the \Drupal\KernelTests\Setup\Commands\ namespace after
 *   https://www.drupal.org/project/drupal/issues/2878269
 */
class SetupDrupalTestScriptTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Disable the usual kernel test setup process, as we want to run a custom
    // command to install Drupal.
    $this->root = static::getDrupalRoot();
  }

  /**
   * @coversNothing
   */
  public function testInstallWithNonExistingClass() {
    $autoloader = $this->root . '/autoload.php';
    $app = new TestInstallationSetupApplication(require $autoloader);
    $app->setAutoExit(FALSE);

    $app_tester = new ApplicationTester($app);
    $app_tester->run(
      [
        'command' => 'setup-drupal-test',
        '--setup_class' => 'this-class-does-not-exist',
      ],
      [
        'interactive' => FALSE,
      ]
    );

    $this->assertContains('There was a problem loading this-class-does-not-exist', $app_tester->getDisplay());
  }

  /**
   * @coversNothing
   */
  public function testInstallWithNonSetupClass() {
    $autoloader = $this->root . '/autoload.php';
    $app = new TestInstallationSetupApplication(require $autoloader);
    $app->setAutoExit(FALSE);

    $app_tester = new ApplicationTester($app);
    $app_tester->run(
      [
        'command' => 'setup-drupal-test',
        '--setup_class' => static::class,
      ],
      [
        'interactive' => FALSE,
      ]
    );

    $this->assertContains('You need to define a class implementing \Drupal\Setup\TestSetupInterface ', $app_tester->getDisplay());
  }

  /**
   * @coversNothing
   */
  public function testInstallScript() {
    $autoloader = $this->root . '/autoload.php';
    $app = new TestInstallationSetupApplication(require $autoloader);
    $app->setAutoExit(FALSE);

    $app_tester = new ApplicationTester($app);
    $app_tester->run(
      [
        'command' => 'setup-drupal-test',
        '--setup_class' => SetupDrupalTestScript::class,
      ],
      [
        'interactive' => FALSE,
      ]
    );

    $this->assertNotRegExp('/PHPUnit_Framework_Error_Warning/', $app_tester->getDisplay());
    $this->assertNotRegExp('/AlreadyInstalledException/', $app_tester->getDisplay());
    $this->assertRegExp('/simpletest/', $app_tester->getDisplay());
    $this->assertEqual(0, $app_tester->getStatusCode());

    list($test_db_prefix) = explode(':', $app_tester->getDisplay(), 2);

    $http_client = new Client();
    $request = (new Request('GET', getenv('SIMPLETEST_BASE_URL') . '/test-page'))
      ->withHeader('User-Agent', trim($app_tester->getDisplay()));

    $response = $http_client->send($request);
    // Ensure the test_page_test module got installed.
    $this->assertContains('Test page | Drupal', (string) $response->getBody());

    // Now test the tear down process as well.
    $this->assertHasTables();
    $app_tester->run(
      [
        'command' => 'teardown-drupal-test',
        'db_prefix' => $test_db_prefix,
      ],
      [
        'interactive' => FALSE,
      ]
    );
    $this->assertHasNoTables();
  }

  public function assertHasTables() {
    $tables = Database::getConnection()->schema()->findTables('%');
    $this->assertNotEmpty($tables);
  }

  public function assertHasNoTables() {
    $tables = Database::getConnection()->schema()->findTables('%');
    $this->assertEmpty($tables);
  }

}
