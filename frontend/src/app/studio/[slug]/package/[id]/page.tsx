import { Suspense } from "react";
import PackageView from "./package-view";

export default function Page({
  params,
}: {
  params: Promise<{ slug: string; id: string }>;
}) {
  return (
    <Suspense fallback={<PackageSkeleton />}>
      <PackageView params={params} />
    </Suspense>
  );
}

function PackageSkeleton() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  );
}
