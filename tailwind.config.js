/* eslint-disable no-undef */
/** @type {import('tailwindcss').Config} */

const zIndexClasses = new Array(100).fill(0).map((_, i) => `z-[${i}]`);

module.exports = {
  content: ["./resources/ts/**/*.{jsx,tsx}"],
  safelist: [...zIndexClasses],
  theme: {
    extend: {
      animation: {
        pulse: "pulse 0.4s",
      },
      dropShadow: {
        card: "rgba(0, 0, 0, 0.07) 0px 4px 10px",
        triangle: [
          "0 4px 1px rgba(0, 0, 0, 0.07)",
          "0 2px 1px rgba(0, 0, 0, 0.06)",
        ],
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
        "publiq-gray": {
          50: "hsl(0, 0%, 98%)",
          75: "hsl(0, 0%, 96%)",
          100: "hsl(0, 0%, 94%)",
          200: "hsl(0, 0%, 86%)",
          300: "hsl(0, 0%, 74%)",
          400: "hsl(0, 0%, 60%)",
          500: "hsl(0, 0%, 49%)",
          600: "hsl(0, 0%, 40%)",
          700: "hsl(0, 0%, 32%)",
          800: "hsl(0, 0%, 27%)",
          900: "hsl(0, 0%, 24%)",
          950: "hsl(0, 0%, 16%)",
        },
        "publiq-orange": "#EC865F",
        "uitid-widget": "#F8F8F8",
        "icon-gray": "#5f6368",
        "icon-gray-light": "#F2F2F2",
        "icon-gray-dark": "#E3E3E3",
        "status-red": "#F9CED6",
        "status-red-dark": "rgb(221, 82, 66)",
        "status-green": "#C8FFC7",
        "status-green-medium": "#66CA8E",
        "status-green-dark": "#2A7D4B",
        "alert-success": "#F3FCF7",
        "alert-success-dark": "#6BCD69",
        "alert-info": "#D1DEFA",
        "alert-info-dark": "#3868EC",
        "alert-error": "#FAE5E3",
        "alert-error-dark": "#DD5242",
        "alert-warning": "#FCF0CB",
        "alert-warning-dark": "#E69336",
      },
      textColor: ({ theme }) => theme("colors.publiq-gray.900"),
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
