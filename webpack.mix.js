const mix = require('laravel-mix');

// Disable manifest generation
Mix.manifest.refresh = _ => void 0;

mix
    .options({
        terser: {
            extractComments: false,
        },
    })
    .js('resources/js/app.js', 'memory-tracker.js')
    .sass('resources/scss/app.scss', 'memory-tracker.css')
    .setPublicPath('resources/dist');
