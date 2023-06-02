/* eslint-disable no-undef */
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./resources/ts/**/*.{jsx,tsx}"],
  theme: {
    extend: {
      animation: {
        pulse: "pulse 0.4s",
      },
      keyframes: {
        pulse: {
          "0%": {
            transform: "scale(1)",
            opacity: "0.7",
            borderRadius: "50%",
          },

          "100%": {
            transform: "scale(2)",
            opacity: "0.3",
            borderRadius: "0",
          },
        },
      },
      colors: {
        "publiq-blue": "#009fdf",
        "publiq-blue-dark": "#0076a5",
        "publiq-blue-light": "#1ebeff",
        "publiq-gray": "#3b3b3b",
        "publiq-gray-light": "#f5f5f5",
      },
      textColor: ({ theme }) => theme("colors.publiq-gray"),
      fontFamily: {
        sans: [
          "ui-sans-serif",
          "system-ui",
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "Roboto",
          "Helvetica Neue",
          "Arial",
          "Noto Sans",
          "sans-serif",
          "Apple Color Emoji",
          "Segoe UI Emoji",
          "Segoe UI Symbol",
          "Noto Color Emoji",
        ],
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
