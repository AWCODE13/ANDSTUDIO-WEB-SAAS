import { Suspense } from "react";
import OrderView from "./order-view";

export default function Page({
  params,
}: {
  params: Promise<{ code: string }>;
}) {
  return (
    <Suspense fallback={<OrderSkeleton />}>
      <OrderView params={params} />
    </Suspense>
  );
}

function OrderSkeleton() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  );
}
