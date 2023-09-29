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
        "publiq-gray-dark": "#3b3b3b",
        "publiq-gray-medium": "#efefef",
        "publiq-gray-light": "#fcfcfc",
        "icon-gray": "#5f6368",
        "icon-gray-light": "#F2F2F2",
        "icon-gray-dark": "#E3E3E3",
        "status-red": "#F9CED6",
        "status-red-dark": "#FB2047",
        "status-yellow": "#FBDBAA80",
        "status-yellow-dark": "#896B24",
        "status-green": "#C8FFC7",
        "status-green-medium": "#66CA8E",
        "status-green-dark": "#2A7D4B"
      },
      textColor: ({ theme }) => theme("colors.publiq-gray-dark"),
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
