<template>
  <div class="settings-page">
    <!-- Show settings only when authenticated -->
    <div v-if="isAuthenticated" class="settings-wrapper">
      <!-- Translucent Background -->
      <div class="translucent-background"></div>

      <!-- Centered Content -->
      <div class="settings-center">
        <div class="settings-card">
          <div class="card-header">
            <button class="close-btn" @click="goToHomepage" title="Close Settings">
              <i class="fas fa-times"></i>
            </button>
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <p v-if="user.email">Welcome, {{ user.email }}</p>
          </div>

          <div class="card-body">
            <div class="actions-grid">
              <button class="action-btn" @click="navigateTo('/shipment')">
                <i class="fas fa-plus-circle"></i>
                <span>New Shipment</span>
              </button>
              <button class="action-btn" @click="navigateTo('/faq')">
                <i class="fas fa-question-circle"></i>
                <span>FAQ</span>
              </button>
              <button class="action-btn" @click="navigateTo('/cdb')">
                <i class="fas fa-life-ring"></i>
                <span>Customer Dashboard</span>
              </button>
              <button class="action-btn" @click="navigateTo('/changepw')">
                <i class="fas fa-key"></i>
                <span>Change Password</span>
              </button>
            </div>
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
        <div class="center-button action-buttons">
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

    // Centralized navigation method
    navigateTo(route) {
      if (!this.isAuthenticated) {
        alert('Please log in to access this feature');
        this.redirectToLogin();
        return;
      }

      // Use Vue Router if available, otherwise fall back to window.location
      if (this.$router) {
        this.$router.push(route);
      } else {
        window.location.href = route;
      }
    },

    // Go back to homepage
    goToHomepage() {
      if (this.$router) {
        this.$router.push('/');
      } else {
        window.location.href = '/';
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
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
}

.settings-page {
  min-height: 100vh;
}

.settings-wrapper {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.translucent-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 228, 238, 0.9) 0%, rgba(241, 217, 223, 0.9) 100%);
  backdrop-filter: blur(10px);
  z-index: -1;
}

.settings-center {
  width: 100%;
  max-width: 500px;
  z-index: 1;
}

.settings-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(255, 66, 117, 0.2);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  overflow: hidden;
  position: relative;
}

.card-header {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  padding: 2rem;
  text-align: center;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1.2rem;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.card-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.card-header p {
  margin: 0;
  opacity: 0.9;
  font-size: 1rem;
}

.card-body {
  padding: 2rem;
}

/* 2x2 Grid Layout */
.actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem 1rem;
  border: 1px solid var(--pink-grey);
  border-radius: 12px;
  background: white;
  color: var(--slate-blue);
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  min-height: 100px;
  justify-content: center;
}

.action-btn:hover {
  border-color: var(--hot-pink);
  color: var(--hot-pink);
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(255, 66, 117, 0.2);
}

.action-btn i {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
}

.action-btn span {
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.2;
}

/* Login Required Styles */
.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-message {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(255, 66, 117, 0.2);
  max-width: 400px;
  width: 90%;
}

.message-icon {
  font-size: 4rem;
  color: var(--hot-pink);
  margin-bottom: 1.5rem;
}

.login-message h2 {
  color: var(--dark-slate-blue);
  margin-bottom: 1rem;
  font-size: 1.8rem;
}

.login-message p {
  color: var(--slate-blue);
  margin-bottom: 2rem;
  font-size: 1.1rem;
  line-height: 1.5;
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
  gap: 0.5rem;
  justify-content: center;
}


.center-button {
  display: flex;
  justify-content: center;
  align-items: center;
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
@media (max-width: 768px) {
  .settings-wrapper {
    padding: 1rem;
  }

  .settings-center {
    max-width: 100%;
  }

  .card-header {
    padding: 1.5rem;
  }

  .card-body {
    padding: 1.5rem;
  }

  .actions-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .action-btn {
    padding: 1.25rem 1rem;
    min-height: 80px;
  }

  .action-btn i {
    font-size: 1.5rem;
  }

  .close-btn {
    top: 0.75rem;
    right: 0.75rem;
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }
}



@media (max-width: 480px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }

  .action-btn {
    min-height: 70px;
  }

  .action-btn i {
    font-size: 1.3rem;
  }

  .action-btn span {
    font-size: 0.85rem;
  }
}
</style>
