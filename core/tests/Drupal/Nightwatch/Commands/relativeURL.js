
/**
 * Concatenate a BASE_URL variable and a pathname.
 *
 * This provides a custom command, .relativeURL()
 *
 * @param  {string} pathname
 *   The relative path to append to BASE_URL
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function relativeURL(pathname) {
  this
    .url(`${process.env.BASE_URL}${pathname}`);
  return this;
};
