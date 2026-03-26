@extends('layouts.app', ['title' => $mode === 'create' ? 'Create Product' : 'Edit Product'])

@section('content')
<section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-black text-palm">{{ $mode === 'create' ? 'Create product' : 'Edit product' }}</h1>

    <form action="{{ $mode === 'create' ? route('vendor.products.store') : route('vendor.products.update', $product) }}" method="POST" class="mt-6 space-y-4 rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
        @csrf
        @if($mode === 'edit')
            @method('PATCH')
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-semibold">Name</label>
                <input name="name" value="{{ old('name', $product->name) }}" required class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-sm font-semibold">SKU</label>
                <input name="sku" value="{{ old('sku', $product->sku) }}" required class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-sm font-semibold">Category</label>
                <select name="category_id" required class="mt-1 w-full rounded-xl border-slate-200">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Type</label>
                <select name="product_type" required class="mt-1 w-full rounded-xl border-slate-200">
                    @foreach(['physical', 'service', 'digital'] as $type)
                        <option value="{{ $type }}" @selected(old('product_type', $product->product_type ?: 'physical') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Price</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-sm font-semibold">Inventory</label>
                <input type="number" min="0" name="inventory" value="{{ old('inventory', $product->inventory ?? 0) }}" required class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-sm font-semibold">Crop type</label>
                <input name="crop_type" value="{{ old('crop_type', $product->crop_type) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-sm font-semibold">Region</label>
                <input name="region" value="{{ old('region', $product->region) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold">Short description</label>
            <textarea name="short_description" rows="2" class="mt-1 w-full rounded-xl border-slate-200">{{ old('short_description', $product->short_description) }}</textarea>
        </div>
        <div>
            <label class="text-sm font-semibold">Description</label>
            <textarea name="description" rows="5" required class="mt-1 w-full rounded-xl border-slate-200">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->is_active ?? true))>
                <span class="text-sm">Active</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" @checked((bool) old('is_featured', $product->is_featured ?? false))>
                <span class="text-sm">Featured</span>
            </label>
        </div>

        <button class="rounded-full bg-palm px-6 py-3 text-sm font-semibold text-white">{{ $mode === 'create' ? 'Create product' : 'Update product' }}</button>
    </form>
</section>
@endsection
