const mix = require("laravel-mix");
const CompressionWebpackPlugin = require("compression-webpack-plugin");


mix
    .options({
        processCssUrls: false,
    })
    .js('src/app.js', 'js')
    .js('src/admin/scripts/tournaments.js', 'js')
    .sass('src/app.scss', 'css')
    .sass('src/editor.scss', 'css')
    .sass('src/admin/style/admin.scss', 'css')
    .setPublicPath('dist')
    .sourceMaps(true, 'source-map')

mix.webpackConfig({
    plugins: [
        new CompressionWebpackPlugin({
            algorithm: "gzip",
            test: /\.js$|\.css$|\.html$|\.svg$/,
            threshold: 10240,
            minRatio: 0.8
        })
    ]
});

// rules
mix.webpackConfig({
    module: {
        rules: [
            { parser: { amd: false } }
        ]
    }
});