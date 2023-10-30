/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["www/*.php", "www/**/*.php"],
  theme: {
    extend: {
      colors: {
        primary: {
          400: "#25262f",
          500: "#0C1374",
          600: "#070b4b",
        },
        success: {
          500: "#61CF93",
          600: "#4fB57D",
        },
        error: {
          500: "#E04b42",
          600: "#D34339",
        },
        yellow: {
          400: "#F1BE41",
        },
        gray: {
          200: "#FBFBFB",
          300: "#F2F2F2",
          DEFAULT: "#FBFBFB",
          600: "#DBDCE2",
        },
      },
    },
  },
  plugins: [],
};
