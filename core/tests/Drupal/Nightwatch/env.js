const fs = require('fs');

const env = {};

if (!fs.existsSync('nightwatch.settings.json')) {
  throw new Error('You need to setup the nightwatch.settings.json.default to nightwatch.settings.json')
}

const settings = require('../../../nightwatch.settings.json');
const availableSettings = [
  ['SIMPLETEST_BASE_URL', 'BASE_URL'],
  ['DB_URL', 'SIMPLETEST_DB'],
  'NIGHTWATCH_OUTPUT',
  'WEBDRIVER_HOSTNAME',
  'HEADLESS_CHROME',
  'CHROME_ARGS'
];

availableSettings.forEach((setting) => {
  // Some settings have aliases. For those we first lookup all env variables and then all settings.
  if (!Array.isArray(setting)) {
    setting = [setting];
  }
  let envSetting = setting.reduce((agg, settingName) => {
    if (agg) {
      return agg;
    }
    if (process.env[settingName]) {
      return process.env[settingName];
    }
  }, '');
  if (!envSetting) {
    envSetting = setting.reduce((agg, settingName) => {
      if (agg) {
        return agg;
      }
      if (settings[settingName]) {
        return settings[settingName];
      }
    });
  }

  if (envSetting) {
    setting.forEach((settingName) => {
      env[setting] = process.env[settingName] = envSetting;
    });
  // Note: The simpletest DB is optional, when there is a local drupal
  // installation.
  } else if (setting.findIndex('DB_URL') === -1) {
    throw new Error(`Missing ${settings.join(', ')} configuration item or environment variable.`);
  }
});

module.exports = env;

