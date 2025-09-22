<!--
TODO:
- footer
- fix HeadLoader to maybe grab the name for title
-->

<script setup>
import HeadLoader from './components/HeadLoader.vue'
import NavBar from './components/NavBar.vue'
</script>

<template>
  <HeadLoader />
  <header>
    <NavBar />
  </header>

  <footer></footer>
</template>

<style scoped></style>
