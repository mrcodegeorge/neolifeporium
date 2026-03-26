@extends('layouts.app', ['title' => 'Vendor Panel | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between">
        <h1 class="text-3xl font-black text-palm">Vendor analytics panel</h1>
        <a href="{{ route('vendor.products.index') }}" class="rounded-full bg-palm px-5 py-3 text-sm font-semibold text-white">Manage products</a>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        @foreach($stats as $label => $value)
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <p class="text-sm uppercase tracking-[0.2em] text-slate-500">{{ str($label)->replace('_', ' ') }}</p>
                <p class="mt-2 text-3xl font-black text-palm">{{ is_numeric($value) ? number_format($value, 2) : $value }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
