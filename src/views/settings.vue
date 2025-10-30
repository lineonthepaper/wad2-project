<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref(null)
const displayName = ref('')
const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const profilePicture = ref(null)
const profilePreview = ref('')
const isLoading = ref(false)
const displayNameLoading = ref(false)
const passwordLoading = ref(false)

// Check authentication and load current user
const checkAuth = () => {
  const stored = sessionStorage.getItem('currentUser')
  if (!stored) {
    router.push('/login')
    return false
  }
  
  try {
    currentUser.value = JSON.parse(stored)
    displayName.value = currentUser.value.display_name || currentUser.value.displayName || ''
    profilePreview.value = currentUser.value.profilePicture || '/default-avatar.png'
    return true
  } catch (e) {
    console.error('Error parsing user data:', e)
    router.push('/login')
    return false
  }
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

// Handle display name change
const changeDisplayName = async () => {
  if (!displayName.value.trim()) {
    alert('Display name cannot be empty.')
    return
  }

  if (displayName.value.trim() === (currentUser.value.display_name || currentUser.value.displayName)) {
    alert('Display name is the same as current name.')
    return
  }

  displayNameLoading.value = true
  try {
    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'updateDisplayName',
        accountId: currentUser.value.account_id || currentUser.value.id,
        newDisplayName: displayName.value.trim()
      })
    })

    const result = await response.json()
    
    if (response.ok) {
      // Update both possible property names
      currentUser.value.display_name = displayName.value.trim()
      currentUser.value.displayName = displayName.value.trim()
      sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
      alert('Display name updated successfully!')
    } else {
      alert(result.message || 'Failed to update display name')
    }
  } catch (error) {
    console.error('Error updating display name:', error)
    alert('Error updating display name')
  } finally {
    displayNameLoading.value = false
  }
}

// Handle password change
const changePassword = async () => {
  if (newPassword.value !== confirmPassword.value) {
    alert('Passwords do not match.')
    return
  }
  
  if (!oldPassword.value || !newPassword.value || !confirmPassword.value) {
    alert('Please fill in all fields.')
    return
  }

  if (newPassword.value.length < 6) {
    alert('Password must be at least 6 characters long.')
    return
  }

  passwordLoading.value = true
  try {
    // Step 1: Verify old password
    const verifyResponse = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'verifyPassword',
        email: currentUser.value.email,
        password: oldPassword.value
      })
    })

    const verifyResult = await verifyResponse.json()
    
    if (!verifyResponse.ok || !verifyResult.valid) {
      alert('Current password is incorrect.')
      return
    }

    // Step 2: Update password
    const updateResponse = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'updatePassword',
        accountId: currentUser.value.account_id || currentUser.value.id,
        newPassword: newPassword.value
      })
    })

    const updateResult = await updateResponse.json()
    
    if (updateResponse.ok) {
      alert('Password changed successfully!')
      oldPassword.value = ''
      newPassword.value = ''
      confirmPassword.value = ''
    } else {
      alert(updateResult.message || 'Failed to change password')
    }
  } catch (error) {
    console.error('Error changing password:', error)
    alert('Error changing password')
  } finally {
    passwordLoading.value = false
  }
}

// Handle profile picture upload
const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file.')
    return
  }

  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Please select an image smaller than 5MB.')
    return
  }

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
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-pink fw-bold mb-0">Account Settings</h2>
      <div class="d-flex align-items-center">
        <span class="me-3">Welcome, {{ currentUser.display_name || currentUser.displayName || currentUser.email }}!</span>
        <button class="btn btn-outline-pink btn-sm" @click="logout">
          Logout
        </button>
      </div>
    </div>

    <div class="card shadow-lg p-4 rounded-4 border-0">
      <div class="text-center mb-4">
        <img
          :src="profilePreview"
          alt="Profile Picture"
          class="rounded-circle mb-3 border"
          style="width: 150px; height: 150px; object-fit: cover;"
        />
        <div>
          <input 
            type="file" 
            @change="handleFileUpload" 
            accept="image/*" 
            class="form-control"
            style="max-width: 250px; margin: 0 auto;"
          />
        </div>
      </div>

      <div class="mb-4">
        <h5 class="fw-bold text-pink">Change Display Name</h5>
        <div class="input-group">
          <input
            v-model="displayName"
            type="text"
            class="form-control"
            placeholder="Enter new display name"
            :disabled="displayNameLoading"
          />
          <button 
            class="btn btn-pink" 
            @click="changeDisplayName"
            :disabled="displayNameLoading || !displayName.trim()"
          >
            <span v-if="displayNameLoading" class="spinner-border spinner-border-sm me-2"></span>
            Update
          </button>
        </div>
      </div>

      <hr />

      <div>
        <h5 class="fw-bold text-pink">Change Password</h5>
        <div class="form-group mb-2">
          <input
            v-model="oldPassword"
            type="password"
            class="form-control"
            placeholder="Current Password"
            :disabled="passwordLoading"
          />
        </div>
        <div class="form-group mb-2">
          <input
            v-model="newPassword"
            type="password"
            class="form-control"
            placeholder="New Password"
            :disabled="passwordLoading"
          />
        </div>
        <div class="form-group mb-3">
          <input
            v-model="confirmPassword"
            type="password"
            class="form-control"
            placeholder="Confirm New Password"
            :disabled="passwordLoading"
          />
        </div>
        <button 
          class="btn btn-pink" 
          @click="changePassword"
          :disabled="passwordLoading || !oldPassword || !newPassword || !confirmPassword"
        >
          <span v-if="passwordLoading" class="spinner-border spinner-border-sm me-2"></span>
          Change Password
        </button>
      </div>
    </div>
  </div>

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
.btn-pink:disabled {
  background-color: #cccccc;
  border-color: #cccccc;
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
</style>