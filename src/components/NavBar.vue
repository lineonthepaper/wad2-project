<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { ref, onMounted, onUnmounted } from 'vue'

const isLoggedIn = ref(false)
const currentUser = ref(null)

// Function to check login status
const checkLoginStatus = () => {
  const userData = sessionStorage.getItem('currentUser')
  if (userData) {
    currentUser.value = JSON.parse(userData)
    isLoggedIn.value = true
  } else {
    currentUser.value = null
    isLoggedIn.value = false
  }
}

// Function to handle logout
const handleLogout = () => {
  sessionStorage.removeItem('currentUser')
  isLoggedIn.value = false
  currentUser.value = null

  // Dispatch event to notify other components
  window.dispatchEvent(new Event('loginStatusChanged'))

  // Redirect to home page
  window.location.href = '/'
}

// Listen for login status changes
const handleLoginStatusChange = () => {
  checkLoginStatus()
}

onMounted(() => {
  checkLoginStatus()
  window.addEventListener('loginStatusChanged', handleLoginStatusChange)
})

onUnmounted(() => {
  window.removeEventListener('loginStatusChanged', handleLoginStatusChange)
})
</script>

<template>
  <div class="z-3">
    <nav class="navbar navbar-expand-md">
      <div class="container-fluid">
        <div class="row align-items-center text-center w-100">
          <div class="col-md-2 col-6 justify-content-center justify-content-sm-start p-2">
            <RouterLink :to="{ name: 'home' }">
              <img src="../assets/wad2_project_logo.png" alt="WAD2 Project Logo" />
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
                  <RouterLink :to="{ name: 'cart' }">Cart</RouterLink>
                </li>
                <li class="nav-item text-end">
                  <RouterLink :to="{ name: 'help' }">Terms & Conditions</RouterLink>
                </li>
                   <li class="nav-item text-end">
                  <RouterLink :to="{ name: 'faq' }">FAQ</RouterLink>
                </li>
                <li class="nav-item" v-if="!isLoggedIn">
                  <button type="button" class="btn btn-pink quicksand-semibold">
                    <RouterLink :to="{ name: 'login' }">
                      <span class="fw-bold">Sign up/Login</span>
                    </RouterLink>
                  </button>
                </li>
                <li class="nav-item dropdown text-end" v-else>
                  <button
                    type="button"
                    class="btn btn-outline-pink quicksand-semibold dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span class="fw-bold">
                      {{ currentUser?.displayName || currentUser?.email }}
                    </span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <button class="dropdown-item" @click="$router.push('/settings')">
                        Quick Action
                      </button>
                       <button class="dropdown-item" @click="$router.push('/changePassword')">
                        Update Account
                      </button>
                       <button class="dropdown-item" @click="$router.push('/CDB')">
                        Customer Dashboard
                      </button>
                    </li>
                    <li>
                      <button class="dropdown-item" @click="handleLogout">Logout</button>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <RouterView />
  </div>
</template>

<style scoped>
nav {
  width: 100vw;
  background-color: rgba(255, 255, 255, 0.75);
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
  background-image: url('/fluffy_shipping_logo.png');
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

/* Style for logout button */
.btn-outline-pink {
  color: #ff4275;
  border-color: #ff4275;
  background-color: white;
}

.btn-outline-pink:hover {
  background-color: #ff4275;
  color: white;
}

.dropdown-item {
  color: #ff4275;
}

.dropdown-item:hover {
  background-color: #ff4275;
  color: white;
}
</style>
