<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="container-fluid">
    <header>
      <hr />

      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Fluffy Shipping</h1>
          <p class="text-dark-pink quicksand-semibold">
            Why fret when sending parcels? <br />
            Singapore Post to your rescue!
          </p>
          <RouterLink :to="{ name: 'shipment' }"
            ><button type="button" class="btn btn-pink quicksand-semibold fw-bold">Ship</button>
          </RouterLink>
        </div>
      </div>

      <hr />
    </header>

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
      <div class="col-md-3">
        <div class="card h-100">
          <RouterLink :to="{ name: 'services' }">
            <img
              src="../assets/home/services.png"
              class="service-icon mx-auto"
              alt="Hand holding up two gears."
            />
            <h5 class="card-title">Our services</h5>
            <p class="px-3">No matter your destination, Fluffy Shipping can ship it!</p>
          </RouterLink>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card h-100">
          <RouterLink :to="{ name: 'faq' }">
            <img
              src="../assets/home/faq.png"
              class="service-icon mx-auto"
              alt="Two overlapping speech bubbles. The top bubble has a question mark in it."
            />
            <h5 class="card-title">FAQ</h5>
            <p class="px-3">Any questions? Check these frequently asked questions out!</p>
          </RouterLink>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card h-100">
          <RouterLink :to="{ name: 'shipment' }">
            <img
              src="../assets/home/shipment.png"
              class="service-icon mx-auto"
              alt="A moving delivery van. It has a check mark on it."
            />
            <h5 class="card-title">Create Shipment</h5>
            <p class="px-3">Ready to ship with Fluffy Shipping? We're ready when you are!</p>
          </RouterLink>
        </div>
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

<style scoped>
.card a {
  text-decoration: none;
}

.card a h5 {
  color: var(--dark-slate-blue);
  font-weight: bold;
}

.card a:hover h5 {
  color: #19252a;
}

.card a p {
  color: var(--slate-blue);
}

.card a:hover p {
  color: var(--dark-slate-blue);
}

.service-icon {
  width: 50px;
  display: block;
  padding: 10px;
  box-sizing: content-box;
}
</style>
