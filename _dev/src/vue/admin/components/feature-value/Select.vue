<script setup>
import { inject } from 'vue'
import Checkbox from 'primevue/checkbox'

const props = defineProps({
  featureValueId: Number,
})

const { selectedFeatureValueIds } = inject('featureValue')
</script>

<template>
  <label :for="`feature-value-${featureValueId}`" class="d-block m-0 w-100">
    <Checkbox
      v-model="selectedFeatureValueIds"
      :inputId="`feature-value-${featureValueId}`"
      name="feature-value"
      :value="featureValueId"
      size="large"
    />
  </label>
</template>

<style scoped lang="scss"></style>
