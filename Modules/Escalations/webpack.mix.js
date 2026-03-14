const dotenvExpand = require('dotenv-expand');
dotenvExpand(require('dotenv').config({ path: '../../.env'/*, debug: true*/}));

const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'js/escalations.js')
    .js(__dirname + '/Resources/assets/js/notifications.js', 'js/escalations-notifications.js')
    .sass( __dirname + '/Resources/assets/sass/app.scss', 'css/escalations.css');

if (mix.inProduction()) {
    mix.version();
}
