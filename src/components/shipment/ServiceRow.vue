<template>
  <tr>
    <td>aa</td>
    <td>aa</td>
    <td>aa</td>
    <td>
      <div class="checkbox" @click="selected = !selected">
        <img src="/shipment/stamp.png" alt="Stamp" :class="{ 'img-selected': selected }" />
      </div>
    </td>
  </tr>
</template>

<script>
export default {
  data() {
    return {
      selected: true,
    }
  },
}
</script>

<style scoped>
.checkbox {
  height: 1.5em;
  width: 1.5em;
  border: 1px solid black;
  border-radius: 2px;

  cursor: pointer;
  user-select: none;
}

.checkbox img {
  height: 0px;
  margin-left: -50%;
  margin-top: -50%;
  transform: rotate(-25deg);
}

.checkbox .img-selected {
  height: 200%;
  transform: rotate(0deg);
  transition: transform 0.25s ease-in;
}
</style>
