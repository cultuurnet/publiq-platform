import js from "@eslint/js";
import tsPlugin from "@typescript-eslint/eslint-plugin";
import tsParser from "@typescript-eslint/parser";
import reactPlugin from "eslint-plugin-react";
import reactHooksPlugin from "eslint-plugin-react-hooks";
import globals from "globals";

export default [
    js.configs.recommended,
    {
        files: ["resources/ts/**/*.{js,jsx,ts,tsx}"],
        plugins: {
            "@typescript-eslint": tsPlugin,
            react: reactPlugin,
            "react-hooks": reactHooksPlugin,
        },
        languageOptions: {
            parser: tsParser,
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                ...globals.browser,
                ...globals.es2021,
            },
        },
        settings: {
            react: {
                version: "detect",
            },
        },
        rules: {
            ...tsPlugin.configs.recommended.rules,
            ...reactPlugin.configs.recommended.rules,
            ...reactHooksPlugin.configs.recommended.rules,
            "no-redeclare": "off",
            "@typescript-eslint/ban-ts-comment": "off",
            "@typescript-eslint/consistent-type-imports": "error",
            "react/prop-types": "off",
            "react-hooks/exhaustive-deps": "error",
        },
        linterOptions: {
            reportUnusedDisableDirectives: true,
        },
    },
];