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
  }
};

export default nextConfig;
