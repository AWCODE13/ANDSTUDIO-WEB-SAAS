import { Suspense } from "react";
import StudioView from "./studio-view";

export default function Page({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  return (
    <Suspense fallback={<StudioSkeleton />}>
      <StudioView params={params} />
    </Suspense>
  );
}

function StudioSkeleton() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  );
}
