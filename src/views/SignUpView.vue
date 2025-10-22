<template>
    <div class="account-form">
        <h2>Sign Up</h2>

        <form @submit.prevent="handleSignup">
            Name: <br>
            <input v-model="displayName" placeholder="Display Name" required aria-label="Display Name"/>

            <br>
            <br>

            Email: <br>
            <input v-model="email" type="email" placeholder="Email" required aria-label="Email" @input="validateEmail"/>
            <p v-if="emailError" class="error-message">Please enter a valide email address</p>
            <br>
            <br>

            Password: <br>
            <input v-model="password" type="password" placeholder="Password" required aria-label="Password"/>



            <label class="staff-label">
                <input type="checkbox" v-model="isStaff" aria-label="Staff Checkbox"/> Staff Account
            </label>


            <button type="submit" :disabled="emailError">Create Account</button>

            <p class="switch-link">
              Already Have an account?
              <router-link to="/login">Log in here</router-link>
            </p>
        </form>
        <p v-if="message">{{ message }}</p>
    </div>
</template>

<script setup>
  import {ref} from 'vue'

  const displayName = ref('')
  const email = ref('')
  const password = ref('')
  const isStaff = ref(false)
  const message = ref('')
  const emailError = ref(false)

  //Validate email format
  function validateEmail() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    emailError.value = !emailRegex.test(email.value);
  }

  async function handleSignup() {
    const response = await fetch('/api/accounts.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        method: 'addAccount',
        displayName: displayName.value,
        email: email.value,
        password: password.value,
        isStaff: isStaff.value
      })

    })
    const data = await response.json()
    message.value = data.message
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
  .error-message {
    color: red;
    font-size: 12px;
    margin-top: 5px;
  }
</style>
