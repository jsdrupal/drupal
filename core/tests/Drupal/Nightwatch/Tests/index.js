module.exports = {
  'Test page': (browser, done) => {
    browser
      .installDrupal('\\\\Drupal\\\\Setup\\\\SetupDrupalTestScript')
      .relativeURL('/test-page')
      .waitForElementVisible('body', 1000)
      .assert.containsText('body', 'Test page text')
      .end();
  },
};
