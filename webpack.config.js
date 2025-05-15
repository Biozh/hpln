const webpack = require('webpack');
const Encore = require('@symfony/webpack-encore');
const path = Encore.isProduction() ? '/build/' : '/biozh/hpln/public/build/';

const dotenv = require('dotenv');
dotenv.config();

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath(path)
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('hpln', './assets/hpln/main.jsx')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

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

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    .enableSassLoader((options) => {
        options.additionalData = `
            $env-primary-color: ${process.env.PROJECT_PRIMARY_COLOR};
            $env-secondary-color: ${process.env.PROJECT_SECONDARY_COLOR};
        `;
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    .enableReactPreset()
    .autoProvideVariables({
        React: 'react'
    })

    // copy fonts to build folder
    .copyFiles({
        from: './assets/fonts',
        to: 'fonts/[path][name].[ext]',
    })


    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    ;


// Utilisation de ProvidePlugin pour injecter React automatiquement
const config = Encore.getWebpackConfig();
config.plugins.push(
    new webpack.ProvidePlugin({
        React: 'react',
        'react/jsx-runtime': 'react/jsx-runtime'
    })
);


module.exports = config;
