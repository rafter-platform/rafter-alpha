const defaultTheme = require("tailwindcss/defaultTheme");
const defaultConfig = require("tailwindcss/defaultConfig");

module.exports = {
  purge: {
    content: ["./resources/views/**/*.blade.php"]
  },
  theme: {
    extend: {
      fontFamily: {
        sans: ["Inter var", ...defaultTheme.fontFamily.sans]
      }
    }
  },
  variants: {
    opacity: [...defaultConfig.variants.opacity, "disabled"]
  },
  plugins: [require("@tailwindcss/ui")]
};
