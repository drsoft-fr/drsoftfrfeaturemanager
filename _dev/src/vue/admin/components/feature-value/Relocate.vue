<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'

const {
  relocate: featureValueRelocate,
  get,
  leftSelectedFeatureValueId,
  leftSelectedFeatureValue,
  rightSelectedFeatureValue,
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
} = inject('featureValue')
const { leftSelectedFeatureId, rightSelectedFeatureId } = inject('feature')
const { products, productTableLoading, selectedProducts } = inject('product')
const { lifetime } = inject('toast')
const loading = ref(false)
const toast = useToast()
const handleFeatureValueRelocate = async () => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true
  productTableLoading.value = true

  const res = await featureValueRelocate(
    leftSelectedFeatureValueId.value,
    leftSelectedFeatureId.value,
    rightSelectedFeatureId.value,
  )
  await Promise.all([
    get(leftSelectedFeatureId.value, 'left', false),
    get(rightSelectedFeatureId.value, 'right', false),
  ])

  leftSelectedFeatureValue.value = undefined
  rightSelectedFeatureValue.value = undefined
  products.value = []
  selectedProducts.value = []

  loading.value = false
  leftFeatureValueTableLoading.value = false
  rightFeatureValueTableLoading.value = false
  productTableLoading.value = false
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: lifetime.value,
  })
}
</script>

<template>
  <div>
    <Button
      class="w-full"
      label="Relocate"
      type="button"
      :loading="loading"
      @click="handleFeatureValueRelocate"
      severity="warn"
      :disabled="
        !(
          leftSelectedFeatureId &&
          rightSelectedFeatureId &&
          leftSelectedFeatureValueId
        )
      "
    />
    <Message class="mt-2" size="small" severity="secondary" variant="simple"
      >Would you like to relocate the value (and products) from the left-hand
      column to the right-hand column?
    </Message>
  </div>
</template>

<style scoped lang="scss"></style>
