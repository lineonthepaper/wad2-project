<template>
  <div>
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner-large"></div>
        <p>Loading your shipments...</p>
      </div>

      <div v-if="errorMessage" class="error-banner">
        <i class="fas fa-exclamation-circle"></i>
        {{ errorMessage }}
        <button @click="fetchUserShipments" class="btn-retry">Retry</button>
      </div>

      <div class="dashboard-header">
        <div class="header-background">
          <div class="header-content">
            <div class="header-text">
              <h1 class="dashboard-title">
                <span class="title-main">Parcel Tracking</span>
                <span class="title-sub">Dashboard</span>
              </h1>
              <p> Track your shipments in real-time.</p>
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

      <div class="main-content" v-if="!loading">
        <!-- Stats Overview Section -->
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

        <!-- Tracking Section -->
        <section class="tracking-section">
          <div class="section-column globe-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-globe-americas"></i> Live Tracking Map</h3>
                <div class="card-actions">
                  <span v-if="globeUpdateTimeout" class="globe-updating-indicator">
                    <i class="fas fa-circle-notch fa-spin"></i> Updating...
                  </span>
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <button class="btn-icon" v-if="selectedParcel" @click="clearRoute" title="Clear Route">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div ref="globeContainer" class="globe-container" style="width: 100%; height: 400px;">
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
                    <button class="btn-retry" @click="forceReinit" style="margin-top: 1rem;">Force Initialize</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="section-column parcels-column">
            <div class="section-card">
              <div class="card-header">
                <div class="card-actions">
                  <div class="search-box">
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="Search shipments..."
                      class="search-input"
                    >
                    <i class="fas fa-search"></i>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="parcels-list">
                  <div
                    v-for="parcel in filteredParcels"
                    :key="parcel.id"
                    class="parcel-item"
                    :class="{ 'active': selectedParcel && selectedParcel.id === parcel.id }"
                    @click="selectParcel(parcel)"
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

        <!-- Charts Section - Collapsible -->
        <section class="charts-section">
          <div class="charts-header" @click="toggleCharts">
            <h3>
              <i class="fas fa-chart-bar"></i>
              Analytics & Insights
              <i class="fas" :class="chartsExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </h3>
            <span class="charts-toggle">
              {{ chartsExpanded ? 'Hide Charts' : 'Show Charts' }}
            </span>
          </div>

          <div class="charts-content" :class="{ 'expanded': chartsExpanded }">
            <!-- Time Filter Controls -->
            <div class="time-filter-controls">
              <div class="filter-group">
                <label>Time Range:</label>
                <div class="time-buttons">
                  <button 
                    v-for="period in timePeriods" 
                    :key="period.value"
                    @click="setTimeRange(period.value)"
                    :class="{ 'active': selectedTimeRange === period.value }"
                    class="time-btn"
                  >
                    {{ period.label }}
                  </button>
                </div>
              </div>

              <div class="filter-group">
                <label>Custom Range:</label>
                <div class="date-range">
                  <input 
                    type="date" 
                    v-model="customStartDate"
                    class="date-input"
                  >
                  <span>to</span>
                  <input 
                    type="date" 
                    v-model="customEndDate"
                    class="date-input"
                  >
                  <button @click="applyCustomRange" class="apply-btn">
                    Apply
                  </button>
                </div>
              </div>

              <div class="filter-group">
                <label>Data Granularity:</label>
                <select v-model="dataGranularity" @change="updateCharts" class="granularity-select">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
              <div class="chart-container">
                <div class="chart-header">
                  <h4>Shipment Status Distribution</h4>
                  <span class="chart-period">{{ timeRangeLabel }}</span>
                </div>
                <canvas ref="statusChart" class="chart-canvas"></canvas>
              </div>

              <div class="chart-container">
                <div class="chart-header">
                  <h4>Monthly Trends</h4>
                  <span class="chart-period">{{ timeRangeLabel }}</span>
                </div>
                <canvas ref="trendsChart" class="chart-canvas"></canvas>
              </div>

              <div class="chart-container">
                <div class="chart-header">
                  <h4>Service Type Usage</h4>
                  <span class="chart-period">{{ timeRangeLabel }}</span>
                </div>
                <canvas ref="serviceChart" class="chart-canvas"></canvas>
              </div>

              <div class="chart-container">
                <div class="chart-header">
                  <h4>Delivery Performance</h4>
                  <span class="chart-period">{{ timeRangeLabel }}</span>
                </div>
                <div class="gauge-wrapper">
                  <canvas ref="gaugeChart" class="chart-canvas"></canvas>
                  <div class="gauge-center">
                    <span class="gauge-value">{{ deliveryRate }}%</span>
                    <span class="gauge-label">On Time</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- History Button -->
        <div class="text-center my-3">
          <button @click="viewHistory"
                  style="background: #ff6b9d; border: none; border-radius: 8px; padding: 15px 25px; color: white; transition: all 0.3s ease; cursor: pointer;"
                  onmouseover="this.style.background='#ff4d8d'"
                  onmouseout="this.style.background='#ff6b9d'">
            <h3 class="mb-0">Shipment History</h3>
            <p class="mb-0">Click to view more</p>
          </button>
        </div>

        <!-- Notifications Section -->
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
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: "EnhancedParcelDashboard",
  data() {
    const today = new Date();
    const oneMonthAgo = new Date(today);
    oneMonthAgo.setMonth(today.getMonth() - 1);
    
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
      refreshInterval: null,
      usingFallbackData: false,
      routeData: null,
      routeLoading: false,
      routeError: null,
      globeUpdateTimeout: null,
      charts: {},
      
      // Charts & Filtering
      chartsExpanded: false,
      selectedTimeRange: '1m',
      dataGranularity: 'monthly',
      customStartDate: oneMonthAgo.toISOString().split('T')[0],
      customEndDate: today.toISOString().split('T')[0],
      timePeriods: [
        { label: '1 Week', value: '1w' },
        { label: '1 Month', value: '1m' },
        { label: '3 Months', value: '3m' },
        { label: '6 Months', value: '6m' },
        { label: '1 Year', value: '1y' },
        { label: 'All Time', value: 'all' }
      ]
    };
  },
  computed: {
    totalShipments() {
      return this.stats.inProgress + this.stats.delivered + this.stats.pending;
    },
    filteredParcels() {
      if (!this.searchQuery.trim()) return this.parcels;

      const query = this.searchQuery.trim().toLowerCase();
      return this.parcels.filter(parcel => {
        const trackingMatch = parcel.trackingId.toLowerCase().includes(query);
        const customerMatch = parcel.customer && parcel.customer.toLowerCase().includes(query);
        const locationMatch = this.getLocationName(parcel.currentLocation || parcel.location).toLowerCase().includes(query);
        const rawTrackingMatch = parcel.trackingId.replace('TRK-', '').toLowerCase().includes(query.replace('trk-', ''));
        const mailIdMatch = parcel.mailId && parcel.mailId.toString().includes(query);
        return trackingMatch || customerMatch || locationMatch || rawTrackingMatch || mailIdMatch;
      });
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
    },
    hasRouteData() {
      return this.routeData && this.routeData.waypoints && this.routeData.waypoints.length > 0;
    },
    deliveryRate() {
      const total = this.totalShipments;
      const delivered = this.stats.delivered;
      return total > 0 ? Math.round((delivered / total) * 100) : 0;
    },
    serviceStats() {
      const services = {};
      this.parcels.forEach(parcel => {
        const serviceName = parcel.service?.name || 'Unknown';
        services[serviceName] = (services[serviceName] || 0) + 1;
      });
      return services;
    },
    timeRangeLabel() {
      const period = this.timePeriods.find(p => p.value === this.selectedTimeRange);
      return period ? period.label : 'Custom Range';
    },
    filteredParcelsByTime() {
      const now = new Date();
      let startDate = new Date();

      switch (this.selectedTimeRange) {
        case '1w':
          startDate.setDate(now.getDate() - 7);
          break;
        case '1m':
          startDate.setMonth(now.getMonth() - 1);
          break;
        case '3m':
          startDate.setMonth(now.getMonth() - 3);
          break;
        case '6m':
          startDate.setMonth(now.getMonth() - 6);
          break;
        case '1y':
          startDate.setFullYear(now.getFullYear() - 1);
          break;
        case 'custom':
          startDate = new Date(this.customStartDate);
          const endDate = new Date(this.customEndDate);
          return this.parcels.filter(parcel => {
            const parcelDate = new Date(parcel.createdDate);
            return parcelDate >= startDate && parcelDate <= endDate;
          });
        case 'all':
        default:
          return this.parcels;
      }

      return this.parcels.filter(parcel => {
        const parcelDate = new Date(parcel.createdDate);
        return parcelDate >= startDate;
      });
    }
  },
  mounted() {
    this.checkAuthentication();
    if (this.isAuthenticated) {
      this.initializeDashboard();
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
    if (this.globeUpdateTimeout) {
      clearTimeout(this.globeUpdateTimeout);
    }
    if (this.globe) {
      this.globe = null;
    }
    // Destroy all charts
    Object.values(this.charts).forEach(chart => {
      if (chart) chart.destroy();
    });
    window.removeEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  methods: {
    viewHistory() {
      this.$router.push('/history');
    },
    
    // Charts Methods
    toggleCharts() {
      this.chartsExpanded = !this.chartsExpanded;
      if (this.chartsExpanded) {
        this.$nextTick(() => {
          this.initCharts();
        });
      }
    },

    setTimeRange(range) {
      this.selectedTimeRange = range;
      this.updateCharts();
    },

    applyCustomRange() {
      this.selectedTimeRange = 'custom';
      this.updateCharts();
    },

    updateCharts() {
      // Destroy existing charts
      Object.values(this.charts).forEach(chart => {
        if (chart) chart.destroy();
      });
      
      // Reinitialize charts with filtered data
      this.initCharts();
    },

    initCharts() {
      this.initStatusChart();
      this.initTrendsChart();
      this.initServiceChart();
      this.initGaugeChart();
    },

    initStatusChart() {
      const ctx = this.$refs.statusChart;
      if (!ctx) return;

      const filteredData = this.getFilteredStats();

      this.charts.status = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['In Progress', 'Delivered', 'Pending'],
          datasets: [{
            data: [filteredData.inProgress, filteredData.delivered, filteredData.pending],
            backgroundColor: ['#ffa500', '#00ff88', '#ff4275'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.raw || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = Math.round((value / total) * 100);
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    },

    initTrendsChart() {
      const ctx = this.$refs.trendsChart;
      if (!ctx) return;

      const trendData = this.generateTrendData();

      this.charts.trends = new Chart(ctx, {
        type: 'line',
        data: {
          labels: trendData.labels,
          datasets: [
            {
              label: 'In Progress',
              data: trendData.inProgress,
              borderColor: '#ffa500',
              backgroundColor: 'rgba(255, 165, 0, 0.1)',
              tension: 0.4,
              fill: true
            },
            {
              label: 'Delivered',
              data: trendData.delivered,
              borderColor: '#00ff88',
              backgroundColor: 'rgba(0, 255, 136, 0.1)',
              tension: 0.4,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Shipments'
              }
            }
          }
        }
      });
    },

    initServiceChart() {
      const ctx = this.$refs.serviceChart;
      if (!ctx) return;

      const serviceStats = {};
      this.filteredParcelsByTime.forEach(parcel => {
        const serviceName = parcel.service?.name || 'Unknown';
        serviceStats[serviceName] = (serviceStats[serviceName] || 0) + 1;
      });

      const serviceNames = Object.keys(serviceStats);
      const serviceCounts = Object.values(serviceStats);
      
      this.charts.service = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: serviceNames,
          datasets: [{
            label: 'Number of Shipments',
            data: serviceCounts,
            backgroundColor: ['#ff4275', '#ff759e', '#ff9096', '#ffa500', '#00ff88'],
            borderColor: '#fff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    },

    initGaugeChart() {
      const ctx = this.$refs.gaugeChart;
      if (!ctx) return;

      const filteredData = this.getFilteredStats();
      const deliveryRate = filteredData.total > 0 ? 
        Math.round((filteredData.delivered / filteredData.total) * 100) : 0;

      this.charts.gauge = new Chart(ctx, {
        type: 'doughnut',
        data: {
          datasets: [{
            data: [deliveryRate, 100 - deliveryRate],
            backgroundColor: [
              deliveryRate >= 80 ? '#00ff88' : 
              deliveryRate >= 60 ? '#ffa500' : '#ff4275',
              '#f0f0f0'
            ],
            borderWidth: 0,
            circumference: 180,
            rotation: 270
          }]
        },
        options: {
          responsive: true,
          cutout: '70%',
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
          }
        }
      });
    },

    getFilteredStats() {
      const filtered = this.filteredParcelsByTime;
      return {
        inProgress: filtered.filter(p => p.status === 'In Progress').length,
        delivered: filtered.filter(p => p.status === 'Delivered').length,
        pending: filtered.filter(p => p.status === 'Pending').length,
        total: filtered.length
      };
    },

    generateTrendData() {
      const now = new Date();
      let labels = [];
      let inProgressData = [];
      let deliveredData = [];

      switch (this.dataGranularity) {
        case 'daily':
          labels = Array.from({length: 7}, (_, i) => {
            const date = new Date(now);
            date.setDate(date.getDate() - (6 - i));
            return date.toLocaleDateString('en-US', { weekday: 'short' });
          });
          break;
        case 'weekly':
          labels = Array.from({length: 4}, (_, i) => `Week ${i + 1}`);
          break;
        case 'monthly':
        default:
          labels = Array.from({length: 6}, (_, i) => {
            const date = new Date(now);
            date.setMonth(date.getMonth() - (5 - i));
            return date.toLocaleDateString('en-US', { month: 'short' });
          });
      }

      // Generate sample data based on current filtered stats
      const baseStats = this.getFilteredStats();
      inProgressData = labels.map(() => Math.max(0, baseStats.inProgress + Math.floor(Math.random() * 3) - 1));
      deliveredData = labels.map(() => Math.max(0, baseStats.delivered + Math.floor(Math.random() * 5) - 2));

      return {
        labels,
        inProgress: inProgressData,
        delivered: deliveredData
      };
    },

    // Rest of your existing methods remain the same...
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.user.email = user.email || user.display_name || 'User';
          this.isAuthenticated = true;
        } catch (error) {
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
      }
    },

    handleLoginStatusChange() {
      this.checkAuthentication();
      if (this.isAuthenticated) {
        this.initializeDashboard();
      } else {
        this.parcels = [];
        this.notifications = [];
        this.stats = { inProgress: 0, delivered: 0, pending: 0 };
        this.usingFallbackData = false;
        this.routeData = null;
      }
    },

    async initializeDashboard() {
      await this.fetchUserShipments();
      await this.$nextTick();
      await new Promise(resolve => setTimeout(resolve, 800));
      await this.initGlobe();
    },

    async fetchUserShipments() {
      this.loading = true;
      this.errorMessage = "";
      this.usingFallbackData = false;

      try {
        const response = await axios.post('/api/dashboard.php', {
          method: 'getAllMailByCustomerEmail',
          email: this.user.email
        });

        if (response.data.success) {
          if (response.data.note) {
            this.usingFallbackData = true;
          }

          this.parcels = this.transformShipmentData(response.data.shipments);
          this.updateStats();
          this.generateNotifications();

          if (this.globeInitialized && this.globe) {
            this.updateGlobeData();
          }
        } else {
          this.useFallbackData();
        }
      } catch (error) {
        this.useFallbackData();
      } finally {
        this.loading = false;
      }
    },

    useFallbackData() {
      this.usingFallbackData = true;
      this.errorMessage = 'Connected to demo data. Real shipments will appear here once you create them.';

      const exampleData = this.getExampleShipments(this.user.email);
      this.parcels = this.transformShipmentData(exampleData);
      this.updateStats();
      this.generateNotifications();

      if (this.globeInitialized && this.globe) {
        this.updateGlobeData();
      }
    },

    getExampleShipments(customerEmail) {
      return [
        {
          'mailId': 1001,
          'customerEmail': customerEmail,
          'senderAddressId': 1,
          'recipientAddressId': 2,
          'mailItems': [
            {
              'itemId': 1,
              'itemDescription': 'Electronics Package',
              'declaredValue': 250.00,
              'itemWeight': 1.5,
              'itemQuantity': 1
            }
          ],
          'parcelLength': 30.0,
          'parcelWidth': 20.0,
          'parcelHeight': 15.0,
          'service': {
            'name': 'Registered Package',
            'type': 'Packets',
            'zone': 3
          },
          'totalWeight': 1.5,
          'totalValue': 250.00,
          'hasBeenPaid': true,
          'trackingNumber': 'TRK784231',
          'createdDate': new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
          'senderAddress': {
            'name': 'John Doe',
            'countryCode': 'SG'
          },
          'recipientAddress': {
            'name': 'Sarah Wilson',
            'countryCode': 'US'
          },
          'status': 'in_transit',
          'expectedDelivery': new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        },
        {
          'mailId': 1002,
          'customerEmail': customerEmail,
          'senderAddressId': 1,
          'recipientAddressId': 3,
          'mailItems': [
            {
              'itemId': 2,
              'itemDescription': 'Documents',
              'declaredValue': 50.00,
              'itemWeight': 0.5,
              'itemQuantity': 1
            }
          ],
          'parcelLength': 35.0,
          'parcelWidth': 25.0,
          'parcelHeight': 2.0,
          'service': {
            'name': 'Registered Mail',
            'type': 'Documents',
            'zone': 2
          },
          'totalWeight': 0.5,
          'totalValue': 50.00,
          'hasBeenPaid': true,
          'trackingNumber': 'TRK784232',
          'createdDate': new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString(),
          'senderAddress': {
            'name': 'John Doe',
            'countryCode': 'SG'
          },
          'recipientAddress': {
            'name': 'Mike Chen',
            'countryCode': 'MY'
          },
          'status': 'delivered',
          'expectedDelivery': new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        }
      ];
    },

    transformShipmentData(shipments) {
      if (!shipments || !Array.isArray(shipments)) {
        return [];
      }

      return shipments.map(shipment => {
        try {
          let senderCoords = this.getCountryCoordinates(shipment.senderAddress?.countryCode || 'SG');
          let recipientCoords = this.getCountryCoordinates(shipment.recipientAddress?.countryCode || 'US');

          if (!Array.isArray(senderCoords) || senderCoords.some(isNaN)) {
            senderCoords = [1.3521, 103.8198];
          }

          if (!Array.isArray(recipientCoords) || recipientCoords.some(isNaN)) {
            recipientCoords = [39.8283, -98.5795];
          }

          let currentLocation = senderCoords;
          const progress = this.calculateProgressFromStatus(shipment.status);

          if (progress > 0 && progress < 100) {
            currentLocation = [
              senderCoords[0] + (recipientCoords[0] - senderCoords[0]) * (progress / 100),
              senderCoords[1] + (recipientCoords[1] - senderCoords[1]) * (progress / 100)
            ];
          } else if (progress >= 100) {
            currentLocation = recipientCoords;
          }

          let trackingId = `TRK-${shipment.mailId.toString().padStart(6, '0')}`;
          if (shipment.trackingNumber && shipment.trackingNumber !== 0) {
            trackingId = `TRK-${shipment.trackingNumber}`;
          }

          return {
            id: shipment.mailId,
            mailId: shipment.mailId,
            trackingId: trackingId,
            customer: shipment.senderAddress?.name || this.user.email,
            status: this.mapApiStatus(shipment.status),
            expectedDelivery: shipment.expectedDelivery,
            location: senderCoords,
            currentLocation: currentLocation,
            destination: recipientCoords,
            progress: progress,
            service: shipment.service,
            totalWeight: shipment.totalWeight,
            totalValue: shipment.totalValue,
            hasBeenPaid: shipment.hasBeenPaid,
            createdDate: shipment.createdDate,
            rawData: shipment,
            senderAddress: shipment.senderAddress,
            recipientAddress: shipment.recipientAddress
          };
        } catch (error) {
          return null;
        }
      }).filter(parcel => parcel !== null);
    },

    getCountryCoordinates(countryCode) {
      const code = countryCode === 'UK' ? 'GB' : countryCode;
      const country = countryData.find(c => c.code2 === code);
      if (country) {
        return [country.lat, country.long];
      }
      return [1.28478, 103.776222];
    },

    calculateProgressFromStatus(status) {
      const progressMap = {
        'pending': 0,
        'in_transit': 50,
        'delivered': 100
      };
      return progressMap[status] || 0;
    },

    mapApiStatus(apiStatus) {
      const statusMap = {
        'pending': 'Pending',
        'in_transit': 'In Progress',
        'delivered': 'Delivered'
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
        const container = this.$refs.globeContainer;
        if (!container) {
          throw new Error('Globe container not found');
        }

        const width = container.clientWidth || 800;
        const height = container.clientHeight || 400;

        if (width === 0 || height === 0) {
          throw new Error('Container has invalid dimensions');
        }

        container.innerHTML = '';

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)
          (container);

        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });
        this.globe.controls().enableZoom = true;
        this.globe.controls().autoRotate = false;

        this.globeInitialized = true;
        this.globeError = false;

        if (this.parcels && this.parcels.length > 0) {
          await this.$nextTick();
          this.updateGlobeData();

          if (this.selectedParcel) {
            await this.$nextTick();
            this.updateGlobeRoute(this.selectedParcel);
          }
        }
      } catch (error) {
        this.globeError = true;
        this.globeInitialized = false;
      }
    },

    updateGlobeData() {
      if (!this.globe || !this.globeInitialized) return;
      if (!this.parcels || this.parcels.length === 0) {
        this.globe.pointsData([]);
        this.globe.arcsData([]);
        return;
      }

      try {
        this.pointsData = this.parcels.map(parcel => ({
          id: parcel.id,
          lat: parcel.currentLocation[0],
          lng: parcel.currentLocation[1],
          color: this.getStatusColor(parcel.status),
          size: 0.4,
          altitude: 0.01,
          label: `${parcel.trackingId}: ${parcel.status}`
        }));

        this.globe
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude('altitude')
          .pointRadius('size')
          .pointLabel('label')
          .pointsMerge(false);
      } catch (error) {
        console.error('Error updating globe data:', error);
      }
    },

    selectParcel(parcel) {
      this.selectedParcel = parcel;

      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
      }

      if (this.globe && this.globeInitialized) {
        this.globeUpdateTimeout = setTimeout(() => {
          requestAnimationFrame(() => {
            this.updateGlobeRoute(parcel);
          });
        }, 100);
      }
    },

    updateGlobeRoute(parcel) {
      if (!this.globe || !this.globeInitialized) return;

      try {
        this.arcsData = [];

        const mainRoute = {
          startLat: parcel.location[0],
          startLng: parcel.location[1],
          endLat: parcel.destination[0],
          endLng: parcel.destination[1],
          color: '#ff4275'
        };

        this.arcsData.push(mainRoute);

        if (parcel.status === 'In Progress' && parcel.progress > 0 && parcel.progress < 100) {
          const progressRoute = {
            startLat: parcel.location[0],
            startLng: parcel.location[1],
            endLat: parcel.currentLocation[0],
            endLng: parcel.currentLocation[1],
            color: '#00ff88'
          };
          this.arcsData.push(progressRoute);
        }

        this.globe
          .arcsData(this.arcsData)
          .arcStartLat(d => d.startLat)
          .arcStartLng(d => d.startLng)
          .arcEndLat(d => d.endLat)
          .arcEndLng(d => d.endLng)
          .arcColor(d => d.color)
          .arcStroke(2)
          .arcDashLength(0.4)
          .arcDashGap(0.2)
          .arcDashAnimateTime(2000)
          .arcAltitude(0.1);

        this.focusOnRoute(parcel.location, parcel.destination);
        this.globeUpdateTimeout = null;
      } catch (error) {
        this.globeUpdateTimeout = null;
      }
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      try {
        const midLat = (start[0] + end[0]) / 2;
        const midLng = (start[1] + end[1]) / 2;

        const distance = this.calculateDistance(start, end);
        const altitude = Math.min(2.5, Math.max(1.5, distance / 5000));

        this.globe.pointOfView({
          lat: midLat,
          lng: midLng,
          altitude: altitude
        }, 1000);
      } catch (error) {
        console.error('Error focusing on route:', error);
      }
    },

    clearRoute() {
      this.selectedParcel = null;
      this.routeData = null;
      this.routeError = null;

      if (this.globe && this.globeInitialized) {
        try {
          this.arcsData = [];
          this.globe.arcsData(this.arcsData);
          this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 }, 1000);
          this.updateGlobeData();
        } catch (error) {
          console.error('Error clearing route:', error);
        }
      }

      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
        this.globeUpdateTimeout = null;
      }
    },

    calculateDistance(start, end) {
      const latDiff = Math.abs(start[0] - end[0]);
      const lngDiff = Math.abs(start[1] - end[1]);
      return Math.sqrt(latDiff * latDiff + lngDiff * lngDiff);
    },

    getStatusColor(status) {
      const colors = {
        'In Progress': '#ffa500',
        'Delivered': '#00ff88',
        'Pending': '#ff4275'
      };
      return colors[status] || '#ff4275';
    },

    getLocationName(coords) {
      if (!Array.isArray(coords) || coords.length < 2) {
        return 'Unknown Location';
      }

      const closestCountry = countryData.reduce((closest, country) => {
        const distance = Math.sqrt(
          Math.pow(country.lat - coords[0], 2) +
          Math.pow(country.long - coords[1], 2)
        );
        return !closest || distance < closest.distance ? { country, distance } : closest;
      }, null);

      return closestCountry ? closestCountry.country.name : 'Unknown Location';
    },

    calculateProgress(parcel) {
      return parcel.progress || 0;
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
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

      if (this.globe) {
        this.globe = null;
      }

      setTimeout(() => {
        this.initGlobe();
      }, 500);
    },

    redirectToLogin() {
      window.dispatchEvent(new CustomEvent('showLoginModal'));
    }
  }
};
</script>

<style scoped>
/* Your existing CSS remains the same, add these new styles: */

/* Charts Section */
.charts-section {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  margin-bottom: 2rem;
  overflow: hidden;
}

.charts-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: var(--light-pink);
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.charts-header:hover {
  background: var(--pink-grey);
}

.charts-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.charts-toggle {
  color: var(--hot-pink);
  font-weight: 600;
  font-size: 0.9rem;
}

.charts-content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.5s ease;
}

.charts-content.expanded {
  max-height: 1200px;
}

/* Time Filter Controls */
.time-filter-controls {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
  background: var(--light-pink);
  border-bottom: 1px solid var(--pink-grey);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 600;
  color: var(--dark-slate-blue);
  font-size: 0.9rem;
}

.time-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.time-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--pink-grey);
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.3s ease;
}

.time-btn:hover {
  border-color: var(--hot-pink);
}

.time-btn.active {
  background: var(--hot-pink);
  color: white;
  border-color: var(--hot-pink);
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.date-input {
  padding: 0.5rem;
  border: 1px solid var(--pink-grey);
  border-radius: 6px;
  font-size: 0.8rem;
}

.apply-btn {
  padding: 0.5rem 1rem;
  background: var(--hot-pink);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: background-color 0.3s ease;
}

.apply-btn:hover {
  background: var(--dark-pink);
}

.granularity-select {
  padding: 0.5rem;
  border: 1px solid var(--pink-grey);
  border-radius: 6px;
  background: white;
  font-size: 0.8rem;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.chart-container {
  background: var(--light-pink);
  border-radius: 12px;
  padding: 1rem;
  position: relative;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.chart-header h4 {
  margin: 0;
  font-size: 1rem;
  color: var(--dark-slate-blue);
}

.chart-period {
  font-size: 0.8rem;
  color: var(--slate-blue);
  background: white;
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
}

.chart-canvas {
  width: 100% !important;
  height: 250px !important;
}

.gauge-wrapper {
  position: relative;
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gauge-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.gauge-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark-slate-blue);
}

.gauge-label {
  display: block;
  font-size: 0.9rem;
  color: var(--slate-blue);
  margin-top: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .time-filter-controls {
    grid-template-columns: 1fr;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .date-range {
    flex-direction: column;
    align-items: stretch;
  }
  
  .charts-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .time-buttons {
    justify-content: center;
  }
  
  .chart-header {
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
  }
}

/* Rest of your existing CSS remains unchanged */
/* ... (all your previous CSS styles) ... */
</style>