<template>
  <div>
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <div class="dashboard-container">
        <div v-if="loading" class="loading-overlay">
          <div class="loading-spinner-large"></div>
          <p>Loading dashboard data...</p>
        </div>

        <div v-if="errorMessage" class="error-banner">
          <i class="fas fa-exclamation-circle"></i>
          {{ errorMessage }}
          <button @click="loadShipmentData" class="btn-retry">Retry</button>
        </div>

        <div class="dashboard-header">
          <div class="header-background">
            <div class="header-content">
              <div class="header-text">
                <h1 class="dashboard-title">
                  <span class="title-main">Parcel Tracking</span>
                  <span class="title-sub">Analytics</span>
                </h1>
                <p>Track your shipments in real-time.</p>
              </div>
              <div class="header-stats">
                <div class="header-stat">
                  <span class="stat-value">{{ summaryStats.totalShipments }}</span>
                  <span class="stat-label">Total Shipments</span>
                </div>
                <div class="header-stat">
                  <span class="stat-value">{{ summaryStats.inTransit }}</span>
                  <span class="stat-label">In Transit</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="main-content" v-if="!loading">
          <section class="stats-overview">
            <div class="stats-grid">
              <div class="stat-card stat-total">
                <div class="stat-content">
                  <div class="stat-icon">📦</div>
                  <div class="stat-data">
                    <h3 class="stat-title">Total Shipments</h3>
                    <div class="stat-number">{{ summaryStats.totalShipments }}</div>
                  </div>
                </div>
                <div class="stat-chart">
                  <div class="mini-chart">
                    <div class="chart-bar" :style="{ height: '70%' }"></div>
                    <div class="chart-bar" :style="{ height: '50%' }"></div>
                    <div class="chart-bar" :style="{ height: '80%' }"></div>
                    <div class="chart-bar" :style="{ height: '60%' }"></div>
                  </div>
                </div>
              </div>

              <div class="stat-card stat-delivered">
                <div class="stat-content">
                  <div class="stat-icon">✅</div>
                  <div class="stat-data">
                    <h3 class="stat-title">Delivered</h3>
                    <div class="stat-number">{{ summaryStats.delivered }}</div>
                  </div>
                </div>
                <div class="stat-chart">
                  <div class="mini-chart">
                    <div class="chart-bar" :style="{ height: '90%' }"></div>
                    <div class="chart-bar" :style="{ height: '85%' }"></div>
                    <div class="chart-bar" :style="{ height: '95%' }"></div>
                    <div class="chart-bar" :style="{ height: '88%' }"></div>
                  </div>
                </div>
              </div>

              <div class="stat-card stat-in-progress">
                <div class="stat-content">
                  <div class="stat-icon">🚚</div>
                  <div class="stat-data">
                    <h3 class="stat-title">In Transit</h3>
                    <div class="stat-number">{{ summaryStats.inTransit }}</div>
                  </div>
                </div>
                <div class="stat-chart">
                  <div class="mini-chart">
                    <div class="chart-bar" :style="{ height: '60%' }"></div>
                    <div class="chart-bar" :style="{ height: '75%' }"></div>
                    <div class="chart-bar" :style="{ height: '55%' }"></div>
                    <div class="chart-bar" :style="{ height: '70%' }"></div>
                  </div>
                </div>
              </div>

              <div class="stat-card stat-pending">
                <div class="stat-content">
                  <div class="stat-icon">⏰</div>
                  <div class="stat-data">
                    <h3 class="stat-title">Pending</h3>
                    <div class="stat-number">{{ summaryStats.pending }}</div>
                  </div>
                </div>
                <div class="stat-chart">
                  <div class="mini-chart">
                    <div class="chart-bar" :style="{ height: '40%' }"></div>
                    <div class="chart-bar" :style="{ height: '35%' }"></div>
                    <div class="chart-bar" :style="{ height: '45%' }"></div>
                    <div class="chart-bar" :style="{ height: '30%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <div class="filter-section">
            <div class="filter-group">
              <label>Time Range:</label>
              <select v-model="selectedTimeRange" @change="updateCharts" class="filter-select">
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Status Filter:</label>
              <select v-model="statusFilter" @change="updateCharts" class="filter-select">
                <option value="all">All Statuses</option>
                <option value="delivered">Delivered</option>
                <option value="in_transit">In Transit</option>
                <option value="pending">Pending</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Service Type:</label>
              <select v-model="serviceFilter" @change="updateCharts" class="filter-select">
                <option value="all">All Services</option>
                <option value="Registered Package">Registered Package</option>
                <option value="Registered Mail">Registered Mail</option>
                <option value="Standard">Standard</option>
              </select>
            </div>
          </div>

          <div class="charts-controls">
            <div class="layout-selector">
              <div class="layout-title">Layout:</div>
              <button class="layout-btn" :class="{ active: layout === '2x2' }" @click="layout = '2x2'" title="2×2 Grid">
                <div class="layout-icon">
                  <div class="grid-2x2">
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                  </div>
                </div>
              </button>
              <button class="layout-btn" :class="{ active: layout === '1x4' }" @click="layout = '1x4'" title="1×4 Row">
                <div class="layout-icon">
                  <div class="grid-1x4">
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                  </div>
                </div>
              </button>
              <button class="layout-btn" :class="{ active: layout === '4x1' }" @click="layout = '4x1'" title="4×1 Column">
                <div class="layout-icon">
                  <div class="grid-4x1">
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                    <div class="grid-cell"></div>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="charts-section" :class="`layout-${layout}`">
            <div class="chart-container" :class="`chart-${layout}`">
              <div class="chart-header">
                <h4>📊 Shipment Status</h4>
                <span class="chart-period">{{ timeRangeLabel }}</span>
              </div>
              <div class="chart-wrapper">
                <div ref="statusChart" class="chart-apex"></div>
              </div>
            </div>

            <div class="chart-container" :class="`chart-${layout}`">
              <div class="chart-header">
                <h4>📈 Shipment Trends</h4>
                <span class="chart-period">{{ timeRangeLabel }}</span>
              </div>
              <div class="chart-wrapper">
                <div ref="trendsChart" class="chart-apex"></div>
              </div>
            </div>

            <div class="chart-container" :class="`chart-${layout}`">
              <div class="chart-header">
                <h4>📋 Service Usage</h4>
                <span class="chart-period">{{ timeRangeLabel }}</span>
              </div>
              <div class="chart-wrapper">
                <div ref="serviceChart" class="chart-apex"></div>
              </div>
            </div>

            <div class="chart-container" :class="`chart-${layout}`">
              <div class="chart-header">
                <h4>🎯 Delivery Performance</h4>
                <span class="chart-period">{{ timeRangeLabel }}</span>
              </div>
              <div class="chart-wrapper">
                <div class="performance-gauge">
                  <div class="gauge-circle" :style="gaugeStyle">
                    <div class="gauge-center">
                      <span class="gauge-value">{{ deliveryRate }}%</span>
                      <span class="gauge-label">Success Rate</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="login-required">
      <div class="login-message">
        <div class="message-icon">
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to access your parcel tracking dashboard</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            Go to Login
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "EnhancedAnalyticsDashboard",
  data() {
    return {
      isAuthenticated: false,
      user: {
        email: ''
      },
      shipments: [],
      loading: false,
      errorMessage: "",
      selectedTimeRange: '30d',
      statusFilter: 'all',
      serviceFilter: 'all',
      layout: '2x2',
      charts: {},
      usingFallbackData: false
    };
  },
  computed: {
    timeRangeLabel() {
      const labels = {
        '7d': 'Last 7 Days',
        '30d': 'Last 30 Days',
        '90d': 'Last 90 Days'
      };
      return labels[this.selectedTimeRange] || 'Last 30 Days';
    },
    summaryStats() {
      const filtered = this.filteredShipments;
      return {
        totalShipments: filtered.length,
        delivered: filtered.filter(s => s.status === 'delivered').length,
        inTransit: filtered.filter(s => s.status === 'in_transit').length,
        pending: filtered.filter(s => s.status === 'pending').length
      };
    },
    deliveryRate() {
      const delivered = this.summaryStats.delivered;
      const total = this.summaryStats.totalShipments;
      return total > 0 ? Math.round((delivered / total) * 100) : 0;
    },
    gaugeStyle() {
      const degree = (this.deliveryRate / 100) * 180;
      return {
        background: `conic-gradient(var(--hot-pink) 0deg, var(--dark-pink) ${degree}deg, var(--light-pink) ${degree}deg, var(--light-pink) 180deg)`
      };
    },
    filteredShipments() {
      let filtered = this.shipments;
      if (this.statusFilter !== 'all') {
        filtered = filtered.filter(shipment => shipment.status === this.statusFilter);
      }
      if (this.serviceFilter !== 'all') {
        filtered = filtered.filter(shipment => shipment.service?.name === this.serviceFilter);
      }
      return filtered;
    }
  },
  mounted() {
    this.checkAuthentication();
    if (this.isAuthenticated) {
      this.initializeDashboard();
    }
    window.addEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  beforeUnmount() {
    this.destroyCharts();
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
        this.shipments = [];
        this.usingFallbackData = false;
      }
    },
    async initializeDashboard() {
      await this.loadShipmentData();
      await this.$nextTick();
      await this.initCharts();
    },
    async loadShipmentData() {
      this.loading = true;
      this.errorMessage = "";
      this.usingFallbackData = false;

      try {
        const response = await fetch('/api/dashboard.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            method: 'getAllMailByCustomerEmail',
            email: this.user.email
          })
        });

        const data = await response.json();
        if (data.success) {
          if (data.note) {
            this.usingFallbackData = true;
          }
          this.shipments = data.shipments || [];
          this.formatShipmentDates();
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
      this.errorMessage = 'Connected to demo data. Real analytics will appear here once you create shipments.';
      this.shipments = this.getSampleShipments();
    },
    getSampleShipments() {
      return [
        {
          mailId: 1001,
          trackingNumber: 'TRK784231',
          service: { name: 'Registered Package' },
          recipientAddress: { countryCode: 'US' },
          status: 'in_transit',
          expectedDelivery: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          createdDate: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString()
        },
        {
          mailId: 1002,
          trackingNumber: 'TRK784232',
          service: { name: 'Registered Mail' },
          recipientAddress: { countryCode: 'MY' },
          status: 'delivered',
          expectedDelivery: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          createdDate: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString()
        },
        {
          mailId: 1003,
          trackingNumber: 'TRK784233',
          service: { name: 'Standard' },
          recipientAddress: { countryCode: 'UK' },
          status: 'pending',
          expectedDelivery: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          createdDate: new Date().toISOString()
        }
      ];
    },
    formatShipmentDates() {
      this.shipments.forEach(shipment => {
        if (shipment.createdDate && typeof shipment.createdDate === 'string') {
          try {
            const date = new Date(shipment.createdDate);
            if (!isNaN(date.getTime())) {
              shipment.createdDate = date.toISOString();
            } else {
              shipment.createdDate = new Date().toISOString();
            }
          } catch (error) {
            shipment.createdDate = new Date().toISOString();
          }
        } else if (!shipment.createdDate) {
          shipment.createdDate = new Date().toISOString();
        }
      });
    },
    destroyCharts() {
      Object.values(this.charts).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
          chart.destroy();
        }
      });
      this.charts = {};
    },
    updateCharts() {
      this.destroyCharts();
      this.$nextTick(() => {
        this.initCharts();
      });
    },
    async initCharts() {
      if (typeof ApexCharts === 'undefined') {
        await this.loadApexCharts();
      }
      this.initStatusChart();
      this.initTrendsChart();
      this.initServiceChart();
    },
    loadApexCharts() {
      return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
        script.onload = resolve;
        document.head.appendChild(script);
      });
    },
    initStatusChart() {
      const stats = this.summaryStats;
      const total = stats.totalShipments;

      const options = {
        series: [stats.delivered, stats.inTransit, stats.pending],
        chart: {
          type: 'donut',
          height: 280,
          fontFamily: 'inherit'
        },
        colors: ['var(--hot-pink)', 'var(--dark-pink)', 'var(--pink)'],
        labels: ['Delivered', 'In Transit', 'Pending'],
        plotOptions: {
          pie: {
            donut: {
              size: '65%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Total',
                  color: 'var(--dark-slate-blue)',
                  fontSize: '14px',
                  fontWeight: '600'
                },
                value: {
                  fontSize: '20px',
                  fontWeight: '700',
                  color: 'var(--dark-slate-blue)'
                }
              }
            }
          }
        },
        dataLabels: {
          enabled: false
        },
        legend: {
          position: 'bottom',
          horizontalAlign: 'center',
          fontSize: '12px',
          fontWeight: '600',
          labels: {
            colors: 'var(--dark-slate-blue)'
          }
        },
        initTrendsChart() {
  const trendData = this.generateTrendData();

  const options = {
    series: [
      {
        name: 'Delivered',
        data: trendData.delivered
      },
      {
        name: 'In Transit',
        data: trendData.inTransit
      },
      {
        name: 'Pending',
        data: trendData.pending
      }
    ],
    chart: {
      height: 280,
      type: 'area',
      fontFamily: 'inherit',
      toolbar: {
        show: false
      }
    },
    colors: ['var(--hot-pink)', 'var(--dark-pink)', 'var(--pink)'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.4,
        opacityTo: 0.1,
        stops: [0, 90, 100]
      }
    },
    xaxis: {
      categories: trendData.labels,
      labels: {
        style: {
          colors: 'var(--slate-blue)',
          fontSize: '11px'
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: 'var(--slate-blue)',
          fontSize: '11px'
        }
      }
    },
    grid: {
      borderColor: 'var(--pink-grey)',
      strokeDashArray: 3
    },
    legend: {
      position: 'top',
      horizontalAlign: 'left',
      fontSize: '12px',
      fontWeight: '600',
      labels: {
        colors: 'var(--dark-slate-blue)'
      }
    },
    tooltip: {
      shared: true,
      intersect: false,
      custom: function({ series, seriesIndex, dataPointIndex, w }) {
        const colors = ['#ff4275', '#ff759e', '#ff9096'];
        const seriesNames = ['Delivered', 'In Transit', 'Pending'];
        const currentDate = w.globals.categoryLabels[dataPointIndex];


        let tooltipContent = `
          <div class="custom-tooltip">
            <div class="tooltip-header" style="background: var(--hot-pink); color: white; padding: 8px 12px; border-radius: 6px 6px 0 0; font-weight: 600;">
              ${currentDate}
            </div>
            <div class="tooltip-body" style="background: white; padding: 12px; border-radius: 0 0 6px 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        `;

       
        series.forEach((s, index) => {
          const value = s[dataPointIndex];
          tooltipContent += `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 4px 0;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: ${colors[index]};"></div>
                <span style="font-size: 14px; color: var(--dark-slate-blue); font-weight: 600;">${seriesNames[index]}</span>
              </div>
              <span style="font-size: 14px; font-weight: 700; color: var(--dark-slate-blue);">${value}</span>
            </div>
          `;
        });

        // Calculate and add total
        const total = series.reduce((sum, s) => sum + s[dataPointIndex], 0);
        tooltipContent += `
            <div style="border-top: 1px solid var(--pink-grey); margin-top: 8px; padding-top: 8px;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; color: var(--dark-slate-blue); font-weight: 700;">Total</span>
                <span style="font-size: 16px; font-weight: 800; color: var(--hot-pink);">${total}</span>
              </div>
            </div>
          </div>
        </div>
        `;

        return tooltipContent;
      }
    }
  };

  this.charts.trends = new ApexCharts(this.$refs.trendsChart, options);
  this.charts.trends.render();
},
    initTrendsChart() {
      const trendData = this.generateTrendData();

      const options = {
        series: [
          {
            name: 'Delivered',
            data: trendData.delivered
          },
          {
            name: 'In Transit',
            data: trendData.inTransit
          },
          {
            name: 'Pending',
            data: trendData.pending
          }
        ],
        chart: {
          height: 280,
          type: 'area',
          fontFamily: 'inherit',
          toolbar: {
            show: false
          }
        },
        colors: ['var(--hot-pink)', 'var(--dark-pink)', 'var(--pink)'],
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
          }
        },
        xaxis: {
          categories: trendData.labels,
          labels: {
            style: {
              colors: 'var(--slate-blue)',
              fontSize: '11px'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: 'var(--slate-blue)',
              fontSize: '11px'
            }
          }
        },
        grid: {
          borderColor: 'var(--pink-grey)',
          strokeDashArray: 3
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          fontSize: '12px',
          fontWeight: '600',
          labels: {
            colors: 'var(--dark-slate-blue)'
          }
        },
        tooltip: {
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const colors = ['#ff4275', '#ff759e', '#ff9096'];
            const seriesNames = ['Delivered', 'In Transit', 'Pending'];

            return `
              <div class="custom-tooltip">
                <div class="tooltip-header" style="background: ${colors[seriesIndex]}; color: white; padding: 8px 12px; border-radius: 6px 6px 0 0; font-weight: 600;">
                  ${seriesNames[seriesIndex]}
                </div>
                <div class="tooltip-body" style="background: white; padding: 12px; border-radius: 0 0 6px 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                  <div style="font-size: 16px; font-weight: 700; color: var(--dark-slate-blue); margin-bottom: 4px;">
                    ${w.globals.categoryLabels[dataPointIndex]}
                  </div>
                  <div style="font-size: 18px; font-weight: 700; color: var(--dark-slate-blue);">${series[seriesIndex][dataPointIndex]} shipments</div>
                </div>
              </div>
            `;
          }
        }
      };

      this.charts.trends = new ApexCharts(this.$refs.trendsChart, options);
      this.charts.trends.render();
    },
    initServiceChart() {
      const serviceStats = {};
      this.filteredShipments.forEach(shipment => {
        const serviceName = shipment.service?.name || 'Unknown';
        serviceStats[serviceName] = (serviceStats[serviceName] || 0) + 1;
      });

      const options = {
        series: [{
          data: Object.values(serviceStats)
        }],
        chart: {
          type: 'bar',
          height: 280,
          fontFamily: 'inherit',
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 8,
            horizontal: true,
            distributed: true
          }
        },
        colors: ['var(--hot-pink)', 'var(--dark-pink)', 'var(--pink)', 'var(--light-pink)'],
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: Object.keys(serviceStats),
          labels: {
            style: {
              colors: 'var(--slate-blue)',
              fontSize: '11px'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: 'var(--dark-slate-blue)',
              fontSize: '12px',
              fontWeight: '600'
            }
          }
        },
        grid: {
          borderColor: 'var(--pink-grey)',
          strokeDashArray: 3
        },
        tooltip: {
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const total = series[seriesIndex].reduce((a, b) => a + b, 0);
            const percentage = total > 0 ? Math.round((series[seriesIndex][dataPointIndex] / total) * 100) : 0;
            const serviceName = w.globals.labels[dataPointIndex];

            return `
              <div class="custom-tooltip">
                <div class="tooltip-header" style="background: var(--hot-pink); color: white; padding: 8px 12px; border-radius: 6px 6px 0 0; font-weight: 600;">
                  ${serviceName}
                </div>
                <div class="tooltip-body" style="background: white; padding: 12px; border-radius: 0 0 6px 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                  <div style="font-size: 18px; font-weight: 700; color: var(--dark-slate-blue);">${series[seriesIndex][dataPointIndex]} shipments</div>
                  <div style="font-size: 14px; color: var(--slate-blue);">${percentage}% of total</div>
                </div>
              </div>
            `;
          }
        }
      };

      this.charts.service = new ApexCharts(this.$refs.serviceChart, options);
      this.charts.service.render();
    },
    generateTrendData() {
      const now = new Date();
      let labels = [];
      let inTransitData = [];
      let deliveredData = [];
      let pendingData = [];

      let daysBack;
      let intervalDays;

      switch (this.selectedTimeRange) {
        case '7d':
          daysBack = 7;
          intervalDays = 1;
          break;
        case '30d':
          daysBack = 30;
          intervalDays = 7;
          break;
        case '90d':
        default:
          daysBack = 90;
          intervalDays = 30;
      }

      const intervals = Math.ceil(daysBack / intervalDays);

      inTransitData = Array(intervals).fill(0);
      deliveredData = Array(intervals).fill(0);
      pendingData = Array(intervals).fill(0);

      for (let i = 0; i < intervals; i++) {
        const intervalStart = new Date(now);
        intervalStart.setDate(intervalStart.getDate() - daysBack + (i * intervalDays));
        intervalStart.setHours(0, 0, 0, 0);

        const intervalEnd = new Date(intervalStart);
        intervalEnd.setDate(intervalEnd.getDate() + intervalDays - 1);
        intervalEnd.setHours(23, 59, 59, 999);

        if (i === intervals - 1) {
          intervalEnd.setTime(now.getTime());
        }

        if (this.selectedTimeRange === '7d') {
          labels.push(intervalStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        } else if (this.selectedTimeRange === '30d') {
          if (i === intervals - 1) {
            labels.push(`${intervalStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`);
          } else {
            labels.push(`${intervalStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${intervalEnd.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`);
          }
        } else {
          labels.push(intervalStart.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }));
        }

        this.filteredShipments.forEach(shipment => {
          if (!shipment.createdDate) return;

          try {
            const createdDate = new Date(shipment.createdDate);

            if (createdDate >= intervalStart && createdDate <= intervalEnd) {
              switch (shipment.status) {
                case 'in_transit':
                  inTransitData[i]++;
                  break;
                case 'delivered':
                  deliveredData[i]++;
                  break;
                case 'pending':
                  pendingData[i]++;
                  break;
              }
            }
          } catch (error) {
            console.warn('Invalid date format for shipment:', shipment.createdDate);
          }
        });
      }

      return {
        labels,
        inTransit: inTransitData,
        delivered: deliveredData,
        pending: pendingData
      };
    },
    redirectToLogin() {
      window.dispatchEvent(new CustomEvent('showLoginModal'));
    }
  }
};
</script>

<style scoped>
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
}

.dashboard-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard-container {
  min-height: 100vh;
  width: 100%;
  margin: 0;
  padding: 0;
}

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

.dashboard-header {
  margin-bottom: 2rem;
  width: 100%;
}

.header-background {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  padding: 2rem;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 4px 20px rgba(255, 66, 117, 0.3);
  width: 100%;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
  width: 100%;
}

.main-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
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

.filter-section {
  background: white;
  border-radius: 16px;
  padding: 1.75rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  align-items: end;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  font-weight: 700;
  color: var(--dark-slate-blue);
  font-size: 0.95rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid var(--pink-grey);
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
}

.filter-select:focus {
  outline: none;
  border-color: var(--hot-pink);
  box-shadow: 0 0 0 3px rgba(255, 66, 117, 0.1);
}

.charts-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 1.5rem;
}

.layout-selector {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.layout-title {
  font-weight: 600;
  color: var(--dark-slate-blue);
  font-size: 0.9rem;
  margin-right: 0.5rem;
}

.layout-btn {
  width: 44px;
  height: 44px;
  border: 2px solid var(--pink-grey);
  background: white;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  padding: 0;
}

.layout-btn.active {
  background: var(--hot-pink);
  border-color: var(--hot-pink);
}

.layout-btn:hover:not(.active) {
  border-color: var(--hot-pink);
}

.layout-icon {
  width: 24px;
  height: 24px;
  display: grid;
  gap: 2px;
}

.grid-2x2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 2px;
  width: 100%;
  height: 100%;
}

.grid-1x4 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 1fr;
  gap: 2px;
  width: 100%;
  height: 100%;
}

.grid-4x1 {
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: 1fr 1fr 1fr 1fr;
  gap: 2px;
  width: 100%;
  height: 100%;
}

.grid-cell {
  background: var(--slate-blue);
  border-radius: 1px;
  transition: all 0.3s ease;
}

.layout-btn.active .grid-cell {
  background: white;
}

.layout-btn:not(.active):hover .grid-cell {
  background: var(--hot-pink);
}

.charts-section {
  display: grid;
  gap: 1.5rem;
  margin-bottom: 2rem;
  overflow: hidden;
  position: relative;
}

.layout-2x2 {
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
}

.layout-1x4 {
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 1fr;
  min-width: 0;
  width: 100%;
}

.layout-4x1 {
  grid-template-columns: 1fr;
  grid-template-rows: repeat(4, 1fr);
}

.chart-container {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
  min-height: 400px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}

.chart-2x2 {
  min-height: 400px;
}

.chart-1x4 {
  min-height: 450px;
}

.chart-4x1 {
  min-height: 300px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-shrink: 0;
}

.chart-header h4 {
  margin: 0;
  color: var(--dark-slate-blue);
  font-size: 1.1rem;
  font-weight: 700;
}

.chart-period {
  font-size: 0.8rem;
  color: var(--slate-blue);
  background: var(--light-pink);
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-weight: 600;
}

.chart-wrapper {
  flex: 1;
  position: relative;
  min-height: 280px;
  overflow: hidden;
  width: 100%;
}

.chart-apex {
  width: 100%;
  height: 100%;
  min-height: 280px;
  overflow: hidden;
}

.performance-gauge {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 280px;
}

.gauge-circle {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gauge-center {
  width: 140px;
  height: 140px;
  background: white;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.gauge-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--dark-slate-blue);
  line-height: 1;
}

.gauge-label {
  font-size: 0.9rem;
  color: var(--slate-blue);
  margin-top: 0.5rem;
  font-weight: 600;
}

.login-required {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
}

.login-message {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
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
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
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
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.3);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

:deep(.apexcharts-tooltip) {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

:deep(.custom-tooltip) {
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

@media (max-width: 1024px) {
  .charts-section.layout-2x2 {
    grid-template-columns: 1fr;
    grid-template-rows: repeat(4, auto);
  }

  .charts-section.layout-1x4 {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
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

  .filter-section {
    flex-direction: column;
    gap: 1.5rem;
  }

  .filter-group {
    min-width: 100%;
  }

  .charts-section.layout-1x4 {
    grid-template-columns: 1fr;
    grid-template-rows: repeat(4, auto);
  }

  .charts-controls {
    justify-content: center;
  }

  .layout-selector {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .header-background {
    padding: 1.5rem 1rem;
  }

  .title-main {
    font-size: 2rem;
  }

  .title-sub {
    font-size: 1.4rem;
  }

  .header-stats {
    gap: 1rem;
  }

  .stat-value {
    font-size: 2rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .chart-container {
    padding: 1rem;
  }

  .login-message {
    padding: 2rem;
  }

  .action-buttons {
    flex-direction: column;
  }
}
</style>
