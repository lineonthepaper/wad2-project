<template>
  <div class="account-form">
    <h2>Login</h2>

    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" required aria-label="email"/>
      <input v-model="password" type="password" placeholder="Password" required aria-label="password"/>
      <button type="submit">Login</button>
    </form>

    <p v-if="message">{{ message }}</p>

    <p class="switch-link">
      Don't have an account?
      <router-link to="/signup"> Create one here</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const email = ref('')
const password = ref('')
const message = ref('')

async function handleLogin() {
  const response = await fetch('/api/accounts.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      method: 'loginAccount',
      email: email.value,
      password: password.value,
    }),
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
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
input,
button {
  display: block;
  width: 100%;
  margin-top: 10px;
  padding: 10px;
}
button {
  background-color: lightpink;
  color: white;
  border: 2px solid lightpink;
  cursor: pointer;
  border-radius: 6px;
}

button:hover {
  background-color: pink;
}

.switch-link {
  margin-top: 1rem;
  text-align: center;
}
.switch-link a {
  color: #42b983;
  text-decoration: none;
  font-weight: 500;
}
.switch-link a:hover {
  text-decoration: underline;
}
</style>
