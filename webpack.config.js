const Encore = require('@symfony/webpack-encore');
const NodePolyfillPlugin = require('node-polyfill-webpack-plugin');
const path = require('path');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', path.resolve(__dirname, 'js/app.js'))
    .enableVueLoader() // Включаем поддержку Vue
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .addPlugin(new NodePolyfillPlugin())
;

module.exports = Encore.getWebpackConfig();
