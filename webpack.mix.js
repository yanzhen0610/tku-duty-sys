const mix = require('laravel-mix');

require('laravel-mix-polyfill');

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

mix.version();
mix.polyfill({
   enabled: true,
   targets: [
      'cover 99.5%',
   ],
});

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.sass('resources/sass/bulma/bulma.sass', 'public/css');

mix.js('resources/js/edit-table.js', 'public/js');
mix.js('resources/js/shifts-arrangements.js', 'public/js');

mix.copy('node_modules/list.js/dist/list.min.js', 'public/js');
