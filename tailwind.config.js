/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./resources/ts/**/*.{jsx,tsx}"],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms')
  ],
}
