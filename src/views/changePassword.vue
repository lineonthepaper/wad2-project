<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref(null)
const displayName = ref('')
const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const newEmail = ref('')
const confirmEmail = ref('')
const isLoading = ref(false)
const displayNameLoading = ref(false)
const passwordLoading = ref(false)
const emailLoading = ref(false)

// Confirmation modal refs
const showConfirmationModal = ref(false)
const pendingNewEmail = ref('')
const confirmationCode = ref('')
const pendingAccountId = ref(null)

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
    newEmail.value = currentUser.value.email || ''
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

// Confirmation Modal Functions
const openConfirmationModal = (email, accountId) => {
  pendingNewEmail.value = email
  pendingAccountId.value = accountId
  showConfirmationModal.value = true
  confirmationCode.value = ''
}

const closeConfirmationModal = () => {
  showConfirmationModal.value = false
  pendingNewEmail.value = ''
  pendingAccountId.value = null
  confirmationCode.value = ''
}

const confirmEmailChange = async () => {
  try {
    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'confirmEmailChange',
        accountId: pendingAccountId.value,
        newEmail: pendingNewEmail.value,
        confirmationCode: confirmationCode.value
      })
    })

    const result = await response.json()

    if (response.ok) {
      alert('Email updated successfully!')
      // Update current user email in session storage
      currentUser.value.email = pendingNewEmail.value
      sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
      newEmail.value = pendingNewEmail.value
      confirmEmail.value = ''
      closeConfirmationModal()
    } else {
      alert(result.message || 'Failed to confirm email change')
    }
  } catch (error) {
    console.error('Error confirming email change:', error)
    alert('Error confirming email change')
  }
}

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
  if (newEmail.value !== confirmEmail.value) {
    alert('Email addresses do not match.')
    return
  }

  if (!newEmail.value.trim() || !confirmEmail.value.trim()) {
    alert('Please fill in all fields.')
    return
  }

  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(newEmail.value)) {
    alert('Please enter a valid email address.')
    return
  }

  if (newEmail.value === currentUser.value.email) {
    alert('New email is the same as current email.')
    return
  }

  // Get accountId
  const accountId = currentUser.value.accountId || currentUser.value.account_id || currentUser.value.id

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
        method: 'changeEmail',
        accountId: accountId,
        newEmail: newEmail.value.trim(),
        currentEmail: currentUser.value.email
      })
    })

    const result = await response.json()
    console.log('Change email response:', result)

    if (response.ok) {
      if (result.requiresConfirmation) {
        // Show confirmation modal with the code (for demo) or just inform user
        alert('Confirmation email sent! Please check your new email address for the confirmation code.')
        // For demo purposes, we'll show the modal with the code
        openConfirmationModal(newEmail.value.trim(), accountId)
      } else {
        // Direct update (no confirmation required)
        currentUser.value.email = newEmail.value.trim()
        sessionStorage.setItem('currentUser', JSON.stringify(currentUser.value))
        alert('Email updated successfully!')
        confirmEmail.value = ''
      }
    } else {
      alert(result.message || 'Failed to change email')
    }
  } catch (error) {
    console.error('Error changing email:', error)
    alert('Error changing email')
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
        <h5 class="fw-bold text-pink">Change Email Address</h5>
        <div class="form-group mb-2">
          <input
            v-model="newEmail"
            type="email"
            class="form-control"
            placeholder="New Email Address"
            :disabled="emailLoading"
          />
        </div>
        <div class="form-group mb-3">
          <input
            v-model="confirmEmail"
            type="email"
            class="form-control"
            placeholder="Confirm New Email Address"
            :disabled="emailLoading"
          />
        </div>
        <button
          class="btn btn-pink"
          @click="changeEmail"
          :disabled="emailLoading || !newEmail || !confirmEmail"
        >
          <span v-if="emailLoading" class="spinner-border spinner-border-sm me-2"></span>
          Change Email
        </button>
        <small class="form-text text-muted d-block mt-2">
          A confirmation email will be sent to your new email address.
        </small>
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

    <!-- Confirmation Modal -->
    <div v-if="showConfirmationModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Email Change</h5>
            <button type="button" class="btn-close" @click="closeConfirmationModal"></button>
          </div>
          <div class="modal-body">
            <p>We've sent a confirmation code to <strong>{{ pendingNewEmail }}</strong></p>
            <div class="form-group">
              <label for="confirmationCode" class="form-label">Confirmation Code</label>
              <input
                v-model="confirmationCode"
                type="text"
                class="form-control"
                placeholder="Enter 6-digit code"
                maxlength="6"
                @keyup.enter="confirmEmailChange"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeConfirmationModal">Cancel</button>
            <button
              type="button"
              class="btn btn-pink"
              @click="confirmEmailChange"
              :disabled="!confirmationCode || confirmationCode.length !== 6"
            >
              Confirm Change
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
.account-settings {
  max-width: 600px;
}

/* Modal styles */
.modal-backdrop {
  opacity: 0.5;
}
</style>
