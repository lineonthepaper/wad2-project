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
    const name = user.display_name || user.displayName
    //  ||  user.email
    welcomeMessage.value = `Welcome, ${name}!`
    isLoggedIn.value = true
  }

  // Listen for login status changes to update welcome message
  window.addEventListener('loginStatusChanged', () => {
    const userData = sessionStorage.getItem('currentUser')
    if (userData) {
      const user = JSON.parse(userData)
      const name = user.displayName || user.email
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

    <div class="row justify-content-center text-center py-2">
      <div class="col-md-3 py-2 col-sm-8" v-for="linkCard in linkCards" :key="linkCard.title">
        <div class="card h-100">
          <RouterLink :to="{ name: linkCard.link }">
            <img :src="linkCard.imgUrl" class="service-icon mx-auto" :alt="linkCard.alt" />
            <h5 class="card-title px-2">{{ linkCard.title }}</h5>
            <p class="px-3">{{ linkCard.subtitle }}</p>
          </RouterLink>
        </div>
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

<script>
export default {
  data() {
    const linkCards = [
      {
        link: 'services',
        title: 'Our Services',
        subtitle: 'No matter your destination, Fluffy Shipping can ship it!',
        alt: 'Hand holding up two gears.',
        imgUrl: '/home/services.png',
      },
      {
        link: 'faq',
        title: 'FAQ',
        subtitle: 'Any questions? Check out these frequently asked questions!',
        alt: 'Two overlapping speech bubbles. The top bubble has a question mark in it.',
        imgUrl: '/home/faq.png',
      },
      {
        link: 'shipment',
        title: 'Create Shipment',
        subtitle: "Ready to ship with Fluffy Shipping? We're ready when you are!",
        alt: 'A moving delivery van. It has a check mark on it.',
        imgUrl: '/home/shipment.png',
      },
    ]
    return {
      linkCards,
    }
  },
}
</script>
