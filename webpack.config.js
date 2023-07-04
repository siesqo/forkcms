var webpack = require('webpack')

module.exports = {
  output: {
    filename: 'bundle.js'
  },
  resolve: {
    modules: ['node_modules']
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      Popper: ['popper.js', 'default']
    })
  ],
  module: {
    rules: [
      {
        test: /\.js$|jsx/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        options: {
          cacheDirectory: true,
          presets: ['@babel/preset-env']
        }
      },
      {
        // separate loader for Bootstrap because it needs to be compiled
        test: /bootstrap\.js$/,
        loader: 'babel-loader',
        options: {
          cacheDirectory: true,
          presets: ['@babel/preset-env']
        }
      }
    ]
  }
}
