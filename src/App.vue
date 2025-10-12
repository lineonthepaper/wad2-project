<!--
TODO:
- footer
- fix HeadLoader to maybe grab the name for title
-->

<script setup>
import HeadLoader from './components/HeadLoader.vue'
import NavBar from './components/NavBar.vue'
import FooterBar from './components/FooterBar.vue'
</script>

<template>
  <HeadLoader />

  <div class="flex-column justify-content-between d-flex flex-grow-1 flex-shrink-0 min-vh-100">
    <header>
      <NavBar />
    </header>
    <footer><FooterBar /></footer>
  </div>
</template>

<style scoped></style>
