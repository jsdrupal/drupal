const exec = require('child_process').exec;

/**
 * @param browser
 * @param cookieValue
 * @returns {*}
 */
const setupCookie = function (browser, cookieValue, done) {
  const matches = process.env.BASE_URL.match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
  const domain = matches[1];
  const path = matches[2];

  return browser
    // See https://bugs.chromium.org/p/chromedriver/issues/detail?id=728#c10
    .url(process.env.BASE_URL)
    .setCookie({
      name: 'SIMPLETEST_USER_AGENT',
      // Colons needs to be URL encoded to be valid.
      value: encodeURIComponent(cookieValue),
      path: path,
      domain: domain,
    }, done);
};

exports.command = function installDrupal(profile = 'testing', setupFile = '', done) {
  const self = this;

  exec(`php ./scripts/setup-drupal-test.php ${profile} --setup_file ${setupFile}`, (err, simpleTestCookie) => {
    if (err) {
      console.error(err);
      return done(err);
    }

    setupCookie(self, simpleTestCookie, done);
  });


  return this;
};
