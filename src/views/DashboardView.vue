<template>
  <div class="dashboard">
    <h1>Welcome {{ usernameDisplay }}</h1>
    <p>This is your dashboard.</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const username = ref('')

onMounted(() => {
  username.value = localStorage.getItem('displayName') || ''
})

const usernameDisplay = computed(() => username.value ? `"${username.value}"` : 'User')
</script>

<style scoped>
.dashboard {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1.5rem;
  background: #fff;
  border-radius: 8px;
}
</style>
