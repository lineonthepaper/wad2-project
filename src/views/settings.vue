<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref(null)
const username = ref('')
const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const profilePicture = ref(null)
const profilePreview = ref('')

// Check authentication and load current user
const checkAuth = () => {
  const stored = sessionStorage.getItem('currentUser')
  if (!stored) {
    // Redirect to login if not authenticated
    router.push('/login')
    return false
  }
  
  currentUser.value = JSON.parse(stored)
  username.value = currentUser.value.display_name || ''
  profilePreview.value = currentUser.value.profilePicture || ''
  return true
}

// Load current user from sessionStorage with auth check
onMounted(() => {
  checkAuth()
})

// Watch for changes in currentUser and redirect if becomes null
watch(currentUser, (newVal) => {
  if (!newVal) {
    router.push('/login')
  }
})

// Handle username change
const changeUsername = () => {
  if (!username.value.trim()) {
    alert('Username cannot be empty.')
    return
  }
  currentUser.value.display_name = username.value
  sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
  alert('Username updated successfully!')
}

// Handle password change
const changePassword = () => {
  if (newPassword.value !== confirmPassword.value) {
    alert('Passwords do not match.')
    return
  }
  if (!oldPassword.value || !newPassword.value) {
    alert('Please fill in all fields.')
    return
  }
  // Mock update - in real app, this would send to backend
  alert('Password changed successfully!')
  oldPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
}

// Handle profile picture upload
const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (event) => {
    profilePreview.value = event.target.result
    currentUser.value.profilePicture = event.target.result
    sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
  }
  reader.readAsDataURL(file)
}

// Logout function
const logout = () => {
  sessionStorage.removeItem('currentUser')
  currentUser.value = null
  router.push('/login')
}
</script>

<template>
  <div class="account-settings container my-5" v-if="currentUser">
    <!-- Header with user info and logout -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-pink fw-bold mb-0">Account Settings</h2>
      <div class="d-flex align-items-center">
        <span class="me-3">Welcome, {{ currentUser.display_name || currentUser.username }}!</span>
        <button class="btn btn-outline-pink btn-sm" @click="logout">
          Logout
        </button>
      </div>
    </div>

    <div class="card shadow-lg p-4 rounded-4 border-0">
      <!-- Profile Picture -->
      <div class="text-center mb-4">
        <img
          :src="profilePreview || '/default-avatar.png'"
          alt="Profile Picture"
          class="rounded-circle mb-3 border"
          style="width: 150px; height: 150px; object-fit: cover;"
        />
        <div>
          <input type="file" @change="handleFileUpload" accept="image/*" />
        </div>
      </div>

      <!-- Change Username -->
      <div class="mb-4">
        <h5 class="fw-bold text-pink">Change Username</h5>
        <div class="input-group">
          <input
            v-model="username"
            type="text"
            class="form-control"
            placeholder="Enter new username"
          />
          <button class="btn btn-pink" @click="changeUsername">Update</button>
        </div>
      </div>

      <hr />

      <!-- Change Password -->
      <div>
        <h5 class="fw-bold text-pink">Change Password</h5>
        <div class="form-group mb-2">
          <input
            v-model="oldPassword"
            type="password"
            class="form-control"
            placeholder="Old Password"
          />
        </div>
        <div class="form-group mb-2">
          <input
            v-model="newPassword"
            type="password"
            class="form-control"
            placeholder="New Password"
          />
        </div>
        <div class="form-group mb-3">
          <input
            v-model="confirmPassword"
            type="password"
            class="form-control"
            placeholder="Confirm New Password"
          />
        </div>
        <button class="btn btn-pink" @click="changePassword">Change Password</button>
      </div>
    </div>
  </div>

  <!-- Loading/Unauthorized state -->
  <div v-else class="container my-5">
    <div class="text-center">
      <div class="spinner-border text-pink" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Redirecting to login...</p>
    </div>
  </div>
</template>

<style scoped>
.text-pink {
  color: #ff4275;
}
.btn-pink {
  background-color: #ff4275;
  color: white;
  border: none;
}
.btn-pink:hover {
  background-color: #ff0044;
}
.btn-outline-pink {
  color: #ff4275;
  border-color: #ff4275;
}
.btn-outline-pink:hover {
  background-color: #ff4275;
  color: white;
}
.account-settings {
  max-width: 600px;
}
input[type="file"] {
  display: inline-block;
  margin-top: 10px;
}
</style>