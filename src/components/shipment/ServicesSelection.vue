<template>
  <div class="container-fluid">
    <table class="table">
      <thead>
        <tr>
          <th>Service</th>
          <th>Transit Time</th>
          <th>Price</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <ServiceRow />
      </tbody>
    </table>
  </div>
</template>

<script setup>
import ServiceRow from './ServiceRow.vue'
</script>
