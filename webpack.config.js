const path = require('path');
var Encore = require('@symfony/webpack-encore');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const { CKEditorTranslationsPlugin } = require( '@ckeditor/ckeditor5-dev-translations' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );
// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('apps/public/assets/')
  // public path used by the web server to access the output path
  .setPublicPath('/assets')
  // only needed for CDN's or sub-directory deploy
  .setManifestKeyPrefix('assets/')
  .copyFiles([
    {
      from: './node_modules/tarteaucitronjs',
      to: 'tarteaucitron/[path][name].[ext]',
      pattern: /\.(js)$/,
      includeSubdirectories: false
    },
    {
      from: './node_modules/tarteaucitronjs/css',
      to: 'tarteaucitron/css/[path][name].[ext]'
    },
    {
      from: './node_modules/tarteaucitronjs/lang',
      to: 'tarteaucitron/lang/[path][name].[ext]'
    }
  ])
  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('front', ['./assets/front.js', './assets/front.scss'])
  .addEntry('back', ['./assets/back.js', './assets/back.scss'])

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  // .enableStimulusBridge('./assets/controllers.json')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  // .disableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // enables @babel/preset-env polyfills
  // .configureBabelPresetEnv((config) => {
  //   config.useBuiltIns = 'usage';
  //   config.corejs = 3;
  // })

  // enables Sass/SCSS support
  .enableSassLoader()

  // uncomment if you use TypeScript
  //.enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  .enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  // .autoProvidejQuery()
  .configureDevServerOptions(options => {
    options.allowedHosts = 'all';
  })
  .addPlugin(new CleanWebpackPlugin())
  .addPlugin(
    new CKEditorTranslationsPlugin({
      // The main language that will be built into the main bundle.
      language: 'fr',

      // Additional languages that will be emitted to the `outputDirectory`.
      // This option can be set to an array of language codes or `'all'` to build all found languages.
      // The bundle is optimized for one language when this option is omitted.
      additionalLanguages: 'all',

      // For more advanced options see https://github.com/ckeditor/ckeditor5-dev/tree/master/packages/ckeditor5-dev-translations.
    })
  )
  .addRule(
    {
      test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
      loader: 'raw-loader'
    }
  )
  .configureLoaderRule( 'images', loader => {
    loader.exclude = /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/;
  })
  .addLoader({
    test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
    loader: 'postcss-loader',
    options: {
        postcssOptions: styles.getPostCssConfig( {
            themeImporter: {
                themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
            },
            minify: true
        } )
    }
  } )

  .enablePostCssLoader(
    (options) => {
      options.postcssOptions = {
        path: path.resolve(__dirname, 'postcss.config.js')
      };
    }
  )
  .addAliases({
    '@nm': path.resolve(__dirname, 'node_modules'),
    '@': path.resolve(__dirname, 'assets'),
    '@components': path.resolve(__dirname, 'components'),
    '@back': path.resolve(__dirname, 'assets/back'),
    '@front': path.resolve(__dirname, 'assets/front'),
    '@global': path.resolve(__dirname, 'assets/global'),
    '@class': path.resolve(__dirname, 'assets/class'),
    '@fonts': path.resolve(__dirname, 'assets/fonts'),
    '@images': path.resolve(__dirname, 'assets/images')
  })
;

module.exports = Encore.getWebpackConfig();