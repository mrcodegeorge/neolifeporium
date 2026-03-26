@extends('install.layout', ['title' => 'Requirements | Neolifeporium', 'heading' => 'System Requirements', 'progress' => 20])

@section('content')
<div class="space-y-4">
    @foreach($checks as $check)
        <div class="flex items-center justify-between rounded-2xl border px-4 py-3 {{ $check['ok'] ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
            <div>
                <p class="font-semibold">{{ $check['label'] }}</p>
                <p class="text-sm text-slate-600">{{ $check['details'] }}</p>
            </div>
            <span class="text-sm font-bold {{ $check['ok'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $check['ok'] ? 'PASS' : 'FAIL' }}</span>
        </div>
    @endforeach

    <div class="pt-4">
        @if($allPassed)
            <a href="{{ route('install.database') }}" class="inline-flex rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Continue to Database Setup</a>
        @else
            <p class="text-sm text-red-700">Fix failed requirements before proceeding.</p>
        @endif
    </div>
</div>
@endsection
