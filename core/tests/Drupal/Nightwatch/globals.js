const chromedriver = require('chromedriver');
const env = require('./env');

module.exports = {
  before: (done) => {
    if (env.NODE_ENV !== 'testbot') {
      chromedriver.start();
    }
    done();
  },
  after: (done) => {
    if (env.NODE_ENV !== 'testbot') {
      chromedriver.stop();
    }
    done();
  },
};
