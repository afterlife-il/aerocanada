export const dynamic = "force-static";

import { NextResponse } from "next/server";
import { data } from "@/lib/data";

export function GET() {
  return NextResponse.json({ data: data.companies });
}
