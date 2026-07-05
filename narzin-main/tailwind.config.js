/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Driven by CSS variables in src/theme/tokens.css so the whole theme
        // is swappable from one place. The rgb(var/<alpha-value>) pattern keeps
        // Tailwind opacity modifiers (e.g. bg-narzin-navy/50) working.
        "narzin-navy": "rgb(var(--nz-navy) / <alpha-value>)",
        "narzin-sand": "rgb(var(--nz-sand) / <alpha-value>)",
        "narzin-gold": "rgb(var(--nz-gold) / <alpha-value>)",
        "narzin-bg": "rgb(var(--nz-bg) / <alpha-value>)",
        "nz-surface": "rgb(var(--nz-surface) / <alpha-value>)",
        "nz-ink": "rgb(var(--nz-ink) / <alpha-value>)",
        "nz-muted": "rgb(var(--nz-muted) / <alpha-value>)",
        "nz-border": "rgb(var(--nz-border) / <alpha-value>)",
        "nz-danger": "rgb(var(--nz-danger) / <alpha-value>)",
      },
      borderRadius: {
        nz: "var(--nz-radius)",
        "nz-lg": "var(--nz-radius-lg)",
      },
      boxShadow: {
        nz: "var(--nz-shadow)",
      },
    },
  },
  plugins: [
    require('daisyui'),
  ],
}

