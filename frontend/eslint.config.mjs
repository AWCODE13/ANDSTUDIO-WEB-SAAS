import { defineConfig, globalIgnores } from "eslint/config";
import nextVitals from "eslint-config-next/core-web-vitals";
import nextTs from "eslint-config-next/typescript";

const eslintConfig = defineConfig([
  ...nextVitals,
  ...nextTs,
  // Override default ignores of eslint-config-next.
  globalIgnores([
    // Default ignores of eslint-config-next:
    ".next/**",
    "out/**",
    "build/**",
    "next-env.d.ts",
  ]),
  {
    rules: {
      // Data fetching di Client Component (useEffect + fetch) adalah pola
      // standar Next.js App Router untuk halaman yang butuh interaksi klien.
      // Rule ini terlalu ketat untuk pola tersebut.
      "react-hooks/set-state-in-effect": "off",

      // Data dari API Laravel masihdinormalisasi di sisi klien dengan tipe
      // longgar (unknown/Record). Sebaiknya diperketat bertahap.
      "@typescript-eslint/no-explicit-any": "warn",
    },
  },
]);

export default eslintConfig;
