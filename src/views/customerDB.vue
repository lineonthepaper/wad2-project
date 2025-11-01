<template>
  <div>
    <!-- Show dashboard only when authenticated -->
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <!-- Loading Overlay -->
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner-large"></div>
        <p>Loading your shipments...</p>
      </div>

      <!-- Error Banner -->
      <div v-if="errorMessage" class="error-banner">
        <i class="fas fa-exclamation-circle"></i>
        {{ errorMessage }}
        <button @click="fetchUserShipments" class="btn-retry">Retry</button>
      </div>

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
      <div class="main-content" v-if="!loading">
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
                        <div class="info-item" v-if="parcel.itemDescription">
                          <i class="fas fa-tag"></i>
                          <span>{{ parcel.itemDescription }}</span>
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

                  <!-- Empty state -->
                  <div v-if="filteredParcels.length === 0" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No shipments found</p>
                    <p class="empty-subtext" v-if="searchQuery">Try adjusting your search terms</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Notifications Section Only -->
        <section class="notifications-section">
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

                  <!-- Empty notifications -->
                  <div v-if="notifications.length === 0" class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No recent activity</p>
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
import countryData from '/json/countryData.json';
import axios from 'axios';

export default {
  name: "EnhancedParcelDashboard",
  data() {
    return {
      isAuthenticated: false,
      user: {
        email: '',
        account_id: null
      },
      searchQuery: '',
      selectedParcel: null,
      globeInitialized: false,
      globeError: false,
      errorMessage: "",
      loading: false,
      stats: {
        inProgress: 0,
        delivered: 0,
        pending: 0,
      },
      parcels: [],
      notifications: [],
      globe: null,
      arcsData: [],
      pointsData: [],
      refreshInterval: null
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
        (parcel.customer && parcel.customer.toLowerCase().includes(query)) ||
        this.getLocationName(parcel.currentLocation || parcel.location).toLowerCase().includes(query) ||
        (parcel.itemDescription && parcel.itemDescription.toLowerCase().includes(query))
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
      // Refresh every 30 seconds for real-time updates
      this.refreshInterval = setInterval(() => {
        if (this.isAuthenticated) {
          this.fetchUserShipments();
        }
      }, 30000);
    }

    window.addEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    window.removeEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  methods: {
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.user.email = user.email || user.display_name || 'User';
          this.user.account_id = user.account_id || null;
          this.isAuthenticated = true;
          console.log('User authenticated:', this.user.email, 'Account ID:', this.user.account_id);
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
      } else {
        // Clear data when logging out
        this.parcels = [];
        this.notifications = [];
        this.stats = { inProgress: 0, delivered: 0, pending: 0 };
      }
    },

    async initializeDashboard() {
      console.log('Initializing dashboard for authenticated user...');
      await this.fetchUserShipments();
      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 100);
      });
    },

    async fetchUserShipments() {
      this.loading = true;
      this.errorMessage = "";
      try {
        console.log('Fetching shipments for:', this.user.email, 'Account ID:', this.user.account_id);

        // Use account_id if available, otherwise fall back to email
        const requestData = this.user.account_id 
          ? { method: 'getAllMailByAccountId', account_id: this.user.account_id }
          : { method: 'getAllMailByCustomerEmail', email: this.user.email };

        const response = await axios.post('/api/dashboard.php', requestData);

        console.log('API Response:', response.data);

        if (response.data.success) {
          this.parcels = this.transformShipmentData(response.data.shipments);
          this.updateStats();
          this.generateNotifications();
          console.log('Successfully loaded', this.parcels.length, 'shipments');

          // If using example data, show a note
          if (response.data.note) {
            console.log('Note from backend:', response.data.note);
          }
        } else {
          console.error('Failed to fetch shipments:', response.data.error);
          this.errorMessage = response.data.error || 'Failed to load shipments';
        }
      } catch (error) {
        console.error('Error fetching shipments:', error);
        this.errorMessage = 'Failed to load shipments. Please try again.';
        if (error.response) {
          console.error('Response error:', error.response.data);
        }
      } finally {
        this.loading = false;
      }
    },

    transformShipmentData(shipments) {
      if (!shipments || !Array.isArray(shipments)) {
        console.log('No shipments data found');
        return [];
      }

      console.log('Transforming', shipments.length, 'shipments');

      return shipments.map(shipment => {
        try {
          // Get coordinates from address data
          const senderCountry = shipment.senderAddress?.country_code || shipment.sender_country_code || 'SG';
          const recipientCountry = shipment.recipientAddress?.country_code || shipment.recipient_country_code || 'US';

          console.log('Sender country:', senderCountry, 'Recipient country:', recipientCountry);

          const senderCoords = this.getCountryCoordinates(senderCountry);
          const recipientCoords = this.getCountryCoordinates(recipientCountry);

          // Determine current location based on status
          let currentLocation = senderCoords;
          const progress = this.calculateProgressFromStatus(shipment.status || shipment.mail_status);

          if (progress > 0 && progress < 100) {
            // For in-progress shipments, interpolate between start and end
            currentLocation = [
              senderCoords[0] + (recipientCoords[0] - senderCoords[0]) * (progress / 100),
              senderCoords[1] + (recipientCoords[1] - senderCoords[1]) * (progress / 100)
            ];
          } else if (progress >= 100) {
            currentLocation = recipientCoords;
          }

          // Create tracking ID from available data
          let trackingId = `TRK-${shipment.mail_id.toString().padStart(6, '0')}`;
          if (shipment.tracking_number && shipment.tracking_number !== 0) {
            trackingId = `TRK-${shipment.tracking_number}`;
          }

          // Get customer name from address data
          const customerName = shipment.senderAddress?.name || 
                              shipment.sender_name || 
                              this.user.email.split('@')[0];

          // Get item description from mail_item data
          const itemDescription = shipment.item_description || 
                                 shipment.items?.[0]?.description || 
                                 'Package';

          const transformedParcel = {
            id: shipment.mail_id,
            mailId: shipment.mail_id,
            trackingId: trackingId,
            customer: customerName,
            status: this.mapApiStatus(shipment.status || shipment.mail_status),
            expectedDelivery: shipment.expected_delivery || shipment.created_date,
            location: senderCoords,
            currentLocation: currentLocation,
            destination: recipientCoords,
            progress: progress,
            service: shipment.service || 'Basic Mail',
            totalWeight: shipment.total_weight || shipment.parcel_weight || 0,
            totalValue: shipment.total_value || shipment.declared_value || 0,
            hasBeenPaid: shipment.has_been_paid || false,
            createdDate: shipment.created_date,
            itemDescription: itemDescription,
            rawData: shipment
          };

          console.log('Transformed parcel:', transformedParcel);
          return transformedParcel;

        } catch (error) {
          console.error('Error transforming shipment:', shipment, error);
          return null;
        }
      }).filter(parcel => parcel !== null);
    },

    getCountryCoordinates(countryCode) {
      const country = countryData.find(c => c.code2 === countryCode);
      if (country) {
        console.log(`Found coordinates for ${countryCode}:`, [country.lat, country.long]);
        return [country.lat, country.long];
      }
      // Default to Singapore if not found
      console.warn(`Country code ${countryCode} not found, using Singapore as default`);
      return [1.28478, 103.776222];
    },

    calculateProgressFromStatus(status) {
      const progressMap = {
        'pending': 0,
        'in_transit': 50,
        'in_progress': 50,
        'delivered': 100,
        'completed': 100
      };
      return progressMap[status] || 0;
    },

    mapApiStatus(apiStatus) {
      const statusMap = {
        'pending': 'Pending',
        'in_transit': 'In Progress',
        'in_progress': 'In Progress',
        'delivered': 'Delivered',
        'completed': 'Delivered'
      };
      return statusMap[apiStatus] || 'Pending';
    },

    updateStats() {
      this.stats = {
        inProgress: this.parcels.filter(p => p.status === 'In Progress').length,
        delivered: this.parcels.filter(p => p.status === 'Delivered').length,
        pending: this.parcels.filter(p => p.status === 'Pending').length,
      };
    },

    generateNotifications() {
      // Create notifications from recent shipments
      this.notifications = this.parcels
        .slice(0, 4)
        .sort((a, b) => new Date(b.createdDate) - new Date(a.createdDate))
        .map((parcel, index) => {
          const statusConfig = {
            'In Progress': {
              type: 'info',
              icon: 'fas fa-shipping-fast',
              message: `Shipment ${parcel.trackingId} is in transit to ${this.getLocationName(parcel.destination)}`
            },
            'Delivered': {
              type: 'success',
              icon: 'fas fa-check-circle',
              message: `Shipment ${parcel.trackingId} has been delivered successfully`
            },
            'Pending': {
              type: 'warning',
              icon: 'fas fa-clock',
              message: `Shipment ${parcel.trackingId} is awaiting processing`
            }
          };

          const config = statusConfig[parcel.status] || statusConfig.Pending;

          return {
            id: index + 1,
            type: config.type,
            icon: config.icon,
            message: config.message,
            time: this.formatRelativeTime(parcel.createdDate)
          };
        });
    },

    formatRelativeTime(dateString) {
      if (!dateString) return 'Recently';
      try {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minutes ago`;
        if (diffHours < 24) return `${diffHours} hours ago`;
        if (diffDays === 1) return '1 day ago';
        return `${diffDays} days ago`;
      } catch (error) {
        return 'Recently';
      }
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

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)(container);

        console.log('Basic globe created');

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
        this.pointsData = this.parcels.map(parcel => ({
          ...parcel,
          lat: parcel.currentLocation[0],
          lng: parcel.currentLocation[1],
          color: this.getStatusColor(parcel.status),
          size: 0.4,
          label: `${parcel.trackingId}: ${parcel.customer} (${parcel.status})`
        }));

        this.globe
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude(0.1)
          .pointRadius('size')
          .pointLabel('label');

        if (this.selectedParcel) {
          this.updateRouteForParcel(this.selectedParcel);
        }

        console.log('Globe data updated successfully');

      } catch (error) {
        console.error('Error updating globe data:', error);
        this.globeError = true;
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

      this.arcsData = [{
        startLat: parcel.location[0],
        startLng: parcel.location[1],
        endLat: parcel.currentLocation[0],
        endLng: parcel.currentLocation[1],
        color: '#00ff00'
      }];

      this.globe
        .arcsData(this.arcsData)
        .arcStartLat(d => d.startLat)
        .arcStartLng(d => d.startLng)
        .arcEndLat(d => d.endLat)
        .arcEndLng(d => d.endLng)
        .arcColor(d => d.color)
        .arcStroke(1.5)
        .arcAltitude(0.05);

      this.focusOnRoute(parcel.location, parcel.destination);
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      const midLat = (start[0] + end[0]) / 2;
      const midLng = (start[1] + end[1]) / 2;

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
        this.updateGlobeData();
      }
    },

    calculateProgress(parcel) {
      // Use the stored progress if available
      if (parcel.progress !== undefined) {
        return parcel.progress;
      }

      if (parcel.status === 'Delivered') return 100;
      if (parcel.status === 'Pending') return 0;

      const start = parcel.location;
      const current = parcel.currentLocation || parcel.location;
      const end = parcel.destination;

      const totalDistance = this.calculateDistance(start, end);
      const traveledDistance = this.calculateDistance(start, current);

      if (totalDistance === 0) return 0;

      return Math.min(100, (traveledDistance / totalDistance) * 100);
    },

    calculateDistance(coord1, coord2) {
      const [lat1, lon1] = coord1;
      const [lat2, lon2] = coord2;

      const R = 6371;
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
      // Find country by coordinates
      const country = countryData.find(c =>
        Math.abs(c.lat - coords[0]) < 5 && Math.abs(c.long - coords[1]) < 5
      );
      return country ? country.name : 'Unknown Location';
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
      if (!dateString) return 'N/A';
      try {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (error) {
        return 'Invalid Date';
      }
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
    },

    redirectToLogin() {
      window.location.href = '/login';
    }
  }
};
</script>

<style scoped>
/* Your existing CSS styles remain exactly the same */
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

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-spinner-large {
  width: 60px;
  height: 60px;
  border: 6px solid var(--light-pink);
  border-top: 6px solid var(--hot-pink);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

/* Error Banner */
.error-banner {
  background: var(--dark-pink);
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  justify-content: space-between;
}

.btn-retry {
  background: white;
  color: var(--hot-pink);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  background: var(--hot-pink);
  color: white;
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
  background: linear-gradient(135deg, var(--dark-pink), var(--slate-blue));
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
  margin: 0 0 0.5rem 0;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark-slate-blue);
  margin: 0 0 0.5rem 0;
  line-height: 1;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.stat-trend.up {
  color: #10b981;
}

.stat-trend.down {
  color: #ef4444;
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
  background: linear-gradient(to top, var(--light-pink), var(--pink));
  border-radius: 2px 2px 0 0;
  min-height: 2px;
}

/* Tracking Section */
.tracking-section {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  margin-bottom: 2rem;
}

.section-column {
  display: flex;
  flex-direction: column;
}

.section-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--light-pink);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  color: var(--dark-slate-blue);
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-header h3 i {
  color: var(--hot-pink);
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 0.75rem;
  color: var(--slate-blue);
}

.search-box input {
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  border: 1px solid var(--pink-grey);
  border-radius: 8px;
  font-size: 0.9rem;
  width: 200px;
  transition: border-color 0.3s ease;
}

.search-box input:focus {
  outline: none;
  border-color: var(--hot-pink);
}

.btn-icon {
  background: var(--light-pink);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--dark-slate-blue);
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: var(--hot-pink);
  color: white;
}

.card-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Globe Styles */
.globe-container {
  flex: 1;
  min-height: 400px;
  background: #000011;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
}

.globe-error,
.globe-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  background: rgba(0, 0, 17, 0.8);
}

.error-icon {
  font-size: 3rem;
  color: var(--hot-pink);
  margin-bottom: 1rem;
}

.globe-loading .loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.selected-parcel-info {
  background: var(--light-pink);
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 1rem;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.parcel-header h4 {
  margin: 0;
  color: var(--dark-slate-blue);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-in-progress {
  background: var(--hot-pink);
  color: white;
}

.status-delivered {
  background: var(--pink);
  color: white;
}

.status-pending {
  background: var(--dark-pink);
  color: white;
}

.route-progress {
  margin-top: 1rem;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
}

.progress-percent {
  font-weight: 600;
  color: var(--hot-pink);
}

.progress-track {
  position: relative;
  height: 6px;
  background: var(--pink-grey);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  position: relative;
  height: 100%;
  width: 100%;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--dark-pink));
  border-radius: 3px;
  transition: width 0.5s ease;
}

.progress-marker {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 20px;
  height: 20px;
  background: white;
  border: 2px solid var(--hot-pink);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.6rem;
  color: var(--hot-pink);
}

.no-selection {
  text-align: center;
  padding: 2rem;
  color: var(--slate-blue);
}

.no-selection i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Parcels List */
.parcels-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 600px;
  overflow-y: auto;
}

.parcel-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--light-pink);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.parcel-item:hover {
  background: white;
  border-color: var(--hot-pink);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 66, 117, 0.2);
}

.parcel-item.active {
  background: white;
  border-color: var(--hot-pink);
  box-shadow: 0 4px 15px rgba(255, 66, 117, 0.3);
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
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.tracking-id {
  margin: 0;
  font-weight: 600;
  color: var(--dark-slate-blue);
  font-size: 1rem;
}

.parcel-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.5rem;
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
  width: 14px;
  color: var(--hot-pink);
}

.parcel-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
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
  background: linear-gradient(90deg, var(--hot-pink), var(--dark-pink));
  border-radius: 2px;
  transition: width 0.5s ease;
}

.progress-text {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--hot-pink);
  min-width: 40px;
}

.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: var(--slate-blue);
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.empty-subtext {
  font-size: 0.9rem;
  opacity: 0.7;
  margin-top: 0.5rem;
}

/* Notifications Section */
.notifications-section {
  margin-bottom: 2rem;
}

.notifications-column {
  width: 100%;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--light-pink);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.notification-item:hover {
  background: white;
  transform: translateX(5px);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
}

.notification-icon.info {
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
}

.notification-icon.success {
  background: linear-gradient(135deg, var(--pink), var(--dark-pink));
}

.notification-icon.warning {
  background: linear-gradient(135deg, var(--dark-pink), var(--slate-blue));
}

.notification-content {
  flex: 1;
}

.notification-text {
  margin: 0 0 0.25rem 0;
  color: var(--dark-slate-blue);
  font-weight: 500;
}

.notification-time {
  font-size: 0.8rem;
  color: var(--slate-blue);
}

.empty-notifications {
  text-align: center;
  padding: 2rem;
  color: var(--slate-blue);
}

.empty-notifications i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

/* Login Required */
.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
  padding: 2rem;
}

.login-message {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  max-width: 400px;
  width: 100%;
}

.message-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, var(--light-pink), var(--pink));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2rem;
  color: var(--hot-pink);
}

.login-message h2 {
  color: var(--dark-slate-blue);
  margin-bottom: 1rem;
}

.login-message p {
  color: var(--slate-blue);
  margin-bottom: 2rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 66, 117, 0.4);
}

/* Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .tracking-section {
    grid-template-columns: 1fr;
  }
  
  .parcels-column {
    order: -1;
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
  
  .tracking-section {
    gap: 1rem;
  }
  
  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .card-actions {
    justify-content: space-between;
  }
  
  .search-box input {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .main-content {
    padding: 0 0.5rem 1rem;
  }
  
  .header-background {
    padding: 1.5rem 1rem;
  }
  
  .title-main {
    font-size: 2rem;
  }
  
  .title-sub {
    font-size: 1.4rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .parcel-info {
    grid-template-columns: 1fr;
  }
}
</style>