/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {},
  },
  plugins: [],
  // Assurons-nous que les utilitaires personnalisés sont bien pris en compte
  utility: {
    // Cette configuration permet de s'assurer que les utilitaires personnalisés
    // dans glow.css sont correctement traités par Tailwind
    extraTransformations: true,
  },
};
