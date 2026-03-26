<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\ProductStoreRequest;
use App\Http\Requests\Marketplace\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Marketplace\ProductCatalogService;
use App\Support\ApiResponse;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly ProductCatalogService $catalog) {}

    public function index()
    {
        return ProductResource::collection($this->catalog->marketplace(request()->all()));
    }

    public function show(string $slug)
    {
        return ProductResource::make($this->catalog->detail($slug));
    }

    public function store(ProductStoreRequest $request)
    {
        $product = auth()->user()->products()->create([
            ...$request->validated(),
            'slug' => Str::slug($request->string('name')).'-'.Str::lower(Str::random(5)),
        ]);

        return ApiResponse::success(ProductResource::make($product->load(['category', 'vendor', 'images', 'reviews'])), 'Product created.', 201);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $user = $request->user();
        $canEdit = $product->vendor_id === $user->id || $user->hasAnyRole(['admin', 'super_admin']);
        abort_unless($canEdit, 403, 'Unauthorized product update.');

        $payload = $request->validated();
        if (isset($payload['name'])) {
            $payload['slug'] = Str::slug($payload['name']).'-'.Str::lower(Str::random(5));
        }

        $product->update($payload);

        return ApiResponse::success(ProductResource::make($product->refresh()->load(['category', 'vendor', 'images', 'reviews'])), 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();
        $canDelete = $product->vendor_id === $user->id || $user->hasAnyRole(['admin', 'super_admin']);
        abort_unless($canDelete, 403, 'Unauthorized product delete.');

        $product->delete();

        return ApiResponse::success(null, 'Product deleted.');
    }
}
