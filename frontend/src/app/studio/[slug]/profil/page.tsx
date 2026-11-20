import { Suspense } from "react";
import StudioProfileView from "./profil-view";

export default function Page({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  return (
    <Suspense fallback={<ProfileSkeleton />}>
      <StudioProfileView params={params} />
    </Suspense>
  );
}

function ProfileSkeleton() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  );
}
