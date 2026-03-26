@extends('layouts.app', [
    'title' => 'Role Onboarding | Neolifeporium',
    'showDefaultNav' => false,
    'pageShellClass' => 'min-h-screen bg-[#f1effb]',
])

@section('content')
<section class="relative overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-[#5f33dc]/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-80 w-80 rounded-full bg-[#6d40ee]/20 blur-3xl"></div>

    <div class="mx-auto grid w-full max-w-7xl overflow-hidden rounded-2xl bg-white shadow-[0_30px_70px_rgba(34,21,84,0.22)] lg:grid-cols-[0.8fr_1.2fr]">
        <div class="hidden bg-[#fbfaff] p-10 lg:flex lg:flex-col lg:justify-between">
            <a href="{{ route('home') }}" class="inline-flex text-2xl font-black tracking-tight text-[#3d2f8f]">Neolifeporium</a>
            <div class="space-y-4">
                <h1 class="text-4xl font-black leading-tight text-[#2e255d]">Role Onboarding & Upgrades.</h1>
                <p class="max-w-md text-sm text-[#5c5680]">Apply for vendor or expert roles and track approval progress in one place.</p>
            </div>
            <div class="space-y-3">
                @foreach($roleStatuses as $status)
                    <article class="rounded-xl border border-[#dacfff] bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#5f33dc]">{{ str($status['role'])->replace('_', ' ')->title() }}</p>
                        <p class="mt-1 text-sm font-bold text-[#2e255d]">{{ strtoupper($status['status']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="bg-gradient-to-b from-[#6c3deb] to-[#5726d8] p-8 text-white sm:p-10">
            <h2 class="text-4xl font-black leading-tight">Complete Your Expansion</h2>
            <p class="mt-2 text-sm text-white/80">Submit details for role approval.</p>

            <div class="mt-7 grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                    <h3 class="text-lg font-bold">Become a Vendor</h3>
                    <form action="{{ route('roles.apply.vendor') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <input name="business_name" value="{{ old('business_name', $user->vendorProfile?->business_name) }}" placeholder="Business Name" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input name="business_type" value="{{ old('business_type', $user->vendorProfile?->business_type) }}" placeholder="Business Type" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input name="product_category" value="{{ old('product_category', $user->vendorProfile?->product_category) }}" placeholder="Product Category" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <textarea name="vendor_description" rows="3" placeholder="Business Description" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">{{ old('vendor_description', $user->vendorProfile?->description) }}</textarea>
                        <input name="vendor_region" value="{{ old('vendor_region', $user->vendorProfile?->region) }}" placeholder="Region" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input type="file" name="vendor_document" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        <button class="w-full rounded-full bg-[#ffcb43] px-4 py-2.5 text-sm font-bold text-[#2c1f68] transition hover:bg-[#ffd566]">Submit Vendor Application</button>
                    </form>
                </div>

                <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                    <h3 class="text-lg font-bold">Become an Expert</h3>
                    <form action="{{ route('roles.apply.expert') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <input name="specialty" value="{{ old('specialty', $user->agronomistProfile?->specialty) }}" placeholder="Specialization" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input name="experience_years" type="number" min="0" value="{{ old('experience_years', $user->agronomistProfile?->experience_years) }}" placeholder="Experience (years)" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input name="hourly_rate" type="number" min="0" step="0.01" value="{{ old('hourly_rate', $user->agronomistProfile?->hourly_rate) }}" placeholder="Session Rate" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <input name="regions_served_text" value="{{ old('regions_served_text', collect($user->agronomistProfile?->regions_served)->implode(', ')) }}" placeholder="Regions Served (comma-separated)" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        <textarea name="expert_bio" rows="3" placeholder="Professional Bio" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">{{ old('expert_bio', $user->agronomistProfile?->bio) }}</textarea>
                        <input type="file" name="certification_document" class="w-full rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        <button class="w-full rounded-full bg-[#ffcb43] px-4 py-2.5 text-sm font-bold text-[#2c1f68] transition hover:bg-[#ffd566]">Submit Expert Application</button>
                    </form>
                </div>
            </div>

            <a href="{{ route('dashboard.redirect') }}" class="mt-6 inline-flex min-w-36 items-center justify-center rounded-full border border-white/60 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                Back to dashboard
            </a>
        </div>
    </div>
</section>
@endsection
