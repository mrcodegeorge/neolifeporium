@extends('layouts.app', [
    'title' => 'Neolifeporium | Smarter Farming Starts Here',
    'metaDescription' => 'Science-backed agritech innovation platform for Ghanaian farmers. Buy better tools, get expert insight, and increase yield with Neolifeporium.',
    'showDefaultNav' => false,
    'pageShellClass' => 'min-h-screen bg-white',
])

@section('content')
    @include('pages.home._nav')
    @include('pages.home._hero')
    @include('pages.home._problem')
    @include('pages.home._impact')
    @include('pages.home._solution')
    @include('pages.home._how-it-works')
    @include('pages.home._story-sequence')
    @include('pages.home._innovations')
    @include('pages.home._proof')
    @include('pages.home._impact-map')
    @include('pages.home._marketplace-preview')
    @include('pages.home._advisory-cta')
    @include('pages.home._final-cta')
    @include('pages.home._live-chat')
@endsection
