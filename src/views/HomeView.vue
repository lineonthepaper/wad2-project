<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { ref, onMounted } from 'vue'

const welcomeMessage = ref('')
const isLoggedIn = ref(false) // added to track login state

onMounted(() => {
  // Get user data from sessionStorage
  const userData = sessionStorage.getItem('currentUser')
  if (userData) {
    const user = JSON.parse(userData)
    // Use display_name (from database) or fall back to email
    const name = user.display_name || user.email
    welcomeMessage.value = `Welcome, ${name}!`
    isLoggedIn.value = true
  }

  // Listen for login status changes to update welcome message
  window.addEventListener('loginStatusChanged', () => {
    const userData = sessionStorage.getItem('currentUser')
    if (userData) {
      const user = JSON.parse(userData)
      const name = user.display_name || user.email
      welcomeMessage.value = `Welcome, ${name}!`
      isLoggedIn.value = true
    } else {
      welcomeMessage.value = ''
      isLoggedIn.value = false
    }
  })
})
</script>

<template>
  <div class="container-fluid">
    <header>
      <hr />

      <!-- Welcome Message - only added this line -->
      <div v-if="welcomeMessage" class="row justify-content-center">
        <div class="col-12 text-center py-2">
          <h3 class="text-hot-pink">{{ welcomeMessage }}</h3>
        </div>
      </div>

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

    <div class="row justify-content-center text-center py-2 cards-row">
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
  </div>
  <RouterView />
</template>

<style>
body {
  background: linear-gradient(135deg, #ff7eb9, #ff65a3, #f5c4d3);
  background-size: 300% 300%;
  animation: gradient-animation 15s ease infinite;
  font-family: 'Quicksand', sans-serif;
  min-height: 100vh;
}

@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>


<style scoped>
.card {
  background: linear-gradient(135deg, #fff3f8, #ffeaf2);
  border: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease; /* Added transition for smooth hover */
  border-radius: 15px;
}
.card:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 20px rgba(255, 101, 163, 0.3);
}

.btn-pink {
  background: linear-gradient(90deg, #ff7eb9, #ff65a3);
  border: none;
  color: white;
  transition: transform 0.3s ease, opacity 0.3s ease; /* Added transition for smooth hover */
  padding: 10px 30px;
  border-radius: 50px;
}

.btn-pink:hover {
  transform: scale(1.08);
  opacity: 1.0;
  background: linear-gradient(90deg, #ff65a3, #ff7eb9);
  color: white;
}

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

.service-icon:hover {
  transform: scale(1.1);
}

header.row a {
  text-decoration: none;
  color: white;
}

header .row a:hover {
  color: white;
}


</style>
