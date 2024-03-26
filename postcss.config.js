const discardComments = require('postcss-discard-comments');
const autoprefixer = require('autoprefixer');
const atrulepacker = require('at-rule-packer');

module.exports = {
  plugins: [
    autoprefixer({}),
    // atrulepacker(),
    discardComments({
      removeAll: true
    })
  ]
}
