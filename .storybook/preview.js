/** @type { import('@storybook/web-components').Preview } */
import './../assets/back.js';
import './../assets/front.js';
const preview = {
  parameters: {
    actions: {
      argTypesRegex: "^on[A-Z].*"
    },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
    options: {
      storySort: {
        method: 'alphabetical',
        order: [ 'Introduction', 'Atoms', 'Molecules', 'Organisms', 'Templates', 'Pages' ],
        locales: '',
      },
    },
  },
};

export default preview;
