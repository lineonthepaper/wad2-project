<template>
  <div class="container-fluid">
    <div v-if="!('services' in props)">Loading...</div>
    <div v-else-if="props.services.length === 0">
      No services could be found. Please check your inputs.
    </div>
    <div class="row justify-content-center" v-else>
      <table class="table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Transit Time</th>
            <th>Tracked?</th>
            <th>Price (SGD)</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <ServiceRow
            v-for="s in props.services"
            :key="s.name"
            :service="s.name"
            :transitTime="s.min + '-' + s.max + ' days'"
            :price="s.price"
            :selected="s.selected"
            :isTracked="s.isTracked"
            @toggleSelect="getSelect(s.name)"
          />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import ServiceRow from './ServiceRow.vue'
</script>

<script>
export default {
  data() {
    return {
      // services: [
      //   {
      //     service: 'a service',
      //     transitTime: '1-10 days',
      //     price: '$10',
      //     selected: true,
      //   }
      // ],
    }
  },
  props: ['props'],
  methods: {
    getSelect(serviceName) {
      let foundService = this.props.services.find((service) => service.name === serviceName)

      foundService.selected = !foundService.selected

      if (foundService.selected) {
        for (let s of this.props.services) {
          if (s.name !== serviceName) {
            s.selected = false
          }
        }
      }
      this.updateSelect()
    },
    updateSelect() {
      let foundService = this.props.services.find((service) => service.selected === true)

      this.$emit('update-select', foundService)
    },
  },
}
</script>

<style scoped>
@media screen and (max-width: 510px) {
  .table {
    transform: scale(0.8);
  }
}

@media screen and (max-width: 450px) {
  .table {
    transform: scale(0.7);
  }
}

@media screen and (max-width: 320px) {
  .table {
    transform: scale(0.55);
  }
}
</style>
