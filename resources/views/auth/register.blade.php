@extends('layouts.app', [
    'title' => 'Create Account | Neolifeporium',
])

@section('content')
<section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-0" x-data="registrationWizard()">
    <div class="rounded-3xl bg-white p-7 shadow-lg shadow-black/5 sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-palm">Create Your Neolifeporium Account</h1>
                <p class="mt-2 text-sm text-slate-600">Choose one or more roles and complete onboarding in one flow.</p>
            </div>
            <div class="rounded-2xl bg-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                Step <span x-text="step"></span> of 3
            </div>
        </div>

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="mt-8 space-y-8">
            @csrf

            <div x-show="step === 1" class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="text-sm font-semibold text-slate-700">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                    @error('name')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                    @error('email')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="text-sm font-semibold text-slate-700">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                    @error('phone')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                    @error('password')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                </div>
            </div>

            <div x-show="step === 2" class="space-y-4">
                <h2 class="text-xl font-bold text-slate-900">What do you want to do?</h2>
                <p class="text-sm text-slate-600">Select one or more roles. Vendor and Expert applications require admin approval.</p>
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-palm/60" :class="roles.includes('farmer') ? 'bg-emerald-50 border-emerald-300' : ''">
                        <input type="checkbox" class="sr-only" name="roles[]" value="farmer" x-model="roles">
                        <p class="text-lg font-bold text-slate-900">Farmer</p>
                        <p class="mt-1 text-sm text-slate-600">Buy tools, track orders, and receive crop insights.</p>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-palm/60" :class="roles.includes('vendor') ? 'bg-amber-50 border-amber-300' : ''">
                        <input type="checkbox" class="sr-only" name="roles[]" value="vendor" x-model="roles">
                        <p class="text-lg font-bold text-slate-900">Vendor</p>
                        <p class="mt-1 text-sm text-slate-600">Sell agritech products and manage your storefront.</p>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-palm/60" :class="roles.includes('agronomist') ? 'bg-sky-50 border-sky-300' : ''">
                        <input type="checkbox" class="sr-only" name="roles[]" value="agronomist" x-model="roles">
                        <p class="text-lg font-bold text-slate-900">Expert</p>
                        <p class="mt-1 text-sm text-slate-600">Provide advisory sessions and agronomy expertise.</p>
                    </label>
                </div>
                @error('roles')<p class="text-sm font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="step === 3" class="space-y-7">
                <div x-show="roles.includes('farmer')" class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.15em] text-emerald-700">Farmer Onboarding</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Region</label>
                            <input name="region" value="{{ old('region', 'Greater Accra') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @error('region')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">District</label>
                            <input name="district" value="{{ old('district') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Location</label>
                            <input name="location" value="{{ old('location') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Farm Size (hectares)</label>
                            <input name="farm_size_hectares" type="number" step="0.01" min="0" value="{{ old('farm_size_hectares') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Crop Types (comma-separated)</label>
                            <input name="crop_types_text" value="{{ old('crop_types_text') }}" placeholder="maize, cassava, tomato" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div x-show="roles.includes('vendor')" class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.15em] text-amber-700">Vendor Onboarding</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Business Name</label>
                            <input name="business_name" value="{{ old('business_name') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @error('business_name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Business Type</label>
                            <input name="business_type" value="{{ old('business_type') }}" placeholder="Retailer, Distributor..." class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Product Category</label>
                            <input name="product_category" value="{{ old('product_category') }}" placeholder="Seeds, Irrigation..." class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Region</label>
                            <input name="vendor_region" value="{{ old('vendor_region') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Business Description</label>
                            <textarea name="vendor_description" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ old('vendor_description') }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Verification Document (optional)</label>
                            <input type="file" name="vendor_document" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        </div>
                    </div>
                </div>

                <div x-show="roles.includes('agronomist')" class="rounded-2xl border border-sky-200 bg-sky-50/60 p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.15em] text-sky-700">Expert Onboarding</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Specialization</label>
                            <input name="specialty" value="{{ old('specialty') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @error('specialty')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Experience (years)</label>
                            <input name="experience_years" type="number" min="0" value="{{ old('experience_years') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Session Rate (GHS)</label>
                            <input name="hourly_rate" type="number" step="0.01" min="0" value="{{ old('hourly_rate') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Regions Served</label>
                            <input name="regions_served_text" value="{{ old('regions_served_text') }}" placeholder="Greater Accra, Ashanti" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Professional Bio</label>
                            <textarea name="expert_bio" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ old('expert_bio') }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Certification Document (optional)</label>
                            <input type="file" name="certification_document" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-between gap-3 border-t border-slate-200 pt-5">
                <button type="button" x-show="step > 1" @click="step--" class="rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700">Back</button>
                <button type="button" x-show="step !== 3" @click="nextStep" class="ml-auto rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white" style="display:inline-block;background:#0f172a;color:#fff;">
                    Continue
                </button>
                <button type="submit" x-show="step === 3" class="ml-auto rounded-full bg-palm px-6 py-3 text-sm font-bold uppercase tracking-[0.15em] text-white transition hover:bg-slate-950">
                    Create Account
                </button>
            </div>
        </form>

        <p class="mt-6 text-sm text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-palm hover:text-leaf">Sign in</a>
        </p>
    </div>
</section>

<script>
function registrationWizard() {
    return {
        step: 1,
        roles: @json(old('roles', old('role') ? [old('role')] : ['farmer'])),
        nextStep() {
            if (this.step === 2 && this.roles.length === 0) {
                return;
            }
            this.step += 1;
        },
    };
}
</script>
@endsection
