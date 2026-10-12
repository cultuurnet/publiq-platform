import js from "@eslint/js";
import tsPlugin from "@typescript-eslint/eslint-plugin";
import tsParser from "@typescript-eslint/parser";
import eslintReact from "@eslint-react/eslint-plugin";
import reactHooksPlugin from "eslint-plugin-react-hooks";
import globals from "globals";

export default [
    js.configs.recommended,
    {
        files: ["resources/ts/**/*.{js,jsx,ts,tsx}"],
        plugins: {
            "@typescript-eslint": tsPlugin,
            ...eslintReact.configs["recommended-typescript"].plugins,
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
        rules: {
            ...tsPlugin.configs.recommended.rules,
            ...eslintReact.configs["recommended-typescript"].rules,
            ...reactHooksPlugin.configs.recommended.rules,
            "no-redeclare": "off",
            "@typescript-eslint/ban-ts-comment": "off",
            "@typescript-eslint/consistent-type-imports": "error",
            // eslint-plugin-react-hooks owns these, so they are not reported twice
            "@eslint-react/error-boundaries": "off",
            "@eslint-react/exhaustive-deps": "off",
            "@eslint-react/purity": "off",
            "@eslint-react/rules-of-hooks": "off",
            "@eslint-react/set-state-in-effect": "off",
            "@eslint-react/set-state-in-render": "off",
            "@eslint-react/static-components": "off",
            "@eslint-react/unsupported-syntax": "off",
            "@eslint-react/use-memo": "off",
            "react-hooks/exhaustive-deps": "error",
        },
        linterOptions: {
            reportUnusedDisableDirectives: true,
        },
    },
];