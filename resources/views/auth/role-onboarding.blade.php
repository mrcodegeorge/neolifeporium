@extends('layouts.app', ['title' => 'Role Onboarding | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-0">
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-900">Role Onboarding & Upgrades</h1>
        <p class="mt-2 text-sm text-slate-600">Apply for new roles and monitor approval status.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach($roleStatuses as $status)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">{{ str($status['role'])->replace('_', ' ')->title() }}</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ strtoupper($status['status']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $status['assigned'] ? 'Role active' : 'Role not active yet' }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-xl font-bold text-slate-900">Become a Vendor</h2>
            <form action="{{ route('roles.apply.vendor') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input name="business_name" value="{{ old('business_name', $user->vendorProfile?->business_name) }}" placeholder="Business Name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="business_type" value="{{ old('business_type', $user->vendorProfile?->business_type) }}" placeholder="Business Type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="product_category" value="{{ old('product_category', $user->vendorProfile?->product_category) }}" placeholder="Product Category" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <textarea name="vendor_description" rows="3" placeholder="Business Description" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ old('vendor_description', $user->vendorProfile?->description) }}</textarea>
                <input name="vendor_region" value="{{ old('vendor_region', $user->vendorProfile?->region) }}" placeholder="Region" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input type="file" name="vendor_document" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                <button class="w-full rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Submit Vendor Application</button>
            </form>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-xl font-bold text-slate-900">Become an Expert</h2>
            <form action="{{ route('roles.apply.expert') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input name="specialty" value="{{ old('specialty', $user->agronomistProfile?->specialty) }}" placeholder="Specialization" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="experience_years" type="number" min="0" value="{{ old('experience_years', $user->agronomistProfile?->experience_years) }}" placeholder="Experience (years)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="hourly_rate" type="number" min="0" step="0.01" value="{{ old('hourly_rate', $user->agronomistProfile?->hourly_rate) }}" placeholder="Session Rate" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="regions_served_text" value="{{ old('regions_served_text', collect($user->agronomistProfile?->regions_served)->implode(', ')) }}" placeholder="Regions Served (comma-separated)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <textarea name="expert_bio" rows="3" placeholder="Professional Bio" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ old('expert_bio', $user->agronomistProfile?->bio) }}</textarea>
                <input type="file" name="certification_document" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                <button class="w-full rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Submit Expert Application</button>
            </form>
        </div>
    </div>
</section>
@endsection
