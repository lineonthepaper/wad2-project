<template>
  <div class="container-fluid">
    <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Your shipment has been registered!</h1>
        </div>
      </div>
      <hr />
    </header>

    <div class="row text-dark-slate-blue">
      <div class="col">
        <h2 class="text-center">What's next?</h2>
      </div>
    </div>
    <div
      class="row justify-content-center text-dark-slate-blue"
      v-for="(section, index) in sections"
      :key="index"
    >
      <div class="col-auto">
        <h3>{{ index + 1 }}. {{ section.title }}</h3>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  data() {
    const sections = ref([
      { title: 'Print your shipping label(s)' },
      { title: 'Drop off your mail' },
      { title: 'Sit back and relax!' },
    ])
    return {
      sections,
    }
  },
}
</script>
