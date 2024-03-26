const path = require('path');
const { sep } = require('path');
const srcDir = '../assets';
const tplDir = '../apps/templates';
import { exit } from 'process';
/** @type { import('@storybook/web-components-webpack5').StorybookConfig } */
import webpack, { entry } from '../webpack.config.js';
const config = {
  stories: [
    '../stories/**/*.mdx',
    '../stories/**/*.stories.@(js|jsx|ts|tsx)'
  ],
  addons: [
    '@storybook/addon-a11y',
    '@storybook/addon-backgrounds',
    '@storybook/addon-viewport',
    '@storybook/addon-links',
    '@storybook/addon-essentials'
  ],
  framework: {
    name: '@storybook/web-components-webpack5',
    options: {},
  },
  docs: {
    autodocs: 'tag',
  },
  staticDirs: [
    {
      from: `${srcDir}/images`,
      to: '/assets/images'
    }
  ],
  async webpackFinal(config) {
    config.plugins = [
      ...config.plugins,
      ...webpack.plugins
    ];

    // Disable plugins CleanWebpackPlugin AssetOutputDisplayPlugin AssetsWebpackPlugin
    config.plugins = config.plugins.filter(
      (plugin) => (
        plugin.constructor.name !== 'CleanWebpackPlugin' &&
        plugin.constructor.name !== 'AssetOutputDisplayPlugin' &&
        plugin.constructor.name !== 'AssetsWebpackPlugin'
      )
    );
    config.module.rules = [
      ...config.module.rules,
      ...webpack.module.rules
    ];
    config.module.rules.push(
      {
        test: /\.twig$/,
        use:  {
          loader: 'twigjs-loader',
          options: {
            renderTemplate(twigData, dependencies, isHot) {
              return `
                ${dependencies}
                const Twig = require('twig');
                Twig.extendFilter('t', (value) => {
                  return value;
                });
                const twig = Twig.twig;
                const tpl = twig(${JSON.stringify(twigData)});
                module.exports = function(context) { return tpl.render(context); };
              `;
            },
          },
        },
      }
    );
    config.resolve.alias = {
      ...config.resolve.alias,
      ...webpack.resolve.alias,
    };

    return config;
  }
};
export default config;
