/* eslint-env node */
const { resolve } = require('path')

module.exports = {
  root: true,

  parserOptions: {
    ecmaVersion: 'latest',
  },

  env: {
    browser: true,
    es2021: true,
    node: true,
    'vue/setup-compiler-macros': true,
  },

  extends: [
    'plugin:vue/vue3-essential',
  ],

  plugins: ['vue'],

  globals: {
    ga: 'readonly',
    cordova: 'readonly',
    __statics: 'readonly',
    __QUASAR_SSR__: 'readonly',
    __QUASAR_SSR_SERVER__: 'readonly',
    __QUASAR_SSR_CLIENT__: 'readonly',
    __QUASAR_SSR_PWA__: 'readonly',
    process: 'readonly',
    Capacitor: 'readonly',
    chrome: 'readonly',
  },

  rules: {
    'prefer-promise-reject-errors': 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'vue/multi-word-component-names': 'off',
  },
}
