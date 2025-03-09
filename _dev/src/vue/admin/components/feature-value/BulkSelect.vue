<script setup>
import { computed, inject } from 'vue'
import Checkbox from 'primevue/checkbox'

const { featureValues, selectedFeatureValues, selectedFeatureValueIds } =
  inject('featureValue')

const selectAll = computed({
  get() {
    return !!selectedFeatureValueIds.value.length
  },
  set() {
    const selected = []

    if (!selectedFeatureValues.value.length) {
      featureValues.value.forEach((featureValue) => {
        selected.push(featureValue.id_feature_value)
      })
    }

    selectedFeatureValueIds.value = selected
  },
})
</script>

<template>
  <label for="feature-value-bulk-select" class="d-block m-0 w-100">
    <Checkbox
      v-model="selectAll"
      inputId="feature-value-bulk-select"
      size="large"
      binary
      :disabled="featureValues.length === 0"
      indeterminate
    />
    Select all
  </label>
</template>

<style scoped lang="scss"></style>
