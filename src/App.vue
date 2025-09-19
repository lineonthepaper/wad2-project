<!--
TODO:
- footer
- fix HeadLoader to maybe grab the name for title
-->

<script setup>
import HeadLoader from './components/HeadLoader.vue'
import NavBar from './components/NavBar.vue'

import router from './router/index.js'

var path = router.currentRoute._value.path
</script>

<template>
  <!-- to fix (not hardcode) -->
  <HeadLoader title="Home" v-if="path === '/'" />
  <header>
    <NavBar />
  </header>

  <footer></footer>
</template>

<style scoped></style>
