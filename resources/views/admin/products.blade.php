@extends('layouts.app', ['title' => 'Admin Products | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Administration</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Product Moderation</h1>
        </div>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Dashboard</a>
    </div>

    <form method="GET" action="{{ route('admin.products') }}" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search product or SKU" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
        <select name="status" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All statuses</option>
            <option value="active" @selected($filters['status'] === 'active')>Active</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
        </select>
        <select name="featured" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">Featured and normal</option>
            <option value="featured" @selected($filters['featured'] === 'featured')>Featured only</option>
            <option value="regular" @selected($filters['featured'] === 'regular')>Regular only</option>
        </select>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
        <a href="{{ route('admin.products') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Reset</a>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/5">
        <table class="min-w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-[0.15em] text-slate-500">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Vendor</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Inventory</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">SKU: {{ $product->sku }}</p>
                            <div class="mt-2 flex gap-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($product->is_featured)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Featured</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->vendor?->vendorProfile?->business_name ?? $product->vendor?->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->category?->name }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">GHS {{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $product->inventory <= 10 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $product->inventory }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <form action="{{ route('admin.products.moderate', $product) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $product->is_active ? 0 : 1 }}">
                                    <button class="rounded-xl px-3 py-2 text-xs font-semibold {{ $product->is_active ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                        {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.products.feature', $product) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-xl px-3 py-2 text-xs font-semibold {{ $product->is_featured ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $product->is_featured ? 'Unfeature' : 'Feature' }}
                                    </button>
                                </form>
                                <details class="text-left">
                                    <summary class="cursor-pointer rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">Edit Inputs</summary>
                                    <form action="{{ route('admin.products.inputs', $product) }}" method="POST" class="mt-3 w-[26rem] max-w-[80vw] space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <input name="name" value="{{ old('name', $product->name) }}" placeholder="Name" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="SKU" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <select name="category_id" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === (int) $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="product_type" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                                @foreach(['physical','service','digital'] as $type)
                                                    <option value="{{ $type }}" @selected(old('product_type', $product->product_type) === $type)>{{ ucfirst($type) }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" placeholder="Price" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input type="number" step="0.01" min="0" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" placeholder="Compare Price" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input name="currency" value="{{ old('currency', $product->currency) }}" placeholder="Currency" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input type="number" min="0" name="inventory" value="{{ old('inventory', $product->inventory) }}" placeholder="Inventory" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input name="crop_type" value="{{ old('crop_type', $product->crop_type) }}" placeholder="Crop Type" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input name="region" value="{{ old('region', $product->region) }}" placeholder="Region" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                        </div>
                                        <input name="short_description" value="{{ old('short_description', $product->short_description) }}" placeholder="Short description" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                        <textarea name="description" rows="3" placeholder="Description" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs">{{ old('description', $product->description) }}</textarea>
                                        <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Save Inputs</button>
                                    </form>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">No products match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</section>
@endsection
