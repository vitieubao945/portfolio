@extends('layouts.app')

@section('content')
  <section class="vue-demo mb-8 space-y-4">
    <h1 class="text-2xl font-bold">{{ get_bloginfo('name') }}</h1>
    <p>{!! __('This page is rendered server-side by Blade. The two boxes below are independent Vue&nbsp;3 islands, mounted client-side by Vite — the rest of the page stays plain server-rendered HTML.', 'sage') !!}</p>

    <div
      data-vue-component="example"
      data-props="{{ wp_json_encode(['message' => __('Hello from Blade + Vue!', 'sage'), 'count' => 0], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
    ></div>

    <div data-vue-component="search"></div>
  </section>

  @include('partials.page-header')

  @if (! have_posts())
    <x-alert type="warning">
      {!! __('Sorry, no results were found.', 'sage') !!}
    </x-alert>

    {!! get_search_form(false) !!}
  @endif

  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
  @endwhile

  {!! get_the_posts_navigation() !!}
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
