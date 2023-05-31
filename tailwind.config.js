/* eslint-disable no-undef */
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./resources/ts/**/*.{jsx,tsx}"],
  theme: {
    extend: {},
    colors: {
      blue: "#009fdf",
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
