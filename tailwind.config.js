import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: ['selector', '[data-theme="studio-dark"], .dark'],
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [daisyui],
  daisyui: {
    themes: [
      {
        "studio-light": {
          "primary": "#059669",
          "primary-content": "#ffffff",
          "secondary": "#0d9488",
          "accent": "#06b6d4",
          "neutral": "#f8fafc",
          "base-100": "#ffffff",
          "base-200": "#f1f5f9",
          "base-300": "#e2e8f0",
          "base-content": "#1e293b",
          "info": "#0284c7",
          "success": "#10b981",
          "warning": "#f59e0b",
          "error": "#ef4444",
        },
      },
      {
        "studio-dark": {
          "primary": "#10b981",
          "primary-content": "#ffffff",
          "secondary": "#14b8a6",
          "accent": "#06b6d4",
          "neutral": "#0a0908",
          "base-100": "#121417",
          "base-200": "#1a1d24",
          "base-300": "#2a303c",
          "base-content": "#f1f5f9",
          "info": "#0284c7",
          "success": "#10b981",
          "warning": "#f59e0b",
          "error": "#ef4444",
        },
      },
    ],
    logs: false,
  },
}
