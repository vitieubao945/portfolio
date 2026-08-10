<script setup>
import { ref, watch } from 'vue';
import { themeConfig } from '../../config';

const query = ref('');
const results = ref([]);
const loading = ref(false);
const error = ref(null);

let debounceTimer;

async function search(term) {
  if (!term) {
    results.value = [];
    error.value = null;
    return;
  }

  const { restUrl, nonce } = themeConfig();

  loading.value = true;
  error.value = null;

  try {
    const url = new URL(`${restUrl}wp/v2/posts`);
    url.searchParams.set('search', term);
    url.searchParams.set('per_page', '5');

    const response = await fetch(url, {
      headers: { 'X-WP-Nonce': nonce },
    });

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    results.value = await response.json();
  } catch (e) {
    error.value = e.message;
    results.value = [];
  } finally {
    loading.value = false;
  }
}

watch(query, (term) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => search(term.trim()), 300);
});
</script>

<template>
  <div class="rounded-lg border border-gray-200 p-4">
    <label for="wp-search" class="mb-1 block text-sm font-medium">
      Search posts (WP REST API)
    </label>
    <input
      id="wp-search"
      v-model="query"
      type="search"
      placeholder="Type to search…"
      class="w-full rounded border border-gray-300 px-3 py-1.5"
    >

    <p v-if="loading" class="mt-2 text-sm text-gray-500">Loading…</p>
    <p v-else-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>

    <ul v-else-if="results.length" class="mt-2 space-y-1">
      <li v-for="post in results" :key="post.id">
        <a :href="post.link" class="text-blue-600 underline" v-html="post.title.rendered" />
      </li>
    </ul>

    <p v-else-if="query" class="mt-2 text-sm text-gray-500">No results.</p>
  </div>
</template>
