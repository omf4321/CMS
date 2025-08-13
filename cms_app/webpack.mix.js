let mix = require('laravel-mix');

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
mix.setPublicPath('../public_html');
mix.js('resources/assets/js/app-v1.1.js', 'js')
   .js('resources/assets/js/user_app-v1.1.js', 'js')
   .js('resources/assets/js/client_app.js', 'js')
   .vue()
   .sass('resources/assets/sass/app.scss', 'css');