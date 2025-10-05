<!--
TODO:
- image on the right
- carousel
- the nav at the bottom
-->
<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="container-fluid">
    <hr />

    <div class="row bg-light-pink justify-content-center header">
      <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
        <h1 class="jua text-hot-pink">Fluffy Shipping</h1>
        <p class="text-dark-pink quicksand-semibold">
          Why fret when sending parcels? <br />
          Singapore Post to your rescue!
        </p>
        <button type="button" class="btn btn-pink quicksand-semibold fw-bold">Ship</button>
      </div>
    </div>

    <hr />

    <div class="row justify-content-center py-2">
      <h2 class="text-dark-pink text-center fw-bold">
        What is <span class="text-hot-pink jua">Fluffy Shipping</span>?
      </h2>
      <p class="text-center text-dark-slate-blue">
        A comprehensive distribution solution and e-Business tool to manage your shipment needs
      </p>
    </div>

    <div class="row justify-content-center py-2">
      <h2 class="text-center text-dark-slate-blue fw-bold">
        Manage your shipment with SingPost today!
      </h2>
      <p class="text-center text-slate-blue">
        Click below to learn more about our Fluffy Shipping services
      </p>
    </div>

    <div class="row justify-content-center text-center py-2">
      <div class="col-md-3"><RouterLink :to="{ name: 'services' }">Our services</RouterLink></div>
      <div class="col-md-3"><RouterLink :to="{ name: 'faq' }">FAQ</RouterLink></div>
      <div class="col-md-3">
        <RouterLink :to="{ name: 'shipment' }">Create shipment</RouterLink>
      </div>
    </div>

    <div class="row justify-content-center text-center py-2">
      <h2 class="text-dark-slate-blue fw-bold">Unsure on how to create a shipment?</h2>
      <div class="col-md-2 col-sm-4 col-8">
        <button type="button" class="btn btn-pink quicksand-semibold">Get a demo →</button>
      </div>
    </div>
  </div>
  <RouterView />
</template>

<style scoped></style>
