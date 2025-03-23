<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'

const {
  move,
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
const handleFeatureValueMove = async () => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true
  productTableLoading.value = true

  const res = await move(
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
      label="Move"
      type="button"
      :loading="loading"
      @click="handleFeatureValueMove"
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
      >Would you like to move the value (and products) from the left-hand
      column to the right-hand column?
    </Message>
  </div>
</template>

<style scoped lang="scss"></style>
