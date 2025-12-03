const mix = require('laravel-mix');

var LiveReloadWebpackPlugin = require('@kooneko/livereload-webpack-plugin');

mix.webpackConfig({
    plugins: [new LiveReloadWebpackPlugin()]
});

mix.sass('admin/ui/scss/app.scss', 'admin/ui/css/app.css');