<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <nav class="navbar navbar-expand-md">
    <div class="container-fluid">
      <div class="row align-items-center text-center w-100">
        <div class="col-md-2 col-6 justify-content-center justify-content-sm-start p-2">
          <RouterLink :to="{ name: 'home' }">
            <img src="../assets/Singapore_Post_Logo.png" alt="Singapore Post Logo"/>
          </RouterLink>
        </div>
        <div class="col py-2 text-end">
          <button
            class="navbar-toggler my-2"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarCollapseContent"
            aria-controls="navbarCollapseContent"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse justify-content-end" id="navbarCollapseContent">
            <ul class="navbar-nav align-items-end align-items-md-center">
              <li class="nav-item text-end">
                <RouterLink :to="{ name: 'home' }">Home</RouterLink>
              </li>
              <li class="nav-item text-end">
                <RouterLink :to="{ name: 'shipment' }">Create Shipment</RouterLink>
              </li>
              <li class="nav-item text-end">
                <RouterLink :to="{ name: 'cart' }">Shipment Cart</RouterLink>
              </li>
              <li class="nav-item text-end">
                <RouterLink :to="{ name: 'help' }">Help</RouterLink>
              </li>
              <li class="nav-item">
                <button type="button" class="btn btn-pink quicksand-semibold">
                  <RouterLink :to="{ name: 'login' }">
                    <span class="fw-bold">Sign up/Login</span>
                  </RouterLink>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
  <RouterView />
</template>

<style scoped>
nav {
  width: 100vw;
  background-color: white;
  height: fit-content;
}
li a {
  color: #ff4275;
  text-decoration: none;
  font-weight: bold;
}
li a:hover,
button:hover a {
  color: #ff0044;
}
li {
  padding: 2px 10px;
  text-align: center;
  vertical-align: middle;
}
img {
  width: 100%;
  max-width: 200px;
  vertical-align: middle;
}
button a {
  color: white;
}
.navbar-toggler-icon {
  background-image: url('../assets/fluffy_shipping_logo.png');
  height: 3em;
  width: 3em;
}
.navbar-toggler {
  padding: 0px;
  border: 0px;
}
li a {
  color: #ff4275;
  text-decoration: none;
  font-weight: bold;
}
li {
  padding: 0px 10px;
}
</style>
