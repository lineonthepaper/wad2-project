<template>
  <div>
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
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: "AnalyticsInsights",
  data() {
    const today = new Date();
    const oneMonthAgo = new Date(today);
    oneMonthAgo.setMonth(today.getMonth() - 1);
    
    return {
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
      ],
      charts: {},
      // Sample data for charts
      sampleParcels: [
        { status: 'In Progress', service: { name: 'Express' }, createdDate: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'Delivered', service: { name: 'Standard' }, createdDate: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'Pending', service: { name: 'Express' }, createdDate: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'In Progress', service: { name: 'Standard' }, createdDate: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'Delivered', service: { name: 'Express' }, createdDate: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'Delivered', service: { name: 'Standard' }, createdDate: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'In Progress', service: { name: 'Express' }, createdDate: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString() },
        { status: 'Pending', service: { name: 'Standard' }, createdDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString() }
      ]
    };
  },
  computed: {
    timeRangeLabel() {
      const period = this.timePeriods.find(p => p.value === this.selectedTimeRange);
      return period ? period.label : 'Custom Range';
    },
    deliveryRate() {
      const filteredData = this.getFilteredStats();
      return filteredData.total > 0 ? 
        Math.round((filteredData.delivered / filteredData.total) * 100) : 0;
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
          return this.sampleParcels.filter(parcel => {
            const parcelDate = new Date(parcel.createdDate);
            return parcelDate >= startDate && parcelDate <= endDate;
          });
        case 'all':
        default:
          return this.sampleParcels;
      }

      return this.sampleParcels.filter(parcel => {
        const parcelDate = new Date(parcel.createdDate);
        return parcelDate >= startDate;
      });
    }
  },
  mounted() {
    // Initialize charts if expanded by default
    if (this.chartsExpanded) {
      this.$nextTick(() => {
        this.initCharts();
      });
    }
  },
  beforeUnmount() {
    this.destroyCharts();
  },
  methods: {
    // Charts Methods
    toggleCharts() {
      this.chartsExpanded = !this.chartsExpanded;
      if (this.chartsExpanded) {
        this.$nextTick(() => {
          setTimeout(() => {
            this.initCharts();
          }, 300);
        });
      } else {
        this.destroyCharts();
      }
    },

    destroyCharts() {
      Object.values(this.charts).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
          chart.destroy();
        }
      });
      this.charts = {};
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
      if (this.chartsExpanded) {
        this.destroyCharts();
        this.$nextTick(() => {
          this.initCharts();
        });
      }
    },

    initCharts() {
      if (!this.$refs.statusChart || !this.chartsExpanded) return;
      
      try {
        this.initStatusChart();
        this.initTrendsChart();
        this.initServiceChart();
        this.initGaugeChart();
      } catch (error) {
        console.error('Error initializing charts:', error);
      }
    },

    initStatusChart() {
      const ctx = this.$refs.statusChart;
      if (!ctx) return;

      const filteredData = this.getFilteredStats();

      if (!ctx.getContext) return;

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
          maintainAspectRatio: false,
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
                  const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
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
      if (!ctx || !ctx.getContext) return;

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
          maintainAspectRatio: false,
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
      if (!ctx || !ctx.getContext) return;

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
          maintainAspectRatio: false,
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
      if (!ctx || !ctx.getContext) return;

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
          maintainAspectRatio: false,
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
          inProgressData = labels.map(() => Math.floor(Math.random() * 10) + 5);
          deliveredData = labels.map(() => Math.floor(Math.random() * 8) + 3);
          break;
        case 'weekly':
          labels = Array.from({length: 4}, (_, i) => `Week ${i + 1}`);
          inProgressData = labels.map(() => Math.floor(Math.random() * 30) + 15);
          deliveredData = labels.map(() => Math.floor(Math.random() * 25) + 10);
          break;
        case 'monthly':
        default:
          labels = Array.from({length: 6}, (_, i) => {
            const date = new Date(now);
            date.setMonth(date.getMonth() - (5 - i));
            return date.toLocaleDateString('en-US', { month: 'short' });
          });
          inProgressData = labels.map(() => Math.floor(Math.random() * 50) + 25);
          deliveredData = labels.map(() => Math.floor(Math.random() * 40) + 20);
      }

      return {
        labels,
        inProgress: inProgressData,
        delivered: deliveredData
      };
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
  max-height: 2000px;
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
</style>