let mix = require("laravel-mix");
const webpack = require('webpack');
mix
  .js("resources/js/cp.js", "resources/dist/js")
  .setPublicPath("resources/dist");
