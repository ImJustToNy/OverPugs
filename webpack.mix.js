const { mix } = require('laravel-mix');
const SentryPlugin = require('webpack-sentry-plugin');

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

if (process.env.NODE_ENV == 'production')
    mix
        .copy('resources/assets/images', 'public/images')
        .webpackConfig({
            plugins: [
                new SentryPlugin({
                    organisation: 'OverPugs',
                    project: 'OverPugs',
                    apiKey: process.env.SENTRY_API_KEY,
                  
                    release: function () {
                        return process.env.GIT_SHA
                    }
                })
            ]
        })
;

mix
    .js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    // .sourceMaps()
    .version()
;
