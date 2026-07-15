import type { NextConfig } from "next";

const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "/SaaS_Aviation";

const nextConfig: NextConfig = {
  output: "export",
  ...(basePath ? { basePath } : {}),
  trailingSlash: true,
  images: {
    unoptimized: true
  },
  typedRoutes: false,
  eslint: {
    dirs: ["src"]
  },
  webpack(config) {
    config.resolve.extensionAlias = {
      ...config.resolve.extensionAlias,
      ".js": [".ts", ".tsx", ".js"]
    };
    return config;
  }
};

export default nextConfig;
