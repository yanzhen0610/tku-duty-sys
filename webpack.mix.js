const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.sass('resources/sass/bulma/bulma.sass', 'public/css').version();
mix.sass('resources/sass/materialize/sass/checkboxes.scss', 'public/css/materialize').version();

mix.js('resources/js/edit-table.js', 'public/js').version();
