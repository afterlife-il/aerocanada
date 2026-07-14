import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "export",
  basePath: "/SaaS_Aviation",
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
