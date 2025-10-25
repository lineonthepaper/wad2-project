<template>
  <div>
    <!-- Show dashboard only when authenticated -->
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <!-- Header -->
      <div class="dashboard-header">
        <div class="header-background">
          <div class="header-content">
            <div class="header-text">
              <h1 class="dashboard-title">
                <span class="title-main">Parcel Tracking</span>
                <span class="title-sub">Dashboard</span>
              </h1>
              <p class="welcome-message">Welcome back, <strong>{{user.email}}</strong>! Track your shipments in real-time.</p>
            </div>
            <div class="header-stats">
              <div class="header-stat">
                <span class="stat-value">{{ totalShipments }}</span>
                <span class="stat-label">Total Shipments</span>
              </div>
              <div class="header-stat">
                <span class="stat-value">{{ stats.inProgress }}</span>
                <span class="stat-label">In Transit</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Stats Overview -->
        <section class="stats-overview">
          <div class="stats-grid">
            <div class="stat-card" :class="`stat-${stat.key}`" v-for="stat in enhancedStats" :key="stat.key">
              <div class="stat-content">
                <div class="stat-icon">
                  <i :class="stat.icon"></i>
                </div>
                <div class="stat-data">
                  <h3 class="stat-title">{{ stat.title }}</h3>
                  <p class="stat-number">{{ stat.value }}</p>
                  <div class="stat-trend" :class="stat.trend">
                    <i :class="stat.trendIcon"></i>
                    <span>{{ stat.trendValue }}</span>
                  </div>
                </div>
              </div>
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" v-for="(point, index) in stat.chartData" :key="index"
                       :style="{ height: point + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Globe and Tracking -->
        <section class="tracking-section">
          <div class="section-column globe-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-globe-americas"></i> Live Tracking Map</h3>
                <div class="card-actions">
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <button class="btn-icon" v-if="selectedParcel" @click="clearRoute" title="Clear Route">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div ref="globeContainer" class="globe-container">
                  <div v-if="globeError" class="globe-error">
                    <div class="error-icon">
                      <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p>Failed to load globe visualization</p>
                    <button class="btn-retry" @click="forceReinit">Retry</button>
                  </div>
                  <div v-else-if="!globeInitialized" class="globe-loading">
                    <div class="loading-spinner"></div>
                    <p>Initializing 3D Globe...</p>
                  </div>
                </div>

                <div v-if="selectedParcel" class="selected-parcel-info">
                  <div class="parcel-header">
                    <h4>Active Tracking: {{ selectedParcel.trackingId }}</h4>
                    <span class="status-badge" :class="`status-${selectedParcel.status.toLowerCase().replace(' ', '-')}`">
                      {{ selectedParcel.status }}
                    </span>
                  </div>
                  <div class="route-progress">
                    <div class="progress-labels">
                      <span class="progress-label">{{ getLocationName(selectedParcel.location) }}</span>
                      <span class="progress-percent">{{ Math.round(calculateProgress(selectedParcel)) }}%</span>
                      <span class="progress-label">{{ getLocationName(selectedParcel.destination) }}</span>
                    </div>
                    <div class="progress-track">
                      <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: calculateProgress(selectedParcel) + '%' }"></div>
                        <div class="progress-marker" :style="{ left: calculateProgress(selectedParcel) + '%' }">
                          <i class="fas fa-shipping-fast"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-selection">
                  <i class="fas fa-mouse-pointer"></i>
                  <p>Select a shipment from the list to view its route</p>
                </div>
              </div>
            </div>
          </div>

          <div class="section-column parcels-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-boxes"></i> Recent Shipments</h3>
                <div class="card-actions">
                  <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search shipments..." v-model="searchQuery">
                  </div>
                  <button class="btn-icon" title="Filter">
                    <i class="fas fa-filter"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="parcels-list">
                  <div
                    v-for="parcel in filteredParcels"
                    :key="parcel.id"
                    class="parcel-item"
                    :class="{ 'active': selectedParcel && selectedParcel.id === parcel.id }"
                    @click="showParcelRoute(parcel)"
                  >
                    <div class="parcel-icon">
                      <i class="fas fa-box"></i>
                    </div>
                    <div class="parcel-details">
                      <div class="parcel-header">
                        <h4 class="tracking-id">{{ parcel.trackingId }}</h4>
                        <span class="status-badge" :class="`status-${parcel.status.toLowerCase().replace(' ', '-')}`">
                          {{ parcel.status }}
                        </span>
                      </div>
                      <div class="parcel-info">
                        <div class="info-item">
                          <i class="fas fa-user"></i>
                          <span>{{ parcel.customer }}</span>
                        </div>
                        <div class="info-item">
                          <i class="fas fa-map-marker-alt"></i>
                          <span>{{ getLocationName(parcel.currentLocation || parcel.location) }}</span>
                        </div>
                        <div class="info-item">
                          <i class="fas fa-calendar-alt"></i>
                          <span>{{ formatDate(parcel.expectedDelivery) }}</span>
                        </div>
                      </div>
                      <div v-if="parcel.status === 'In Progress'" class="parcel-progress">
                        <div class="progress-mini">
                          <div class="progress-fill-mini" :style="{ width: calculateProgress(parcel) + '%' }"></div>
                        </div>
                        <span class="progress-text">{{ Math.round(calculateProgress(parcel)) }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Quick Actions & Notifications -->
        <section class="bottom-section">
          <div class="section-column actions-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
              </div>
              <div class="card-body">
                <div class="actions-grid">
                  <button class="action-btn" @click="navigateToCreateShipment">
                    <i class="fas fa-plus-circle"></i>
                    <span>New Shipment</span>
                  </button>
                  <button class="action-btn" @click="navigateToFAQ">
                    <i class="fas fa-question-circle"></i>
                    <span>FAQ</span>
                  </button>
                  <button class="action-btn" @click="navigateToHelp">
                    <i class="fas fa-life-ring"></i>
                    <span>Help</span>
                  </button>
                  <button class="action-btn" @click="navigateToSettings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="section-column notifications-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-bell"></i> Recent Activity</h3>
              </div>
              <div class="card-body">
                <div class="notifications-list">
                  <div class="notification-item" v-for="notification in notifications" :key="notification.id">
                    <div class="notification-icon" :class="notification.type">
                      <i :class="notification.icon"></i>
                    </div>
                    <div class="notification-content">
                      <p class="notification-text">{{ notification.message }}</p>
                      <span class="notification-time">{{ notification.time }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Show login prompt when not authenticated -->
    <div v-else class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to access your parcel tracking dashboard</p>
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
import Globe from 'globe.gl';

export default {
  name: "EnhancedParcelDashboard",
  data() {
    return {
      isAuthenticated: false,
      user: {
        email: ''
      },
      searchQuery: '',
      selectedParcel: null,
      globeInitialized: false,
      globeError: false,
      errorMessage: "",
      stats: {
        inProgress: 12,
        delivered: 48,
        pending: 5,
      },
      parcels: [
        {
          id: 1,
          trackingId: "TRK-784231",
          customer: "Alice Johnson",
          status: "In Progress",
          expectedDelivery: "2025-10-18",
          location: [37.7749, -122.4194],
          currentLocation: [39.8283, -98.5795],
          destination: [40.7128, -74.0060]
        },
        {
          id: 2,
          trackingId: "TRK-784232",
          customer: "Robert Smith",
          status: "Delivered",
          expectedDelivery: "2025-10-15",
          location: [51.5074, -0.1278],
          currentLocation: [48.8566, 2.3522],
          destination: [48.8566, 2.3522]
        },
        {
          id: 3,
          trackingId: "TRK-784233",
          customer: "Maria Garcia",
          status: "Pending",
          expectedDelivery: "2025-10-20",
          location: [35.6895, 139.6917],
          currentLocation: [35.6895, 139.6917],
          destination: [34.0522, -118.2437]
        },
        {
          id: 4,
          trackingId: "TRK-784234",
          customer: "David Wilson",
          status: "In Progress",
          expectedDelivery: "2025-10-19",
          location: [48.8566, 2.3522],
          currentLocation: [45.4642, 9.1900],
          destination: [41.9028, 12.4964]
        },
      ],
      notifications: [
        {
          id: 1,
          type: 'success',
          icon: 'fas fa-check-circle',
          message: 'Package TRK-784232 delivered successfully',
          time: '2 hours ago'
        },
        {
          id: 2,
          type: 'info',
          icon: 'fas fa-info-circle',
          message: 'Package TRK-784231 is in transit to New York',
          time: '5 hours ago'
        },
        {
          id: 3,
          type: 'warning',
          icon: 'fas fa-exclamation-triangle',
          message: 'Package TRK-784233 is awaiting pickup',
          time: '1 day ago'
        },
        {
          id: 4,
          type: 'info',
          icon: 'fas fa-info-circle',
          message: 'New shipment created: TRK-784235',
          time: '2 days ago'
        }
      ],
      globe: null,
      arcsData: [],
      pointsData: []
    };
  },
  computed: {
    totalShipments() {
      return this.stats.inProgress + this.stats.delivered + this.stats.pending;
    },
    filteredParcels() {
      if (!this.searchQuery) return this.parcels;
      const query = this.searchQuery.toLowerCase();
      return this.parcels.filter(parcel =>
        parcel.trackingId.toLowerCase().includes(query) ||
        parcel.customer.toLowerCase().includes(query) ||
        this.getLocationName(parcel.currentLocation || parcel.location).toLowerCase().includes(query)
      );
    },
    enhancedStats() {
      return [
        {
          key: 'in-progress',
          title: 'In Transit',
          value: this.stats.inProgress,
          icon: 'fas fa-shipping-fast',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+2 today',
          chartData: [65, 70, 75, 80, 75, 70, 68]
        },
        {
          key: 'delivered',
          title: 'Delivered',
          value: this.stats.delivered,
          icon: 'fas fa-check-circle',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+12%',
          chartData: [40, 45, 50, 55, 60, 65, 70]
        },
        {
          key: 'pending',
          title: 'Pending',
          value: this.stats.pending,
          icon: 'fas fa-clock',
          trend: 'down',
          trendIcon: 'fas fa-arrow-down',
          trendValue: '-1 today',
          chartData: [80, 75, 70, 65, 60, 55, 50]
        },
        {
          key: 'total',
          title: 'Total Shipments',
          value: this.totalShipments,
          icon: 'fas fa-boxes',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+8%',
          chartData: [50, 55, 60, 65, 70, 75, 80]
        }
      ];
    }
  },
  mounted() {
    this.checkAuthentication();
    if (this.isAuthenticated) {
      this.initializeDashboard();
    }

    // Listen for login status changes
    window.addEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  beforeUnmount() {
    // Clean up event listener
    window.removeEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  methods: {
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.user.email = user.email || user.display_name || 'User';
          this.isAuthenticated = true;
          console.log('User authenticated:', this.user.email);
        } catch (error) {
          console.error('Error parsing user data:', error);
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
        console.log('No user data found in sessionStorage');
      }
    },

    handleLoginStatusChange() {
      console.log('Login status changed, rechecking authentication...');
      this.checkAuthentication();
      if (this.isAuthenticated) {
        this.initializeDashboard();
      }
    },

    initializeDashboard() {
      console.log('Initializing dashboard for authenticated user...');
      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 100);
      });
    },

    redirectToLogin() {
      // Redirect to login page
      window.location.href = '/login';
    },

    // Navigation Methods
    navigateToCreateShipment() {
      if (!this.isAuthenticated) {
        alert('Please log in to create a shipment');
        this.redirectToLogin();
        return;
      }
      // Redirect to shipment page
      window.location.href = '/shipment';
    },

    navigateToFAQ() {
      if (!this.isAuthenticated) {
        this.redirectToLogin();
        return;
      }
      window.location.href = '/faq';
    },

    navigateToHelp() {
      if (!this.isAuthenticated) {
        this.redirectToLogin();
        return;
      }
      window.location.href = '/help';
    },

    navigateToSettings() {
      if (!this.isAuthenticated) {
        this.redirectToLogin();
        return;
      }
      // You can implement settings navigation here
      alert('Settings page would open here');
    },

    async initGlobe() {
      try {
        console.log('Starting globe initialization...');

        const container = this.$refs.globeContainer;
        if (!container) {
          throw new Error('Globe container not found');
        }

        const width = container.clientWidth || 800;
        const height = container.clientHeight || 500;

        console.log('Container dimensions:', width, 'x', height);

        // Initialize globe with basic settings
        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)(container);

        console.log('Basic globe created');

        // Add initial data
        this.updateGlobeData();

        this.globeInitialized = true;
        console.log('Globe initialized successfully');

      } catch (error) {
        console.error('Globe initialization error:', error);
        this.globeError = true;
        this.errorMessage = error.message;
      }
    },

    updateGlobeData() {
      if (!this.globe) return;

      try {
        // Update points data - show all parcels as points
        this.pointsData = this.parcels.map(parcel => ({
          ...parcel,
          // For display, use current location if available, otherwise start location
          displayLocation: parcel.currentLocation || parcel.location
        }));

        this.globe
          .pointsData(this.pointsData)
          .pointLat(d => d.displayLocation[0])
          .pointLng(d => d.displayLocation[1])
          .pointColor(d => this.getStatusColor(d.status))
          .pointAltitude(0.1)
          .pointRadius(0.4)
          .pointLabel(d => `${d.trackingId}: ${d.customer} (${d.status})`);

        // Update arcs if we have a selected parcel
        if (this.selectedParcel) {
          this.updateRouteForParcel(this.selectedParcel);
        }

        console.log('Globe data updated successfully');

      } catch (error) {
        console.error('Error updating globe data:', error);
        this.globeError = true;
        this.errorMessage = error.message;
      }
    },

    showParcelRoute(parcel) {
      if (!this.globe) {
        console.error('Globe not ready');
        return;
      }

      this.selectedParcel = parcel;
      this.updateRouteForParcel(parcel);
    },

    updateRouteForParcel(parcel) {
      if (!this.globe) return;

      // Create multiple arcs to show the complete journey
      this.arcsData = [
        // Arc from start to current location (green - completed path)
        {
          start: parcel.location,
          end: parcel.currentLocation || parcel.location,
          color: '#00ff00',
          stroke: 1.5
        },
        // Arc from current location to destination (orange - remaining path)
        {
          start: parcel.currentLocation || parcel.location,
          end: parcel.destination,
          color: '#ffa500',
          stroke: 1.5
        }
      ];

      // Update the globe with new arcs
      this.globe
        .arcsData(this.arcsData)
        .arcStartLat(d => d.start[0])
        .arcStartLng(d => d.start[1])
        .arcEndLat(d => d.end[0])
        .arcEndLng(d => d.end[1])
        .arcColor(d => d.color)
        .arcStroke(d => d.stroke)
        .arcAltitude(0.05)
        .arcDashLength(0.3)
        .arcDashGap(0.1)
        .arcDashAnimateTime(4000);

      // Focus camera on the entire route
      this.focusOnRoute(parcel.location, parcel.destination);
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      const midLat = (start[0] + end[0]) / 2;
      const midLng = (start[1] + end[1]) / 2;

      // Calculate a good altitude to show the entire route
      const latDiff = Math.abs(start[0] - end[0]);
      const lngDiff = Math.abs(start[1] - end[1]);
      const maxDiff = Math.max(latDiff, lngDiff);
      const altitude = Math.max(1.5, 3 - maxDiff * 0.5);

      this.globe.pointOfView({ lat: midLat, lng: midLng, altitude });
    },

    clearRoute() {
      if (this.globe) {
        this.arcsData = [];
        this.selectedParcel = null;
        this.globe.arcsData(this.arcsData);
        this.globe.pointOfView({ lat: 0, lng: 0, altitude: 1.8 });
        // Refresh points data
        this.updateGlobeData();
      }
    },

    calculateProgress(parcel) {
      if (parcel.status === 'Delivered') return 100;
      if (parcel.status === 'Pending') return 0;

      // Simple linear progress calculation based on distance
      const start = parcel.location;
      const current = parcel.currentLocation || parcel.location;
      const end = parcel.destination;

      const totalDistance = this.calculateDistance(start, end);
      const traveledDistance = this.calculateDistance(start, current);

      if (totalDistance === 0) return 0;

      return Math.min(100, (traveledDistance / totalDistance) * 100);
    },

    calculateDistance(coord1, coord2) {
      // Haversine distance calculation
      const [lat1, lon1] = coord1;
      const [lat2, lon2] = coord2;

      const R = 6371; // Earth's radius in km
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a =
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      return R * c;
    },

    getLocationName(coords) {
      // Simple coordinate to city name mapping
      const locations = {
        '37.7749,-122.4194': 'San Francisco',
        '40.7128,-74.0060': 'New York',
        '51.5074,-0.1278': 'London',
        '48.8566,2.3522': 'Paris',
        '35.6895,139.6917': 'Tokyo',
        '34.0522,-118.2437': 'Los Angeles',
        '41.9028,12.4964': 'Rome',
        '39.8283,-98.5795': 'Kansas',
        '45.4642,9.1900': 'Milan'
      };

      const key = `${coords[0]},${coords[1]}`;
      return locations[key] || 'Unknown Location';
    },

    getStatusColor(status) {
      const colors = {
        'In Progress': '#ffa500',
        'Delivered': '#00ff00',
        'Pending': '#ff4444'
      };
      return colors[status] || '#cccccc';
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },

    forceReinit() {
      this.globeInitialized = false;
      this.globeError = false;
      this.globe = null;
      this.arcsData = [];
      this.selectedParcel = null;

      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 100);
      });
    }
  }
};
</script>

<style scoped>
/* Import Font Awesome */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

/* Pink Color Palette */
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
}

/* Base Styles */
.dashboard-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header */
.dashboard-header {
  margin-bottom: 2rem;
}

.header-background {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  padding: 2rem;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 4px 20px rgba(255, 66, 117, 0.3);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.header-text {
  flex: 1;
}

.dashboard-title {
  margin-bottom: 0.5rem;
}

.title-main {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.1;
}

.title-sub {
  display: block;
  font-size: 1.8rem;
  font-weight: 300;
  opacity: 0.9;
}

.welcome-message {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 2rem;
}

.header-stat {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Main Content */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
}

/* Stats Overview */
.stats-overview {
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-left: 4px solid;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stat-card.stat-in-progress {
  border-left-color: var(--hot-pink);
}

.stat-card.stat-delivered {
  border-left-color: var(--pink);
}

.stat-card.stat-pending {
  border-left-color: var(--dark-pink);
}

.stat-card.stat-total {
  border-left-color: var(--slate-blue);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-in-progress .stat-icon {
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
}

.stat-delivered .stat-icon {
  background: linear-gradient(135deg, var(--pink), var(--dark-pink));
}

.stat-pending .stat-icon {
  background: linear-gradient(135deg, var(--dark-pink), var(--hot-pink));
}

.stat-total .stat-icon {
  background: linear-gradient(135deg, var(--slate-blue), var(--dark-slate-blue));
}

.stat-data {
  flex: 1;
}

.stat-title {
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
  margin: 0 0 0.3rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.3rem;
  color: var(--dark-slate-blue);
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.stat-trend.up {
  color: var(--hot-pink);
}

.stat-trend.down {
  color: var(--dark-pink);
}

.stat-chart {
  width: 80px;
}

.mini-chart {
  display: flex;
  align-items: end;
  gap: 2px;
  height: 40px;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(to top, var(--hot-pink), var(--dark-pink));
  border-radius: 2px;
  min-height: 2px;
}

/* Tracking Section */
.tracking-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.section-column {
  display: flex;
  flex-direction: column;
}

.section-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0;
  margin-bottom: 1rem;
  background: var(--light-pink);
  border-bottom: 1px solid var(--pink-grey);
}

.card-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  background: white;
  color: var(--hot-pink);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: var(--hot-pink);
  color: white;
  transform: scale(1.05);
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 10px;
  color: var(--slate-blue);
}

.search-box input {
  padding: 0.5rem 0.5rem 0.5rem 2rem;
  border: 1px solid var(--pink-grey);
  border-radius: 8px;
  font-size: 0.9rem;
  width: 180px;
  transition: border-color 0.3s ease;
  background: var(--light-pink);
}

.search-box input:focus {
  outline: none;
  border-color: var(--hot-pink);
}

.card-body {
  padding: 0 1.5rem 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Globe Container */
.globe-container {
  width: 100%;
  height: 400px;
  border-radius: 12px;
  background: var(--dark-slate-blue);
  position: relative;
  overflow: hidden;
  margin-bottom: 1rem;
}

.globe-loading, .globe-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: white;
  font-size: 1.1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--light-pink);
  border-top: 4px solid var(--hot-pink);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.globe-error {
  color: white;
  background: var(--dark-pink);
  padding: 2rem;
  text-align: center;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.btn-retry {
  background: var(--hot-pink);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  margin-top: 1rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-retry:hover {
  background: var(--dark-pink);
}

/* Selected Parcel Info */
.selected-parcel-info {
  background: var(--light-pink);
  border-radius: 12px;
  padding: 1.5rem;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.parcel-header h4 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--dark-slate-blue);
}

.route-progress {
  margin-top: 1rem;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: var(--slate-blue);
}

.progress-percent {
  font-weight: 600;
  color: var(--hot-pink);
}

.progress-track {
  position: relative;
}

.progress-bar {
  height: 8px;
  background: var(--pink-grey);
  border-radius: 4px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--pink), var(--dark-pink));
  transition: width 0.5s ease;
}

.progress-marker {
  position: absolute;
  top: -6px;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  background: white;
  border: 2px solid var(--hot-pink);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: var(--hot-pink);
}

.no-selection {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--slate-blue);
}

.no-selection i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
  color: var(--pink);
}

.no-selection p {
  margin: 0;
  font-size: 1rem;
}

/* Parcels List */
.parcels-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 600px;
  overflow-y: auto;
}

.parcel-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--pink-grey);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.parcel-item:hover {
  border-color: var(--hot-pink);
  transform: translateX(5px);
}

.parcel-item.active {
  border-color: var(--hot-pink);
  background: var(--light-pink);
}

.parcel-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
}

.parcel-details {
  flex: 1;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.tracking-id {
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  margin: 0;
}

.parcel-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  margin-bottom: 0.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--slate-blue);
}

.info-item i {
  width: 12px;
  color: var(--hot-pink);
}

.parcel-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.progress-mini {
  flex: 1;
  height: 4px;
  background: var(--pink-grey);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill-mini {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--pink));
  transition: width 0.5s ease;
}

.progress-text {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--hot-pink);
  min-width: 35px;
}

/* Status Badges */
.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-in-progress {
  background: var(--light-pink);
  color: var(--hot-pink);
}

.status-delivered {
  background: var(--pink-grey);
  color: var(--pink);
}

.status-pending {
  background: var(--pink-grey);
  color: var(--dark-pink);
}

/* Bottom Section */
.bottom-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

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
}

.action-btn:hover {
  border-color: var(--hot-pink);
  color: var(--hot-pink);
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(255, 66, 117, 0.2);
}

.action-btn i {
  font-size: 1.5rem;
}

.action-btn span {
  font-size: 0.9rem;
  font-weight: 500;
}

/* Notifications */
.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--pink-grey);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.notification-item:hover {
  border-color: var(--hot-pink);
  transform: translateX(5px);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.notification-icon.success {
  background: var(--light-pink);
  color: var(--hot-pink);
}

.notification-icon.info {
  background: var(--pink-grey);
  color: var(--pink);
}

.notification-icon.warning {
  background: var(--pink-grey);
  color: var(--dark-pink);
}

.notification-content {
  flex: 1;
}

.notification-text {
  margin: 0 0 0.3rem;
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
}

.notification-time {
  font-size: 0.8rem;
  color: var(--slate-blue);
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

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
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
}

.btn-primary {
  background: var(--hot-pink);
  color: white;
}

.btn-primary:hover {
  background: var(--dark-pink);
  transform: translateY(-2px);
}

.btn-secondary {
  background: var(--light-pink);
  color: var(--hot-pink);
  border: 1px solid var(--hot-pink);
}

.btn-secondary:hover {
  background: var(--hot-pink);
  color: white;
}

/* Animations */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1024px) {
  .tracking-section {
    grid-template-columns: 1fr;
  }

  .bottom-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }

  .header-stats {
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .search-box input {
    width: 100%;
  }

  /* Responsive design for login message */
  .login-message {
    padding: 2rem 1.5rem;
    margin: 1rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    justify-content: center;
  }
}
</style>
