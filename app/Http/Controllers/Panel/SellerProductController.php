<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SellerProduct;
use App\Models\SellerProductMedia;
use App\Models\SellerStore;
use App\Services\Marketplace\SellerStoreService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerProductController extends Controller
{
    public function index(SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $store = $storeService->ensureForUser(auth()->user());

        $products = SellerProduct::query()
            ->where('user_id', auth()->id())
            ->with('media')
            ->latest('id')
            ->paginate(12);

        return view('panel.marketplace.products.index', compact('store', 'products'));
    }

    public function create(SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $store = $storeService->ensureForUser(auth()->user());
        $product = new SellerProduct([
            'type' => 'digital',
            'status' => 'draft',
        ]);

        return view('panel.marketplace.products.form', compact('store', 'product'));
    }

    public function store(Request $request, SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $store = $storeService->ensureForUser(auth()->user());
        $product = new SellerProduct([
            'user_id' => auth()->id(),
            'seller_store_id' => $store->id,
        ]);

        return $this->persist($request, $product, $store);
    }

    public function edit(SellerProduct $product, SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        abort_unless((int) $product->user_id === (int) auth()->id(), 403);
        $store = $storeService->ensureForUser(auth()->user());
        $product->load('media');

        return view('panel.marketplace.products.form', compact('store', 'product'));
    }

    public function update(Request $request, SellerProduct $product, SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        abort_unless((int) $product->user_id === (int) auth()->id(), 403);
        $store = $storeService->ensureForUser(auth()->user());

        return $this->persist($request, $product, $store);
    }

    public function destroy(SellerProduct $product)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        abort_unless((int) $product->user_id === (int) auth()->id(), 403);

        foreach ($product->media as $media) {
            UploadStorage::disk()->delete(UploadStorage::normalizePath($media->file_path));
        }

        if ($product->cover_path) {
            UploadStorage::disk()->delete(UploadStorage::normalizePath($product->cover_path));
        }

        if ($product->digital_file_path) {
            Storage::disk('local')->delete($product->digital_file_path);
        }

        $product->delete();

        return redirect()->route('panel.marketplace.products.index')->with('success', 'Produto removido com sucesso.');
    }

    public function destroyMedia(SellerProduct $product, SellerProductMedia $media)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        abort_unless((int) $product->user_id === (int) auth()->id(), 403);
        abort_unless((int) $media->seller_product_id === (int) $product->id, 404);

        UploadStorage::disk()->delete(UploadStorage::normalizePath($media->file_path));
        $media->delete();

        return back()->with('success', 'Midia removida com sucesso.');
    }

    private function persist(Request $request, SellerProduct $product, $store)
    {
        $isNew = !$product->exists;
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:80'],
            'type' => ['required', 'in:physical,digital'],
            'excerpt' => ['nullable', 'string', 'max:280'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
            'height_cm' => ['nullable', 'integer', 'min:1'],
            'width_cm' => ['nullable', 'integer', 'min:1'],
            'length_cm' => ['nullable', 'integer', 'min:1'],
            'cover' => ['nullable', 'image', 'max:6144'],
            'gallery.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,m4v,webm', 'max:20480'],
            'digital_file' => ['nullable', 'file', 'max:51200'],
            'digital_url' => ['nullable', 'url', 'max:2048'],
            'digital_instructions' => ['nullable', 'string', 'max:5000'],
            'remove_cover' => ['nullable', 'boolean'],
            'remove_digital_file' => ['nullable', 'boolean'],
        ]);

        $status = (string) ($data['status'] ?? 'draft');
        $type = (string) ($data['type'] ?? 'digital');
        if ($status === 'published') {
            if ($type === 'physical') {
                foreach (['stock', 'weight_grams', 'height_cm', 'width_cm', 'length_cm'] as $field) {
                    if (!filled($data[$field] ?? null)) {
                        return back()->withErrors([$field => 'Preencha todos os dados logisticos antes de publicar um produto fisico.'])->withInput();
                    }
                }

                $seller = auth()->user();
                foreach (['cep', 'address', 'city', 'state'] as $field) {
                    if (blank($seller->{$field})) {
                        return back()->withErrors([$field => 'Atualize seu endereco no perfil antes de publicar produtos fisicos.'])->withInput();
                    }
                }
            }

            if ($type === 'digital' && blank($data['digital_url'] ?? null) && !$request->hasFile('digital_file') && blank($product->digital_file_path)) {
                return back()->withErrors(['digital_file' => 'Envie um arquivo digital ou informe uma URL valida antes de publicar.'])->withInput();
            }
        }

        if ($request->boolean('remove_cover') && $product->cover_path) {
            UploadStorage::disk()->delete(UploadStorage::normalizePath($product->cover_path));
            $product->cover_path = null;
        }

        if ($request->boolean('remove_digital_file') && $product->digital_file_path) {
            Storage::disk('local')->delete($product->digital_file_path);
            $product->digital_file_path = null;
            $product->digital_file_name = null;
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = UploadStorage::storeUploadedFile($request->file('cover'), 'uploads/imagens/marketplace/products/covers');
        }

        if ($request->hasFile('digital_file')) {
            $data['digital_file_path'] = $request->file('digital_file')->store('seller-products/digital', 'local');
            $data['digital_file_name'] = $request->file('digital_file')->getClientOriginalName();
            $data['digital_delivery_type'] = 'file';
        } elseif (filled($data['digital_url'] ?? null)) {
            $data['digital_delivery_type'] = 'url';
        }

        if ($isNew || blank($product->slug)) {
            $product->slug = $this->makeSlug((string) $data['title'], (int) $store->id, $product->id ?: null);
        }

        $product->fill([
            'seller_store_id' => $store->id,
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'sku' => $data['sku'] ?? null,
            'type' => $type,
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'status' => $status,
            'is_featured' => $request->boolean('is_featured'),
            'stock' => $type === 'physical' ? (int) ($data['stock'] ?? 0) : 1,
            'weight_grams' => $type === 'physical' ? (int) ($data['weight_grams'] ?? 0) : null,
            'height_cm' => $type === 'physical' ? (int) ($data['height_cm'] ?? 0) : null,
            'width_cm' => $type === 'physical' ? (int) ($data['width_cm'] ?? 0) : null,
            'length_cm' => $type === 'physical' ? (int) ($data['length_cm'] ?? 0) : null,
            'digital_url' => $type === 'digital' ? ($data['digital_url'] ?? null) : null,
            'digital_instructions' => $type === 'digital' ? ($data['digital_instructions'] ?? null) : null,
            'published_at' => $status === 'published' ? ($product->published_at ?: now()) : null,
        ]);

        if (isset($data['cover_path'])) {
            $product->cover_path = $data['cover_path'];
        }

        if (isset($data['digital_file_path'])) {
            $product->digital_file_path = $data['digital_file_path'];
            $product->digital_file_name = $data['digital_file_name'];
            $product->digital_delivery_type = $data['digital_delivery_type'];
        } elseif ($type !== 'digital') {
            $product->digital_delivery_type = null;
            $product->digital_file_path = null;
            $product->digital_file_name = null;
            $product->digital_url = null;
            $product->digital_instructions = null;
        } elseif (filled($data['digital_url'] ?? null) && blank($product->digital_file_path)) {
            $product->digital_delivery_type = 'url';
        }

        $product->save();

        if ($request->hasFile('gallery')) {
            $sortOrder = (int) $product->media()->max('sort_order');
            foreach ($request->file('gallery') as $file) {
                $sortOrder++;
                $mime = (string) $file->getMimeType();
                $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = UploadStorage::storeUploadedFile($file, 'uploads/imagens/marketplace/products/gallery');

                $product->media()->create([
                    'media_type' => $mediaType,
                    'file_path' => $path,
                    'sort_order' => $sortOrder,
                ]);
            }
        }

        return redirect()
            ->route('panel.marketplace.products.edit', $product)
            ->with('success', $isNew ? 'Produto criado com sucesso.' : 'Produto atualizado com sucesso.');
    }

    private function makeSlug(string $title, int $storeId, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'produto';
        }

        $slug = $base;
        $suffix = 2;
        while (
            SellerProduct::query()
                ->where('seller_store_id', $storeId)
                ->where('slug', $slug)
                ->when($ignoreProductId, fn($query) => $query->where('id', '!=', $ignoreProductId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
