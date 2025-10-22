<template>
  <div class="container-fluid">
    <div class="row py-2">
      <div class="col">labels!!!</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    const labels = useLabelsStore()
    return {
      labels,
    }
  },
}
</script>

<script setup>
import { useLabelsStore } from '@/stores/labels'

import { ref } from 'vue'
</script>
