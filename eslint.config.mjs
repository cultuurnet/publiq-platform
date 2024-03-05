import javascriptPlugin from "@eslint/js";
import reactPlugin from "eslint-plugin-react";
import reactHooksPlugin from "eslint-plugin-react-hooks";
import typescriptPlugin from "@typescript-eslint/eslint-plugin";
import typescriptParser from "@typescript-eslint/parser";

export default [
  {
    plugins: {
      react: reactPlugin,
      "react-hooks": reactHooksPlugin,
      "@typescript-eslint": typescriptPlugin,
    },
    rules: {
      ...javascriptPlugin.configs.recommended.rules,

      ...reactPlugin.configs.recommended.rules,
      "react/prop-types": "off",

      ...reactHooksPlugin.configs.recommended.rules,
      "react-hooks/exhaustive-deps": "error",

      ...typescriptPlugin.configs.recommended.rules,
    },
    languageOptions: {
      globals: {
        browser: true,
        es2021: true,
      },
      parser: typescriptParser,
      parserOptions: {
        ecmaVersion: "latest",
        sourceType: "module",
      },
    },
    settings: {
      react: {
        version: "detect",
      },
    },
  },
];
