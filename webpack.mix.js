const { mix } = require('laravel-mix');
const webpack = require('webpack');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

if (mix.config.inProduction) mix.copy('resources/assets/images', 'public/images');

mix
    .js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version()
    .options({
        plugins: [
          new webpack.DefinePlugin({
            'debug': !mix.config.inProduction
          })
        ],
        uglify: {
          compress: {
            drop_console: false
          }
        }
    })
;
