<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ get_bloginfo('name') }} &mdash; {{ __('Under maintenance', 'sage') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="bg-gray-900 text-white">
    <div
      class="flex min-h-screen flex-col items-center justify-center gap-6 bg-black/50 px-6 text-center"
      @if ($backgroundUrl)
        style="background-image: url('{{ $backgroundUrl }}'); background-size: cover; background-position: center;"
      @endif
    >
      <h1 class="text-3xl font-bold">{{ get_bloginfo('name') }}</h1>

      <p class="max-w-md text-lg">
        {{ __('Coming Soon!!!', 'sage') }}
      </p>

      @if ($targetDate)
        <div
          data-vue-component="countdown"
          data-props="{{ wp_json_encode(['targetDate' => $targetDate], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
        ></div>
      @endif

      <div class="mt-4 text-sm">
        <p>{{ __('Need us urgently? Contact us:', 'sage') }}</p>
        <a class="underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
      </div>
    </div>
  </body>
</html>
