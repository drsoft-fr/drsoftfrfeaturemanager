<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  newFeatureId: Number,
  newFeatureValueId: Number,
  featureId: Number,
  featureValueId: Number,
  productIds: Array,
})

const { relocate, get } = inject('product')
const loading = ref(false)
const toast = useToast()
const handleProductRelocate = async () => {
  loading.value = true

  const res = await relocate(
    props.productIds,
    props.newFeatureValueId,
    props.newFeatureId,
    props.featureValueId,
    props.featureId,
  )
  await get(props.featureId, props.featureValueId)

  loading.value = false
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: 3000,
  })
}
</script>

<template>
  <div>
    <Button
      label="Relocate"
      type="button"
      :loading="loading"
      @click="handleProductRelocate"
      severity="info"
      :disabled="
        !(
          newFeatureId &&
          newFeatureValueId &&
          featureId &&
          featureValueId &&
          productIds.length
        )
      "
      v-tooltip.top="
        'Would you like to move products from the left column to the right?'
      "
    />
  </div>
</template>

<style scoped lang="scss"></style>
