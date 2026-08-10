<script setup>
import { ref } from 'vue';

const props = defineProps({
  message: {
    type: String,
    default: 'Hello from Vue!',
  },
  count: {
    type: Number,
    default: 0,
  },
});

const count = ref(props.count);
</script>

<template>
  <div class="rounded-lg border border-gray-200 p-4">
    <p class="mb-2">{{ message }}</p>
    <button
      type="button"
      class="rounded bg-gray-900 px-3 py-1.5 text-white"
      @click="count++"
    >
      Count: {{ count }}
    </button>
  </div>
</template>
