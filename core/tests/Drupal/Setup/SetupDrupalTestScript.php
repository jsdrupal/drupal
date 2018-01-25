<?php

namespace Drupal\Setup;

/**
 * Setup file used by \Drupal\KernelTests\Setup\Commands\SetupDrupalTestScriptTest
 */
class SetupDrupalTestScript implements TestSetupInterface {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    \Drupal::service('module_installer')->install(['test_page_test']);
  }

}
