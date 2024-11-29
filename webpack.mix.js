let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss')
let path = require('path')
let postcssImport = require('postcss-import')
let postcssRtlcss = require('postcss-rtlcss')

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
mix
  .js('templates/js/app.js', 'public/js')
  .vue({ version: 3 })
  .sourceMaps()
  .extract()
  .setPublicPath('public')
  .postCss('templates/css/app.css', 'public/css', [
    postcssImport(),
    tailwindcss('tailwind.config.js'),
    postcssRtlcss(),
  ])
  .copy('templates/fonts/', 'public/fonts')
  .alias({
    '@': path.join(__dirname, 'templates/js/'),
    '@fields': path.join(__dirname, 'app/fields/'),
    '@packages': path.join(__dirname, 'app/packages/')
  })
  .webpackConfig({ output: { uniqueName: 'laravel/nova' } })
  .options({
    vue: {
      exposeFilename: true,
      compilerOptions: {
        isCustomElement: tag => tag.startsWith('trix-'),
      },
    },
    processCssUrls: false,
  })
  .version()