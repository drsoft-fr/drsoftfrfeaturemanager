<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'

const { delete: featureDelete, feature, getAll } = inject('feature')
const loading = ref(false)
const handleFeatureDelete = async (event) => {
  loading.value = true
  await featureDelete(parseInt(event.target.dataset.featureId || ''))
  await getAll()
  loading.value = false
}
</script>

<template>
  <Button
    label="Delete"
    type="button"
    :loading="loading"
    :data-feature-id="feature.id_feature"
    @click="handleFeatureDelete"
    :disabled="!feature.id_feature"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
