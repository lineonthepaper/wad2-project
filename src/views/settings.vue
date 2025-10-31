<template>
  <div class="settings-page">
    <!-- Show settings only when authenticated -->
    <div v-if="isAuthenticated" class="settings-wrapper">
      <div class="settings-card">
        <div class="card-header">
          <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        </div>

        <div class="card-body">
          <div class="actions-grid">
            <button class="action-btn" @click="navigateTo('/shipment')">
              <i class="fas fa-truck"></i>
              <span>New Shipment</span>
            </button>
            <button class="action-btn" @click="navigateTo('/faq')">
              <i class="fas fa-question-circle"></i>
              <span>FAQ</span>
            </button>
            <button class="action-btn" @click="navigateTo('/help')">
              <i class="fas fa-life-ring"></i>
              <span>Help</span>
            </button>
            <button class="action-btn" @click="navigateTo('/settings')">
              <i class="fas fa-cog"></i>
              <span>Settings</span>
            </button>
          </div>
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
        <p>Please log in to access Quick Actions</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login
          </button>
        </div>
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
      user: {
        email: ''
      }
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
          this.user.email = user.displayName || user.email || 'User';
          this.isAuthenticated = true;
        } catch (error) {
          console.error('Error parsing user data:', error);
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
      }
    },
    navigateTo(route) {
      if (!this.isAuthenticated) {
        alert('Please log in to access this feature');
        this.redirectToLogin();
        return;
      }
      if (this.$router) {
        this.$router.push(route);
      } else {
        window.location.href = route;
      }
    },
    redirectToLogin() {
      if (this.$router) {
        this.$router.push('/login');
      } else {
        window.location.href = '/login';
      }
    }
  }
};
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');

:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink-grey: #f1d9df;
  --slate-blue: #8796b3;
  --dark-slate-blue: #455a64;
}

.settings-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #fff6f8 0%, #ffeef3 100%);
}

.settings-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(255, 66, 117, 0.25);
  width: 520px;
  max-width: 90%;
  overflow: hidden;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* Header with gradient pink */
.card-header {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  justify-content: flex-start;
}

.card-header h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

/* Body with centered buttons */
.card-body {
  padding: 1.5rem;
  display: flex;
  justify-content: center;
}

.actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1rem;
  justify-items: center;
  width: 100%;
}

/* Button styles */
.action-btn {
  width: 100%;
  max-width: 220px;
  background: #fff;
  border: 1px solid var(--pink-grey);
  border-radius: 10px;
  color: var(--slate-blue);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem 0.5rem;
  gap: 0.5rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.action-btn:hover {
  border-color: var(--hot-pink);
  color: var(--hot-pink);
  background: #fff9fa;
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.2);
}

.action-btn i {
  font-size: 1.6rem;
}

.action-btn span {
  font-size: 0.95rem;
  font-weight: 500;
}

/* Login prompt */
.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #ffe8ee 0%, #f1d9df 100%);
}

.login-message {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 16px;
  box-shadow: 0 8px 30px rgba(255, 66, 117, 0.2);
  max-width: 400px;
  width: 90%;
}

.message-icon {
  font-size: 3rem;
  color: var(--hot-pink);
  margin-bottom: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary {
  background: var(--hot-pink);
  color: white;
}

.btn-primary:hover {
  background: var(--dark-pink);
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 600px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
