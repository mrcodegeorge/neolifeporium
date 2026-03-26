@extends('layouts.app', [
    'title' => 'Create Account | Neolifeporium',
    'showDefaultNav' => false,
    'pageShellClass' => 'min-h-screen bg-[#f1effb]',
])

@section('content')
<section class="relative overflow-hidden px-4 py-10 sm:px-6 lg:px-8" x-data="registrationWizard()">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-[#5f33dc]/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-80 w-80 rounded-full bg-[#6d40ee]/20 blur-3xl"></div>

    <div class="mx-auto grid w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-[0_30px_70px_rgba(34,21,84,0.22)] md:grid-cols-2">
        <div class="relative hidden bg-[#fbfaff] p-10 md:flex md:flex-col md:justify-between">
            <a href="{{ route('home') }}" class="inline-flex text-2xl font-black tracking-tight text-[#3d2f8f]">Neolifeporium</a>

            <div class="space-y-5">
                <h1 class="text-4xl font-black leading-tight text-[#2e255d]">Create Your Agritech Identity.</h1>
                <p class="max-w-md text-sm text-[#5c5680]">Join as a farmer, vendor, or agronomist and unlock the tools that fit your goals.</p>
                <div class="inline-flex rounded-full bg-[#ece5ff] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-[#5f33dc]">
                    Step <span class="mx-1" x-text="step"></span> of 3
                </div>
            </div>

            <div class="rounded-2xl border border-[#dacfff] bg-white p-4">
                <svg viewBox="0 0 520 320" class="h-56 w-full">
                    <rect x="0" y="0" width="520" height="320" fill="#fbfaff"></rect>
                    <circle cx="120" cy="100" r="44" fill="#ece5ff"></circle>
                    <circle cx="402" cy="88" r="54" fill="#ece5ff"></circle>
                    <rect x="208" y="174" width="92" height="86" rx="14" fill="#f0e7ff"></rect>
                    <rect x="228" y="146" width="50" height="28" rx="8" fill="#d8c7ff"></rect>
                    <circle cx="114" cy="140" r="30" fill="#5f33dc"></circle>
                    <rect x="84" y="168" width="62" height="92" rx="22" fill="#5f33dc"></rect>
                    <circle cx="382" cy="132" r="34" fill="#5f33dc"></circle>
                    <rect x="350" y="168" width="66" height="92" rx="24" fill="#5f33dc"></rect>
                    <path d="M144 190L230 196" stroke="#5f33dc" stroke-width="6" stroke-linecap="round"></path>
                    <path d="M334 188L294 196" stroke="#5f33dc" stroke-width="6" stroke-linecap="round"></path>
                    <circle cx="256" cy="98" r="32" fill="#ffffff" stroke="#5f33dc" stroke-width="3" stroke-dasharray="7 7"></circle>
                    <circle cx="256" cy="98" r="10" fill="#d8c7ff"></circle>
                    <path d="M254 109V124" stroke="#5f33dc" stroke-width="4" stroke-linecap="round"></path>
                    <path d="M244 261H280" stroke="#8b69f3" stroke-width="8" stroke-linecap="round"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-b from-[#6c3deb] to-[#5726d8] p-8 text-white sm:p-10">
            <h2 class="text-5xl font-black leading-none">Welcome!</h2>
            <p class="mt-3 text-sm text-white/80">Set up your Neolifeporium account and onboarding profile.</p>

            <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
                @csrf

                <div x-show="step === 1" x-cloak class="space-y-4">
                    <div>
                        <label for="name" class="sr-only">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="Your full name" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                        @error('name')<p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Your e-mail" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                        @error('email')<p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="sr-only">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="Your phone" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                        @error('phone')<p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required placeholder="Create password" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                        @error('password')<p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Confirm password" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                    </div>
                </div>

                <div x-show="step === 2" x-cloak class="space-y-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#ffcb43]">Choose role(s)</p>
                    <div class="grid gap-3">
                        <label class="cursor-pointer rounded-2xl border border-white/25 bg-white/10 p-4 transition hover:bg-white/15" :class="roles.includes('farmer') ? 'border-[#ffcb43] bg-white/20' : ''">
                            <input type="checkbox" class="sr-only" name="roles[]" value="farmer" x-model="roles">
                            <p class="text-base font-bold">Farmer</p>
                            <p class="mt-1 text-sm text-white/80">Buy tools, track orders, and receive recommendations.</p>
                        </label>
                        <label class="cursor-pointer rounded-2xl border border-white/25 bg-white/10 p-4 transition hover:bg-white/15" :class="roles.includes('vendor') ? 'border-[#ffcb43] bg-white/20' : ''">
                            <input type="checkbox" class="sr-only" name="roles[]" value="vendor" x-model="roles">
                            <p class="text-base font-bold">Vendor</p>
                            <p class="mt-1 text-sm text-white/80">List agritech products and manage your storefront.</p>
                        </label>
                        <label class="cursor-pointer rounded-2xl border border-white/25 bg-white/10 p-4 transition hover:bg-white/15" :class="roles.includes('agronomist') ? 'border-[#ffcb43] bg-white/20' : ''">
                            <input type="checkbox" class="sr-only" name="roles[]" value="agronomist" x-model="roles">
                            <p class="text-base font-bold">Agronomist (Expert)</p>
                            <p class="mt-1 text-sm text-white/80">Offer paid advisory services to farmers.</p>
                        </label>
                    </div>
                    @error('roles')<p class="text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>@enderror
                </div>

                <div x-show="step === 3" x-cloak class="max-h-[48vh] space-y-5 overflow-y-auto pr-1">
                    <div x-show="roles.includes('farmer')" class="rounded-2xl border border-white/25 bg-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ffcb43]">Farmer onboarding</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <input name="region" value="{{ old('region', 'Greater Accra') }}" placeholder="Region" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="district" value="{{ old('district') }}" placeholder="District" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="location" value="{{ old('location') }}" placeholder="Location" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="farm_size_hectares" type="number" step="0.01" min="0" value="{{ old('farm_size_hectares') }}" placeholder="Farm size (ha)" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="crop_types_text" value="{{ old('crop_types_text') }}" placeholder="Crop types: maize, cassava" class="sm:col-span-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                        </div>
                    </div>

                    <div x-show="roles.includes('vendor')" class="rounded-2xl border border-white/25 bg-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ffcb43]">Vendor onboarding</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <input name="business_name" value="{{ old('business_name') }}" placeholder="Business name" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="business_type" value="{{ old('business_type') }}" placeholder="Business type" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="product_category" value="{{ old('product_category') }}" placeholder="Product category" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="vendor_region" value="{{ old('vendor_region') }}" placeholder="Region" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <textarea name="vendor_description" rows="3" placeholder="Business description" class="sm:col-span-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">{{ old('vendor_description') }}</textarea>
                            <input type="file" name="vendor_document" class="sm:col-span-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        </div>
                    </div>

                    <div x-show="roles.includes('agronomist')" class="rounded-2xl border border-white/25 bg-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ffcb43]">Expert onboarding</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <input name="specialty" value="{{ old('specialty') }}" placeholder="Specialization" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="experience_years" type="number" min="0" value="{{ old('experience_years') }}" placeholder="Experience (years)" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="hourly_rate" type="number" step="0.01" min="0" value="{{ old('hourly_rate') }}" placeholder="Session rate (GHS)" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <input name="regions_served_text" value="{{ old('regions_served_text') }}" placeholder="Regions served" class="rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">
                            <textarea name="expert_bio" rows="3" placeholder="Professional bio" class="sm:col-span-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400">{{ old('expert_bio') }}</textarea>
                            <input type="file" name="certification_document" class="sm:col-span-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                        </div>
                    </div>
                </div>

                <div class="pt-1">
                    <div class="mb-4 h-1.5 w-full rounded-full bg-[#7b59e9]">
                        <div class="h-full rounded-full bg-[#ffcb43] transition-all" :style="`width:${(step/3)*100}%`"></div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="step > 1" x-cloak @click="step--" class="inline-flex min-w-24 items-center justify-center rounded-full border border-white/60 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                            Back
                        </button>

                        <button type="button" x-show="step < 3" x-cloak @click="nextStep()" class="inline-flex min-w-36 items-center justify-center rounded-full bg-[#ffcb43] px-6 py-2.5 text-sm font-bold text-[#2c1f68] transition hover:bg-[#ffd566]">
                            Continue
                        </button>

                        <button type="submit" x-show="step === 3" x-cloak class="inline-flex min-w-44 items-center justify-center rounded-full bg-[#ffcb43] px-6 py-2.5 text-sm font-bold text-[#2c1f68] transition hover:bg-[#ffd566]">
                            Create account
                        </button>

                        <a href="{{ route('login') }}" class="inline-flex min-w-28 items-center justify-center rounded-full border border-white/60 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                            Sign in
                        </a>
                    </div>
                </div>
            </form>

            <p class="mt-5 text-sm text-white/75">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-[#ffcb43] hover:text-[#ffe092]">Sign in</a>
            </p>
        </div>
    </div>
</section>

<script>
function registrationWizard() {
    return {
        step: @json(
            old('crop_types_text') ||
            old('business_name') ||
            old('specialty') ? 3 : (old('roles') ? 2 : 1)
        ),
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
