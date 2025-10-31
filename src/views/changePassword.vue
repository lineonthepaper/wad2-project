<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref(null)
const displayName = ref('')
const email = ref('')
const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const displayNameLoading = ref(false)
const emailLoading = ref(false)
const passwordLoading = ref(false)
const showDeleteModal = ref(false)
const deletePassword = ref('')
const deleteLoading = ref(false)

// Check authentication and load current user
const checkAuth = () => {
  const stored = sessionStorage.getItem('currentUser')
  if (!stored) {
    router.push('/login')
    return false
  }

  try {
    currentUser.value = JSON.parse(stored)
    console.log('Current user from sessionStorage:', currentUser.value)
    displayName.value = currentUser.value.displayName || currentUser.value.display_name || ''
    email.value = currentUser.value.email || ''
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

  if (displayName.value.trim() === (currentUser.value.displayName || currentUser.value.display_name)) {
    alert('Display name is the same as current name.')
    return
  }

  const accountId = currentUser.value.accountId || currentUser.value.account_id || currentUser.value.id

  console.log('Attempting to update display name with accountId:', accountId)

  if (!accountId) {
    alert('Cannot find account ID. Please log in again.')
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
        accountId: accountId,
        newDisplayName: displayName.value.trim()
      })
    })

    const result = await response.json()
    console.log('Update display name response:', result)

    if (response.ok) {
      // Update both possible property names
      currentUser.value.displayName = displayName.value.trim()
      currentUser.value.display_name = displayName.value.trim()
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

// Handle email change
const changeEmail = async () => {
  if (!email.value.trim()) {
    alert('Email cannot be empty.')
    return
  }

  if (email.value.trim() === currentUser.value.email) {
    alert('Email is the same as current email.')
    return
  }

  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.value.trim())) {
    alert('Please enter a valid email address.')
    return
  }

  const accountId = currentUser.value.accountId || currentUser.value.account_id || currentUser.value.id

  console.log('Attempting to update email with accountId:', accountId)

  if (!accountId) {
    alert('Cannot find account ID. Please log in again.')
    return
  }

  emailLoading.value = true
  try {
    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'updateEmail',
        accountId: accountId,
        newEmail: email.value.trim()
      })
    })

    const result = await response.json()
    console.log('Update email response:', result)

    if (response.ok) {
      // Update email in current user data
      currentUser.value.email = email.value.trim()
      sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
      alert('Email updated successfully!')
    } else {
      alert(result.message || 'Failed to update email')
    }
  } catch (error) {
    console.error('Error updating email:', error)
    alert('Error updating email')
  } finally {
    emailLoading.value = false
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

  const accountId = currentUser.value.accountId || currentUser.value.account_id || currentUser.value.id

  console.log('Attempting to change password with accountId:', accountId)

  if (!accountId) {
    alert('Cannot find account ID. Please log in again.')
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
    console.log('Verify password response:', verifyResult)

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
        accountId: accountId,
        newPassword: newPassword.value
      })
    })

    const updateResult = await updateResponse.json()
    console.log('Update password response:', updateResult)

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

// Handle account deletion
const deleteAccount = async () => {
  if (!deletePassword.value) {
    alert('Please enter your password to confirm account deletion.')
    return
  }

  const accountId = currentUser.value.accountId || currentUser.value.account_id || currentUser.value.id

  console.log('Attempting to delete account with accountId:', accountId)

  if (!accountId) {
    alert('Cannot find account ID. Please log in again.')
    return
  }

  deleteLoading.value = true
  try {
    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'deleteAccount',
        accountId: accountId,
        password: deletePassword.value
      })
    })

    const result = await response.json()
    console.log('Delete account response:', result)

    if (response.ok) {
      alert('Account deleted successfully!')
      // Clear session and redirect to home/login
      sessionStorage.removeItem('currentUser')
      currentUser.value = null
      router.push('/')
    } else {
      alert(result.message || 'Failed to delete account')
    }
  } catch (error) {
    console.error('Error deleting account:', error)
    alert('Error deleting account')
  } finally {
    deleteLoading.value = false
    showDeleteModal.value = false
    deletePassword.value = ''
  }
}

// Open delete confirmation modal
const confirmDelete = () => {
  showDeleteModal.value = true
}

// Close delete modal
const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletePassword.value = ''
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
        <span class="me-3">Welcome, {{ currentUser.displayName || currentUser.display_name || currentUser.email }}!</span>
        <button class="btn btn-outline-pink btn-sm" @click="logout">
          Logout
        </button>
      </div>
    </div>

    <div class="card shadow-lg p-4 rounded-4 border-0">
      <!-- Change Display Name -->
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

      <!-- Change Email -->
      <div class="mb-4">
        <h5 class="fw-bold text-pink">Change Email</h5>
        <div class="input-group">
          <input
            v-model="email"
            type="email"
            class="form-control"
            placeholder="Enter new email"
            :disabled="emailLoading"
          />
          <button
            class="btn btn-pink"
            @click="changeEmail"
            :disabled="emailLoading || !email.trim()"
          >
            <span v-if="emailLoading" class="spinner-border spinner-border-sm me-2"></span>
            Update
          </button>
        </div>
      </div>

      <hr />

      <!-- Change Password -->
      <div class="mb-4">
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

      <hr />

      <!-- Delete Account -->
      <div class="mt-4">
        <h5 class="fw-bold text-danger">Danger Zone</h5>
        <p class="text-muted small mb-3">
          Once you delete your account, there is no going back. Please be certain.
        </p>
        <button
          class="btn btn-outline-danger"
          @click="confirmDelete"
          :disabled="isLoading"
        >
          Delete Account
        </button>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Delete Account</h5>
            <button
              type="button"
              class="btn-close"
              @click="closeDeleteModal"
              :disabled="deleteLoading"
            ></button>
          </div>
          <div class="modal-body">
            <p class="text-danger mb-3">
              <strong>Warning:</strong> This action is permanent and cannot be undone. All your data will be lost.
            </p>
            <p>Please enter your password to confirm account deletion:</p>
            <input
              v-model="deletePassword"
              type="password"
              class="form-control"
              placeholder="Enter your password"
              :disabled="deleteLoading"
              @keyup.enter="deleteAccount"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeDeleteModal"
              :disabled="deleteLoading"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-danger"
              @click="deleteAccount"
              :disabled="deleteLoading || !deletePassword"
            >
              <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-2"></span>
              Delete Account Permanently
            </button>
          </div>
        </div>
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
.text-danger {
  color: #dc3545;
}
.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}
.btn-danger:hover {
  background-color: #c82333;
  border-color: #bd2130;
}
.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}
.btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
}
.account-settings {
  max-width: 600px;
}
.modal-content {
  border: none;
  border-radius: 12px;
}
</style>
