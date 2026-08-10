<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
  targetDate: {
    type: String,
    required: true,
  },
});

const now = ref(Date.now());
let timer;

onMounted(() => {
  timer = setInterval(() => {
    now.value = Date.now();
  }, 1000);
});

onUnmounted(() => clearInterval(timer));

const remaining = computed(() => {
  const diff = Math.max(0, new Date(props.targetDate).getTime() - now.value);

  return {
    done: diff <= 0,
    units: [
      ['Days', Math.floor(diff / 86400000)],
      ['Hours', Math.floor((diff % 86400000) / 3600000)],
      ['Minutes', Math.floor((diff % 3600000) / 60000)],
      ['Seconds', Math.floor((diff % 60000) / 1000)],
    ],
  };
});
</script>

<template>
  <div v-if="!remaining.done" class="flex gap-6">
    <div v-for="[label, value] in remaining.units" :key="label" class="text-center">
      <div class="text-4xl font-bold tabular-nums">{{ String(value).padStart(2, '0') }}</div>
      <div class="text-xs uppercase tracking-wide opacity-80">{{ label }}</div>
    </div>
  </div>
  <p v-else>We’re back — refresh the page.</p>
</template>
