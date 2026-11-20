/**
 * URL dasar API. Nilai STATIS dari build-time env — tidak pernah diturunkan
 * dari window.location.hostname (mencegah SSRF / Host Header Injection).
 * ponytail: satu origin tetap; ubah ke allowlist bila kelak butuh multi-domain.
 */
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

/**
 * Domain utama platform (tanpa subdomain). Digunakan untuk deteksi subtenant.
 * Diisi dari NEXT_PUBLIC_APP_URL atau fallback ke APP_URL.
 */
const APP_URL_ORIGIN = (() => {
  try {
    return new URL(process.env.NEXT_PUBLIC_APP_URL || "http://localhost:8000").origin;
  } catch {
    return "http://localhost:8000";
  }
})();

export function getBaseUrl(): string {
  return API_URL;
}

/**
 * Ekstrak subdomain dari hostname browser.
 * Contoh: kavia.andstudio.com → "kavia"
 * Contoh: localhost:3000 → null
 * Contoh: andstudio.com → null
 */
export function getTenantSubdomain(): string | null {
  if (typeof window === "undefined") return null;

  const hostname = window.location.hostname;
  const originHost = new URL(APP_URL_ORIGIN).hostname;
  const parts = hostname.split(".");
  const originParts = originHost.split(".");

  // Perlu lebih banyak bagian dari origin domain untuk memiliki subdomain
  if (parts.length <= originParts.length) {
    return null;
  }

  const subdomainParts = parts.slice(0, parts.length - originParts.length);
  return subdomainParts.join(".") || null;
}

/**
 * Origin API (tanpa path /api) dipakai untuk membangun URL file statis.
 */
export function getApiOrigin(): string {
  try {
    return new URL(API_URL).origin;
  } catch {
    return "http://localhost:8000";
  }
}

/**
 * CSRF Token Management for SPA
 * 
 * Laravel sets XSRF-TOKEN cookie (encrypted) when session starts.
 * We need to:
 * 1. Call /api/csrf-cookie on app init to establish session & get cookie
 * 2. Read XSRF-TOKEN from document.cookie
 * 3. Send as X-XSRF-TOKEN header on mutating requests (POST, PUT, PATCH, DELETE)
 */

// Cached promise so initCsrf() only runs once per browser tab
let csrfInitPromise: Promise<void> | null = null;

function getCsrfTokenFromCookie(): string | null {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(/(^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[2]) : null;
}

export async function initCsrf(): Promise<void> {
  // Return cached promise if already initialized
  if (csrfInitPromise) return csrfInitPromise;

  csrfInitPromise = (async () => {
    try {
      // Panggil endpoint /sanctum/csrf-cookie di root URL backend (bukan di dalam /api)
      const res = await fetch(`${getApiOrigin()}/sanctum/csrf-cookie`, {
        method: "GET",
        credentials: "include",
        headers: { Accept: "application/json" },
      });

      if (!res.ok) {
        console.warn("[CSRF] Failed to initialize CSRF token:", res.status);
      }
    } catch (err) {
      console.warn("[CSRF] Error initializing CSRF token:", err);
    }
  })();

  return csrfInitPromise;
}

/**
 * Reset CSRF initialization (useful for testing or after logout)
 */
export function resetCsrfInit(): void {
  csrfInitPromise = null;
}

/**
 * Normalisasi URL media (foto paket, logo dsb) menjadi URL absolut yang bisa dimuat browser.
 */
export function resolveMediaUrl(src?: string | null): string | undefined {
  if (!src) return undefined;

  const origin = getApiOrigin();

  // Sudah absolut dengan http/https
  if (/^https?:\/\//i.test(src)) {
    try {
      const parsed = new URL(src);
      const isLocal = ["localhost", "127.0.0.1", "0.0.0.0", "::1"].includes(
        parsed.hostname
      );
      // Jika diakses dari HP / device lain di jaringan (bukan localhost),
      // ganti host localhost dengan origin aktif agar gambar bisa dimuat di HP.
      if (
        typeof window !== "undefined" &&
        isLocal &&
        window.location.hostname !== "localhost" &&
        window.location.hostname !== "127.0.0.1"
      ) {
        return `${origin}${parsed.pathname}${parsed.search}`;
      }
      if (isLocal && !parsed.port) {
        return `${origin}${parsed.pathname}${parsed.search}`;
      }
      return src;
    } catch {
      return src;
    }
  }

  // Path relatif (/storage/...) -> gabungkan dengan origin API
  if (src.startsWith("/")) {
    return `${origin}${src}`;
  }

  return src;
}

/**
 * Ambil handle Instagram dari URL medsos studio.
 * "https://instagram.com/kaviagraduation" -> "kaviagraduation"
 */
export function instagramHandle(url?: string | null): string {
  if (!url) return "";

  const handle =
    url
      .replace(/^https?:\/\//i, "")
      .replace(/^www\./i, "")
      .split("/")
      .filter(Boolean)
      .pop() ?? "";

  return handle.replace(/^@/, "");
}

/**
 * Bentuk respons API Laravel.
 */
export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
  errors?: Record<string, string[]>;
}

/**
 * Data registrasi studio baru.
 */
export interface RegisterTenantPayload {
  studio_name: string;
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
  terms: boolean | number;
  address: string;
  plan?: string;
  instagram_url?: string;
  operational_start?: string;
  operational_end?: string;
}

/**
 * Data booking yang dikirim ke API.
 */
export interface CreateBookingPayload {
  booking_slot_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  payment_option: "dp" | "full";
  notes?: string | null;
}

/**
 * Ulasan pelanggan.
 */
export interface ReviewPayload {
  booking_code?: string;
  email?: string;
  phone?: string;
  rating: number;
  comment?: string | null;
}

export interface StudioReview {
  id: number;
  customer_name: string;
  rating: number;
  comment: string | null;
  created_at: string | null;
  package_name?: string | null;
}

async function fetchAPI<T = unknown>(
  path: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  const method = (options.method || "GET").toUpperCase();
  const isMutating = ["POST", "PUT", "PATCH", "DELETE"].includes(method);

  // Initialize CSRF token for mutating requests
  if (isMutating) {
    await initCsrf();
  }

  // Prepare headers (FormData dipakai untuk upload file — jangan paksa Content-Type JSON)
  const headers: Record<string, string> = {
    Accept: "application/json",
    ...(options.headers as Record<string, string>),
  };
  if (!(options.body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }

  // Attach X-Tenant-Subdomain header jika request datang dari subdomain tenant
  const subdomain = getTenantSubdomain();
  if (subdomain) {
    headers["X-Tenant-Subdomain"] = subdomain;
  }

  // Attach X-XSRF-TOKEN header for mutating requests
  if (isMutating) {
    const csrfToken = getCsrfTokenFromCookie();
    if (csrfToken) {
      headers["X-XSRF-TOKEN"] = csrfToken;
    } else {
      console.warn("[CSRF] No XSRF-TOKEN cookie found for mutating request:", path);
    }
  }

  const res = await fetch(`${getBaseUrl()}${path}`, {
    headers,
    // Kirim cookie session Laravel (auto-login & tenant admin API)
    credentials: "include",
    ...options,
  });

  if (!res.ok) {
    const error = await res
      .json()
      .catch(() => ({ message: "Terjadi kesalahan" }));
    throw new Error(error.message || "Terjadi kesalahan");
  }

  return res.json();
}

export const api = {
  // Daftar studio baru (trial 14 hari)
  registerTenant: (body: RegisterTenantPayload | FormData) => {
    if (body instanceof FormData) {
      return fetchAPI<{
        booking_code: string | null;
        tenant_slug: string;
        tenant_name: string;
        trial_days: number;
        dashboard_url: string;
        message: string;
      }>(`/register`, {
        method: "POST",
        body,
      });
    }

    // Temuan #12: sanitize URL Instagram sebelum dikirim ke backend.
    const instagram_url = body.instagram_url?.trim();
    return fetchAPI<{
      booking_code: string | null;
      tenant_slug: string;
      tenant_name: string;
      trial_days: number;
      dashboard_url: string;
      message: string;
    }>(`/register`, {
      method: "POST",
      body: JSON.stringify({
        ...body,
        instagram_url: instagram_url ? instagram_url : undefined,
      }),
    });
  },

  // Daftar studio aktif (halaman beranda)
  getStudios: () =>
    fetchAPI<{
      studios: Array<{
        id: number;
        name: string;
        slug: string;
        logo_url: string | null;
        rating: number | null;
        reviews_count: number;
        instagram_url: string | null;
        operational_start: string | null;
        operational_end: string | null;
        address: string | null;
      }>;
    }>(`/studios`),

  // Statistik platform publik
  getStats: () =>
    fetchAPI<{
      studio_partners: number;
      total_bookings: number;
      client_satisfaction: number;
      uptime_months: number;
    }>(`/stats`),

  // Katalog studio
  getStudio: (slug: string) =>
    fetchAPI<{
      tenant: {
        id: number;
        name: string;
        slug: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_url: string | null;
        rating: number | null;
        reviews_count: number;
        instagram_url: string | null;
        operational_start: string | null;
        operational_end: string | null;
        booking_policy: string | null;
      };
      packages: Array<Record<string, unknown>>;
    }>(`/studio/${slug}`),

  // Ulasan + rata-rata rating studio
  getStudioReviews: (slug: string) =>
    fetchAPI<{
      rating: number | null;
      reviews_count: number;
      reviews: StudioReview[];
    }>(`/studio/${slug}/reviews`),

  // Kirim ulasan (hanya untuk pesanan yang sudah selesai)
  submitReview: (body: ReviewPayload) =>
    fetchAPI<{ review: StudioReview }>(`/reviews`, {
      method: "POST",
      body: JSON.stringify(body),
    }),

  // Detail paket + slot
  getPackage: (slug: string, packageId: number | string) =>
    fetchAPI<{
      package: Record<string, unknown>;
      availableSlots: Record<string, Array<Record<string, unknown>>>;
    }>(`/studio/${slug}/package/${packageId}`),

  // Buat booking
  createBooking: (
    slug: string,
    packageId: number | string,
    body: CreateBookingPayload
  ) =>
    fetchAPI<{
      booking_code: string;
      booking_status: string;
      payment_status: string;
      amount_to_pay: number;
      total_amount: number;
      payment_deadline: string | null;
      detail_url: string;
    }>(`/studio/${slug}/package/${packageId}/book`, {
      method: "POST",
      body: JSON.stringify(body),
    }),

  // Detail booking + snap token
  getBooking: (code: string) =>
    fetchAPI<Record<string, unknown>>(`/booking/success/${code}`),

  // Cari order (bisa banyak hasil jika pakai email/telepon)
  lookupOrder: (query: string) =>
    fetchAPI<{
      found: boolean;
      orders_count: number;
      orders: Array<Record<string, unknown>>;
    }>(`/pesanan?query=${encodeURIComponent(query)}`),

  // Detail order
  getOrder: (code: string) =>
    fetchAPI<{
      booking_code: string;
      booking_status: string;
      payment_status: string;
      is_fully_paid: boolean;
      can_review: boolean;
      review: {
        id: number;
        rating: number;
        comment: string | null;
        created_at: string | null;
      } | null;
      [key: string]: any;
    }>(`/pesanan/${code}`),

  // Bayar sisa via Midtrans
  payBalance: (code: string) =>
    fetchAPI<{ snap_token: string; amount: number; pay_url: string }>(
      `/pesanan/${code}/bayar-sisa`,
      { method: "POST" }
    ),

  // Perpanjang batas waktu pembayaran
  extendDeadline: (code: string) =>
    fetchAPI<{ payment_deadline: string; message: string }>(
      `/pesanan/${code}/perpanjang`,
      { method: "POST" }
    ),

  // Snap token untuk pelunasan sisa
  getPaySuccess: (code: string, snapToken: string) =>
    fetchAPI<{
      booking_code: string;
      snap_token: string;
      remaining: number;
      redirect_url: string;
    }>(
      `/pesanan/${code}/bayar-sisa/success?snapToken=${encodeURIComponent(snapToken)}`
    ),
};
