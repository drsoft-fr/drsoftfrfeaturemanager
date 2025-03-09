<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'

const props = defineProps({
  featureId: Number,
  featureValueId: Number,
})

const { delete: featureValueDelete, get } = inject('featureValue')
const loading = ref(false)
const handleFeatureValueDelete = async (event) => {
  loading.value = true

  await featureValueDelete(props.featureValueId)
  await get(props.featureId)

  loading.value = false
}
</script>

<template>
  <Button
    label="Delete"
    type="button"
    :loading="loading"
    @click="handleFeatureValueDelete"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
