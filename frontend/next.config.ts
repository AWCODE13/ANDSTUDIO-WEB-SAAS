import type { NextConfig } from "next";

const isDev = process.env.NODE_ENV === "development";

const nextConfig: NextConfig = {
  /* config options here */
  experimental: {
    agentUpgrade: "security",
  },
  cacheComponents: true,
  partialPrefetching: true,
  // Temuan #16, #19, #20: allowedDevOrigins hanya di development
  allowedDevOrigins: isDev
    ? ["100.81.255.49", "localhost", "127.0.0.1", "100.83.250.52"]
    : [],
  images: {
    // Host lokal API Laravel (foto paket disimpan di storage/public)
    remotePatterns: [
      { protocol: "http" as const, hostname: "localhost", port: "8000", pathname: "/**" },
      { protocol: "http" as const, hostname: "127.0.0.1", port: "8000", pathname: "/**" },
      ...(isDev
        ? [{ protocol: "http" as const, hostname: "100.81.255.49", port: "8000", pathname: "/**" }]
        : []),
      // Tunneling ngrok (domain acak tiap kali tunnel dibuat)
      { protocol: "https" as const, hostname: "*.ngrok-free.app", pathname: "/**" },
      { protocol: "https" as const, hostname: "*.ngrok.io", pathname: "/**" },
      { protocol: "https" as const, hostname: "*.ngrok.app", pathname: "/**" },
    ],
    // Domain lokal biasanya tanpa optimasi server Next, biarkan file asli dimuat
    unoptimized: true,
    // Temuan #19: dangerouslyAllowLocalIP hanya di development
    dangerouslyAllowLocalIP: isDev,
    qualities: [75],
  },
  // Temuan #20: rewrites ke IP internal hanya di development
  rewrites: async () => {
    if (!isDev) return { beforeFiles: [] };
    return {
      beforeFiles: [
        // Admin routes → Laravel
        { source: "/app/:path*", destination: "http://192.168.18.201:8000/app/:path*" },
        { source: "/login", destination: "http://192.168.18.201:8000/login" },
        { source: "/register", destination: "http://192.168.18.201:8000/register" },
        { source: "/forgot-password", destination: "http://192.168.18.201:8000/forgot-password" },
        { source: "/logout", destination: "http://192.168.18.201:8000/logout" },
        { source: "/notifications/:path*", destination: "http://192.168.18.201:8000/notifications/:path*" },
        // API → Laravel
        { source: "/api/:path*", destination: "http://100.81.255.49:8000/api/:path*" },
        // Static files → Laravel
        { source: "/storage/:path*", destination: "http://192.168.18.201:8000/storage/:path*" },
        { source: "/build/:path*", destination: "http://192.168.18.201:8000/build/:path*" },
      ],
    };
  },
  turbopack: {
    root: __dirname,
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },
  async headers() {
    const apiOrigin = process.env.NEXT_PUBLIC_API_URL
      ? (() => {
          try {
            return new URL(process.env.NEXT_PUBLIC_API_URL).origin;
          } catch {
            return "http://localhost:8000";
          }
        })()
      : "http://localhost:8000";

    return [
      {
        source: "/:path*",
        headers: [
          {
            key: "Content-Security-Policy",
            value:
              "default-src 'self'; " +
              "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.sandbox.midtrans.com https://api.midtrans.com; " +
              "style-src 'self' 'unsafe-inline'; " +
              // Temuan #19: IP internal hanya di CSP dev; production pakai apiOrigin dari env
              `img-src 'self' data: https: blob:${isDev ? " http:" : ""} ${apiOrigin}; ` +
              `connect-src 'self' https://app.sandbox.midtrans.com https://api.midtrans.com${isDev ? " http://localhost:* http://127.0.0.1:* http://100.81.255.49:* ws: wss:" : ""} ${apiOrigin}; ` +
              "font-src 'self' data: https://fonts.gstatic.com; " +
              "frame-src 'self' https://app.sandbox.midtrans.com; " +
              "base-uri 'self'; " +
              "form-action 'self';",
          },
          {
            key: "X-Frame-Options",
            value: "SAMEORIGIN",
          },
          {
            key: "X-Content-Type-Options",
            value: "nosniff",
          },
          {
            key: "Referrer-Policy",
            value: "strict-origin-when-cross-origin",
          },
          {
            key: "Strict-Transport-Security",
            value: "max-age=31536000; includeSubDomains",
          },
          {
            key: "Permissions-Policy",
            value: "camera=(), microphone=(), geolocation=()",
          },
        ],
      },
    ];
  },
};

export default nextConfig;
