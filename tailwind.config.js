/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        indomaret: {
          blue: '#00509d',
          'blue-dark': '#003870',
          'blue-light': '#e8f0fe',
          red: '#d90429',
          'red-dark': '#b00320',
          'red-light': '#fde8eb',
          yellow: '#ffb703',
          'yellow-dark': '#e09f00',
          'yellow-light': '#fff8e6',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
        mono: ['JetBrains Mono', 'Fira Code', 'monospace']
      }
    },
  },
  plugins: [],
}
