const { defineConfig } = require("@vue/cli-service");
// vue.config.js

module.exports = defineConfig({
  devServer: {
    host: "0.0.0.0",
    port: 8001,
    allowedHosts: "all",
  },
  transpileDependencies: true,
  publicPath: "/helka/",
  outputDir: "dist",
});
