@extends('layouts.app', ['title' => 'Vendor Products | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">Vendor</p>
            <h1 class="mt-2 text-3xl font-black text-palm">Manage products</h1>
        </div>
        <a href="{{ route('vendor.products.create') }}" class="rounded-full bg-palm px-5 py-3 text-sm font-semibold text-white">Add product</a>
    </div>

    <div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/5">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Product</th>
                    <th class="px-4 py-3 text-left font-semibold">SKU</th>
                    <th class="px-4 py-3 text-left font-semibold">Price</th>
                    <th class="px-4 py-3 text-left font-semibold">Inventory</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr>
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->sku }}</td>
                        <td class="px-4 py-3">GHS {{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $product->inventory }}</td>
                        <td class="px-4 py-3">{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('vendor.products.edit', $product) }}" class="rounded-lg border border-slate-200 px-3 py-1 font-semibold">Edit</a>
                            <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-2 rounded-lg border border-red-200 px-3 py-1 font-semibold text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</section>
@endsection
