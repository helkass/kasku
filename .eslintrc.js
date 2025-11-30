module.exports = {
  root: true,
  parser: "vue-eslint-parser",
  parserOptions: {
    parser: "@babel/eslint-parser",
    requireConfigFile: false,
    ecmaVersion: 2020,
    sourceType: "module",
  },
  extends: ["eslint:recommended", "plugin:vue/vue3-recommended"],
  rules: {
    "vue/max-attributes-per-line": "off",
    "vue/singleline-html-element-content-newline": "off",
    "vue/multiline-html-element-content-newline": "off",
    "vue/attributes-order": "off",
    "no-unreachable": "off",
    "vue/html-self-closing": "off",
    "vue/html-closing-bracket-newline": "off",
    "vue/html-indent": "off",
  },
};
