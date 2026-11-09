@extends('layouts.tenant')

@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')
@section('page_title', 'Paket Foto')

@section('content')
 <div class="max-w-3xl bg-base-100 rounded-2xl border border-base-300 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)] p-8">
 <div class="mb-6 border-b border-base-300 pb-4">
 <h2 class="text-xl font-bold text-base-content">{{ $package->exists ? 'Edit Paket Foto' : 'Tambah Paket Foto Baru' }}</h2>
 <p class="text-base-content/60 text-sm mt-1">Lengkapi informasi layanan paket foto di bawah ini.</p>
 </div>

 @if($errors->any())
 <div class="mb-6 p-4 bg-error/10 text-error text-sm rounded-xl">
 <ul class="list-disc pl-5">
 @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
 </ul>
 </div>
 @endif

 <form action="{{ $package->exists ? route('tenant.packages.update', $package->id) : route('tenant.packages.store') }}" method="POST" class="space-y-5">
 @csrf
 @if($package->exists) @method('PUT') @endif

 <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Nama Paket</label>
 <input type="text" name="name" value="{{ old('name', $package->name) }}" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Kategori</label>
 <select name="category" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
 <option value="graduation" {{ old('category', $package->category) == 'graduation' ? 'selected' : '' }}>Graduation</option>
 <option value="prewedding" {{ old('category', $package->category) == 'prewedding' ? 'selected' : '' }}>Pre-Wedding</option>
 <option value="self-photo" {{ old('category', $package->category) == 'self-photo' ? 'selected' : '' }}>Self Photo</option>
 <option value="product" {{ old('category', $package->category) == 'product' ? 'selected' : '' }}>Katalog Produk</option>
 <option value="family" {{ old('category', $package->category) == 'family' ? 'selected' : '' }}>Family Portrait</option>
 </select>
 </div>
 </div>

 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Deskripsi Paket (Fasilitas, Edit, dll)</label>
 <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">{{ old('description', $package->description) }}</textarea>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Harga (Rp)</label>
 <input type="number" name="price" value="{{ old('price', $package->price) }}" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary outline-none transition" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Minimal DP (Rp)</label>
 <input type="number" name="down_payment_min" value="{{ old('down_payment_min', $package->down_payment_min) }}" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary outline-none transition" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Durasi Sesi (Menit)</label>
 <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $package->duration_minutes ?? 60) }}" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary outline-none transition" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Maksimal Orang</label>
 <input type="number" name="max_persons" value="{{ old('max_persons', $package->max_persons ?? 1) }}" class="w-full px-4 py-2.5 bg-base-200 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:bg-base-100 focus:border-primary outline-none transition" required>
 </div>
 </div>

 <div class="flex items-center gap-2 pt-2">
 <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded border-base-300">
 <label for="is_active" class="text-sm font-semibold text-base-content/80">Tampilkan paket ini di Katalog Publik klien</label>
 </div>

 @if($package->exists)
 <div class="pt-6 border-t border-base-300">
 <h3 class="text-sm font-bold text-base-content mb-4">Foto Paket</h3>
 
 @if($package->photos()->count() < 4)
 <div class="mb-6">
 <label class="block text-xs font-bold text-base-content/80 mb-3">Unggah Foto (JPG/PNG, Max 5MB) - Maksimal 4 foto</label>
 <div class="flex items-center justify-center w-full">
 <label class="flex flex-col items-center justify-center w-full px-4 py-6 border-2 border-dashed border-base-300 rounded-xl cursor-pointer hover:bg-base-200 transition">
 <div class="flex flex-col items-center justify-center pt-5 pb-6">
 <svg class="w-8 h-8 text-base-content/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
 <p class="text-sm text-base-content/70">Klik untuk unggah atau drag & drop</p>
 </div>
 <input type="file" id="photoUpload" accept="image/jpeg,image/png" class="hidden">
 </label>
 </div>
 <div id="uploadStatus" class="mt-2 text-sm"></div>
 </div>
 @else
 <div class="mb-6 p-4 bg-warning/10 text-warning text-sm rounded-xl">
 Maksimal 4 foto tercapai. Hapus foto yang ada untuk mengunggah yang baru.
 </div>
 @endif

 <div id="photosGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4">
 @foreach($package->photos()->orderBy('sort_order')->get() as $photo)
 <div class="photo-item relative group" data-photo-id="{{ $photo->id }}">
 <img src="{{ url('storage/' . $photo->photo_url) }}" alt="Foto paket {{ $loop->iteration }}" class="w-full h-40 object-cover rounded-lg bg-base-100 border border-base-300">

 {{-- Overlay hover: pointer-events-none supaya tidak menutupi klik
 ke tombol hapus / reorder di bawahnya --}}
 <div class="absolute inset-0 bg-black bg-opacity-40 rounded-lg transition flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 pointer-events-none z-10">
 <button type="button" class="reorder-btn btn-up pointer-events-auto px-2 py-1 bg-primary text-white text-xs rounded hover:bg-primary/90" data-action="reorder" data-direction="up" title="Naik" aria-label="Naik">↑</button>
 <button type="button" class="reorder-btn btn-down pointer-events-auto px-2 py-1 bg-primary text-white text-xs rounded hover:bg-primary/90" data-action="reorder" data-direction="down" title="Turun" aria-label="Turun">↓</button>
 </div>

 {{-- Tombol hapus: mengambang di pojok kanan atas foto, selalu di atas overlay --}}
 <button type="button" class="delete-btn absolute top-2 right-2 bg-error text-white p-1 rounded-full z-20 shadow-md hover:bg-error transition" data-action="delete" title="Hapus foto" aria-label="Hapus foto">X</button>
 </div>
 @endforeach
 </div>
 </div>
 @endif

 <div class="pt-6 flex gap-3">
 <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-bold rounded-xl transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">Simpan Paket</button>
 <a href="{{ route('tenant.packages.index') }}" class="px-6 py-2.5 bg-base-200 text-base-content/80 font-bold rounded-xl hover:bg-base-300 transition">Batal</a>
 </div>
 </form>
 </div>

 @if($package->exists)
 <script>
 const packageId = {{ $package->id }};
 const uploadInput = document.getElementById('photoUpload');
 const uploadStatus = document.getElementById('uploadStatus');
 const photosGrid = document.getElementById('photosGrid');

 // Input upload hanya ada saat jumlah foto < 4. Jangan crash kalau tidak ada,
 // karena seluruh script jadi mati dan tombol hapus/reorder tidak merespons.
 if (uploadInput) {
 uploadInput.addEventListener('change', handleUpload);
 }

 document.addEventListener('click', (e) => {
 const delBtn = e.target.closest('[data-action="delete"]');
 if (delBtn) {
 e.preventDefault();
 const photoItem = delBtn.closest('.photo-item');
 const photoId = photoItem.dataset.photoId;
 if (confirm('Hapus foto ini?')) deletePhoto(photoId, photoItem);
 return;
 }

 const reorderBtn = e.target.closest('[data-action="reorder"]');
 if (reorderBtn) {
 e.preventDefault();
 const photoItem = reorderBtn.closest('.photo-item');
 const direction = reorderBtn.dataset.direction;
 reorderPhoto(photoItem, direction);
 }
 });

 function handleUpload(e) {
 const file = e.target.files[0];
 if (!file) return;

 uploadStatus.textContent = 'Mengunggah...';
 uploadStatus.className = 'mt-2 text-sm text-primary';

 const fd = new FormData();
 fd.append('photo', file);

 fetch(`/app/packages/${packageId}/photos`, {
 method: 'POST',
 body: fd,
 headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
 })
 .then(r => r.json())
 .then(data => {
 if (data.success) {
 uploadStatus.textContent = '✓ Foto berhasil diunggah';
 uploadStatus.className = 'mt-2 text-sm text-success';
 uploadInput.value = '';
 setTimeout(() => location.reload(), 500);
 } else {
 uploadStatus.textContent = data.message || 'Gagal mengunggah foto';
 uploadStatus.className = 'mt-2 text-sm text-error';
 }
 })
 .catch(() => {
 uploadStatus.textContent = 'Error: Gagal mengunggah foto';
 uploadStatus.className = 'mt-2 text-sm text-error';
 });
 }

 function deletePhoto(photoId, el) {
 fetch(`/app/photos/${photoId}`, {
 method: 'DELETE',
 headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
 })
 .then(r => r.json())
 .then(data => {
 if (data.success) {
 // Reload supaya form upload kembali muncul (< 4 foto) & urutan rapi
 location.reload();
 }
 });
 }

 function reorderPhoto(el, direction) {
 const photos = Array.from(photosGrid.querySelectorAll('.photo-item'));
 const idx = photos.indexOf(el);
 const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
 
 if (swapIdx < 0 || swapIdx >= photos.length) return;

 const photoId = el.dataset.photoId;
 const targetId = photos[swapIdx].dataset.photoId;

 fetch(`/app/packages/${packageId}/photos/reorder`, {
 method: 'POST',
 body: JSON.stringify({ photo_id: photoId, swap_with_id: targetId }),
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
 }
 })
 .then(r => r.json())
 .then(data => {
 if (data.success) setTimeout(() => location.reload(), 200);
 });
 }
 </script>
 @endif
@endsection