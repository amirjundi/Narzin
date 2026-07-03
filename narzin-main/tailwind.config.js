/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        "narzin-navy": "#141923",
        "narzin-sand": "#C5A880",
        "narzin-gold": "#D4AF37",
        "narzin-bg": "#F7F9FB",
      },
    },
  },
  plugins: [
    require('daisyui'),
  ],
}

