export const dynamic = "force-static";

import { NextResponse } from "next/server";

export function GET() {
  return NextResponse.json({ ok: true, service: "saas-aviation-web", mode: "sample-adapter" });
}
