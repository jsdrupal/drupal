const env = require('./tests/Drupal/Nightwatch/env');

const args = ['--disable-gpu', ...env.CHROME_ARGS];
if (env.HEADLESS_CHROME) {
  args.push('--headless');
}

module.exports = {
  src_folders: ['tests/Drupal/Nightwatch/Tests'],
  output_folder: env.NIGHTWATCH_OUTPUT,
  custom_commands_path: ['tests/Drupal/Nightwatch/Commands'],
  custom_assertions_path: '',
  page_objects_path: '',
  globals_path: 'tests/Drupal/Nightwatch/globals.js',
  selenium: {
    start_process: false,
  },
  test_settings: {
    default: {
      selenium_port: 9515,
      selenium_host: env.WEBDRIVER_HOSTNAME,
      default_path_prefix: '',
      desiredCapabilities: {
        browserName: 'chrome',
        acceptSslCerts: true,
        chromeOptions: {
          args,
        },
      },
      screenshots: {
        enabled: true,
        on_failure: true,
        on_error: true,
        path: `${env.NIGHTWATCH_OUTPUT}/screenshots`,
      },
    },
  },
};
