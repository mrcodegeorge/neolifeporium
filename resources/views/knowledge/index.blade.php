@extends('layouts.app', ['title' => 'Knowledge Hub | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">Knowledge hub</p>
    <h1 class="mt-2 text-3xl font-black text-palm">Localized crop intelligence and practical field guidance</h1>

    <form method="GET" action="{{ route('knowledge.index') }}" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 sm:grid-cols-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search articles, guides, videos" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
        <select name="category" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-palm px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
    </form>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @forelse($articles as $article)
            <a href="{{ route('knowledge.show', $article->slug) }}" class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1 hover:shadow-xl">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ optional($article->published_at)->format('M d, Y') }}</p>
                <h2 class="mt-3 text-2xl font-bold">{{ $article->title }}</h2>
                <p class="mt-3 text-sm text-slate-600">{{ $article->excerpt }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($article->tags as $tag)
                        <span class="rounded-full bg-leaf/10 px-2 py-1 text-xs font-semibold text-leaf">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </a>
        @empty
            <div class="rounded-3xl bg-white p-8 text-slate-500 shadow-lg shadow-black/5 lg:col-span-2">No articles found for this filter.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
</section>
@endsection
