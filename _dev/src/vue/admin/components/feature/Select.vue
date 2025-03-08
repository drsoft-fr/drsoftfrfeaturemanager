<script setup>
import { inject } from 'vue'
import Select from 'primevue/select'

const { feature, features } = inject('feature')
</script>

<template>
  <div class="flex flex-col gap-2">
    <label for="features">Features</label>
    <Select
      v-model="feature"
      :options="features"
      filter
      optionLabel="name"
      placeholder="Select a Feature"
      id="features"
    />
  </div>
</template>

<style scoped lang="scss">

</style>
