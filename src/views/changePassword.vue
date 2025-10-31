<template>
  <div class="settings-page">
    <div v-if="isAuthenticated" class="settings-wrapper">
      <div class="settings-card">
        <div class="card-header">
          <h2><i class="fas fa-cog"></i> Account Settings</h2>
        </div>

        <div class="card-body">
          <!-- Update Display Name -->
          <form @submit.prevent="updateDisplayName" class="settings-form">
            <label for="displayName">Display Name</label>
            <input
              type="text"
              id="displayName"
              v-model="displayName"
              placeholder="Enter new display name"
              required
            />
            <button type="submit" class="btn btn-primary">Update Display Name</button>
          </form>

          <hr />

          <!-- Update Password -->
          <form @submit.prevent="updatePassword" class="settings-form">
            <label for="newPassword">New Password</label>
            <input
              type="password"
              id="newPassword"
              v-model="newPassword"
              placeholder="Enter new password"
              required
            />
            <label for="confirmPassword">Confirm Password</label>
            <input
              type="password"
              id="confirmPassword"
              v-model="confirmPassword"
              placeholder="Confirm new password"
              required
            />
            <button type="submit" class="btn btn-primary">Update Password</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Show login prompt when not authenticated -->
    <div v-else class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to manage your account settings.</p>
        <button @click="redirectToLogin" class="btn btn-primary">
          <i class="fas fa-sign-in-alt"></i>
          Go to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SettingsPage",
  data() {
    return {
      isAuthenticated: false,
      accountId: null,
      displayName: '',
      newPassword: '',
      confirmPassword: ''
    };
  },
  mounted() {
    this.checkAuthentication();
  },
  methods: {
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.displayName = user.displayName || '';
          this.accountId = user.accountId;
          this.isAuthenticated = true;
        } catch (error) {
          console.error('Error parsing user data:', error);
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
      }
    },

    redirectToLogin() {
      this.$router ? this.$router.push('/login') : window.location.href = '/login';
    },

    async updateDisplayName() {
      if (!this.displayName.trim()) {
        alert("Display name cannot be empty.");
        return;
      }

      try {
        const res = await fetch("/api/account.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            method: "updateDisplayName",
            accountId: this.accountId,
            newDisplayName: this.displayName
          })
        });
        const data = await res.json();
        if (res.ok) {
          alert(data.message);
          // Update session storage
          const user = JSON.parse(sessionStorage.getItem('currentUser'));
          user.displayName = this.displayName;
          sessionStorage.setItem('currentUser', JSON.stringify(user));
        } else {
          alert(data.message || "Failed to update display name.");
        }
      } catch (error) {
        console.error(error);
        alert("Error updating display name.");
      }
    },

    async updatePassword() {
      if (this.newPassword.length < 6) {
        alert("Password must be at least 6 characters long.");
        return;
      }
      if (this.newPassword !== this.confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      try {
        const res = await fetch("/api/account.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            method: "updatePassword",
            accountId: this.accountId,
            newPassword: this.newPassword
          })
        });
        const data = await res.json();
        if (res.ok) {
          alert(data.message);
          this.newPassword = '';
          this.confirmPassword = '';
        } else {
          alert(data.message || "Failed to update password.");
        }
      } catch (error) {
        console.error(error);
        alert("Error updating password.");
      }
    }
  }
};
</script>

<style scoped>
/* Keep your existing styles, just add form spacing */
.settings-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.settings-form input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
}

.settings-form button {
  align-self: flex-start;
  padding: 0.6rem 1.2rem;
}
</style>
