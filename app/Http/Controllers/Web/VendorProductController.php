<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\ProductStoreRequest;
use App\Http\Requests\Marketplace\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorProductController extends Controller
{
    public function index(): View
    {
        $vendorId = $this->resolveVendorId();

        return view('vendor.products.index', [
            'products' => Product::query()->where('vendor_id', $vendorId)->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('vendor.products.form', [
            'product' => new Product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        Product::query()->create([
            ...$request->validated(),
            'vendor_id' => $this->resolveVendorId(),
            'slug' => Str::slug($request->string('name')).'-'.Str::lower(Str::random(5)),
            'currency' => 'GHS',
        ]);

        return redirect()->route('vendor.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->assertOwnership($product);

        return view('vendor.products.form', [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $this->assertOwnership($product);

        $payload = $request->validated();
        if (isset($payload['name'])) {
            $payload['slug'] = Str::slug($payload['name']).'-'.Str::lower(Str::random(5));
        }

        $product->update($payload);

        return redirect()->route('vendor.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->assertOwnership($product);
        $product->delete();

        return redirect()->route('vendor.products.index')->with('status', 'Product deleted.');
    }

    private function resolveVendorId(): int
    {
        if (auth()->check()) {
            return auth()->id();
        }

        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'vendor'))
            ->value('id') ?? 1;
    }

    private function assertOwnership(Product $product): void
    {
        abort_unless($product->vendor_id === $this->resolveVendorId(), 403, 'Unauthorized vendor action.');
    }
}
