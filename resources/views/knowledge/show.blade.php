@extends('layouts.app', ['title' => $article->title.' | Neolifeporium', 'metaDescription' => $article->meta_description ?: $article->excerpt])

@push('head')
    <meta property="og:title" content="{{ $article->meta_title ?: $article->title }}">
    <meta property="og:description" content="{{ $article->meta_description ?: $article->excerpt }}">
    <meta property="og:type" content="article">
    @if($article->cover_image)
        <meta property="og:image" content="{{ $article->cover_image }}">
    @endif
@endpush

@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">{{ optional($article->published_at)->format('M d, Y') }}</p>
    <h1 class="mt-2 text-4xl font-black text-palm">{{ $article->title }}</h1>
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach($article->tags as $tag)
            <a href="{{ route('knowledge.category', $tag->slug) }}" class="rounded-full bg-leaf/10 px-3 py-1 text-xs font-semibold text-leaf">{{ $tag->name }}</a>
        @endforeach
    </div>
    @if($article->video_url)
        <div class="mt-6 overflow-hidden rounded-3xl bg-white p-3 shadow-lg shadow-black/5">
            <iframe src="{{ $article->video_url }}" class="h-72 w-full rounded-2xl md:h-96" loading="lazy" allowfullscreen></iframe>
        </div>
    @endif
    <div class="prose prose-lg mt-6 max-w-none rounded-3xl bg-white p-8 shadow-lg shadow-black/5 whitespace-pre-wrap">
        {{ strip_tags($article->body) }}
    </div>

    <section class="mt-8 rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
        <h2 class="text-2xl font-black text-palm">Recommended Tools</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @forelse($article->recommendedProducts as $product)
                <a href="{{ route('marketplace.show', $product->slug) }}" class="rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <p class="text-sm font-bold text-slate-900">{{ $product->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $product->category?->name }}</p>
                    <p class="mt-2 text-sm font-semibold text-palm">GHS {{ number_format($product->price, 2) }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500 sm:col-span-2">No direct product recommendations yet.</p>
            @endforelse
        </div>
    </section>
</article>
@endsection
