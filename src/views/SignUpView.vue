<template>
    <div class="account-form">
        <h2>Sign Up</h2>

        <form @submit.prevent="handleSignup">
            Name: <br>
            <input v-model="displayName" placeholder="Display Name" required aria-label="Display Name"/>

            <br>
            <br>

            Email: <br>
            <input v-model="email" type="email" placeholder="Email" required aria-label="Email"/>

            <br>
            <br>

            Password: <br>
            <input v-model="password" type="password" placeholder="Password" required aria-label="Password"/>

            <br>
            <br>

            Confirm Password: <br>
            <input v-model="confirmPassword" type="password" placeholder="Confirm Password" required aria-label="Confirm Password"/>

            <label class="staff-label">
                <input type="checkbox" v-model="isStaff" aria-label="Staff Checkbox"/> Staff Account
            </label>

            <button type="submit">Create Account</button>

            <p class="switch-link">
              Already Have an account?
              <router-link to="/login">Log in here</router-link>
            </p>
        </form>
        <p v-if="message" :class="messageClass">{{ message }}</p>
    </div>
</template>

<script setup>
  import {ref, computed} from 'vue'

  const displayName = ref('')
  const email = ref('')
  const password = ref('')
  const confirmPassword = ref('')
  const isStaff = ref(false)
  const message = ref('')

  const messageClass = computed(() => {
    return message.value.includes('successfully') ? 'success-message' : 'error-message';
  })

  async function handleSignup() {
    // Client-side validation
    if (password.value !== confirmPassword.value) {
      message.value = "Passwords do not match!"
      return
    }

    if (password.value.length < 6) {
      message.value = "Password must be at least 6 characters long!"
      return
    }

    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        method: 'addAccount',
        displayName: displayName.value,
        email: email.value,
        password: password.value,
        confirmPassword: confirmPassword.value,
        isStaff: isStaff.value
      })
    })
    const data = await response.json()
    message.value = data.message
    
    // Clear form on success
    if (response.ok && data.message.includes('successfully')) {
      displayName.value = ''
      email.value = ''
      password.value = ''
      confirmPassword.value = ''
      isStaff.value = false
    }
  }
</script>

<style scoped>
  .account-form {
    max-width: 400px;
    margin: auto;
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }

  input, button {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px;
  }
  input {
    background-color: pink;
    color: black;
    border: solid 2px;
    border-color: lightpink;
  }
  button {
    background-color: pink;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 6px;
  }
  button:hover {
    background-color: lightpink;
  }
  .staff-label {
    display:inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
  }
  .success-message {
    color: green;
    font-weight: bold;
    margin-top: 10px;
  }
  .error-message {
    color: red;
    font-weight: bold;
    margin-top: 10px;
  }
</style>