var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    // .autoProvidejQuery()
    // .autoProvideVariables({
    //     "window.jQuery": "jquery",
    //     "window.Bloodhound": require.resolve('bloodhound-js'),
    //     "jQuery.tagsinput": "bootstrap-tagsinput"
    // })
    .enableSassLoader()
    .enableVersioning(false)
    // .createSharedEntry('js/common', ['jquery'])
    .addEntry('main', './vue-app/main.js')
    .enableVueLoader()
    .addLoader({ test: /\.styl$/, loader: 'style-loader!css-loader!stylus-loader' })
    // .addEntry('js/login', './assets/js/login.js')
    // .addEntry('js/admin', './assets/js/admin.js')
    // .addEntry('js/search', './assets/js/search.js')
    // .addStyleEntry('css/app', ['./assets/scss/app.scss'])
    // .addStyleEntry('css/admin', ['./assets/scss/admin.scss'])
;

module.exports = Encore.getWebpackConfig();


//Compile asset once
// yarn run encore dev
//Recompile asset automatic when files chances
// yarn run encore dev --watch
//Compile asset minify for production
// yarn run encore production

//Api-platform generate vue files
//generate-api-platform-client -g vue https://demo.api-platform.com src/ --resource foo

