/* eslint-disable no-undef */
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./resources/ts/**/*.{jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        blue: "#009fdf",
        textColor: "#3b3b3b",
      },
      textColor: ({ theme }) => theme("colors.textColor"),
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
