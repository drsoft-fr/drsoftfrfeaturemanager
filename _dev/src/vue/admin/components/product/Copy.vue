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

const { copy, get } = inject('product')
const { lifetime } = inject('toast')
const loading = ref(false)
const toast = useToast()
const handleProductAddToRightColumn = async () => {
  loading.value = true

  const res = await copy(
    props.productIds,
    props.newFeatureValueId,
    props.newFeatureId,
  )
  await get(props.featureId, props.featureValueId)

  loading.value = false
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
      label="Copy"
      type="button"
      :loading="loading"
      @click="handleProductAddToRightColumn"
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
        'Would you like to copy products from the left-hand column to the right-hand column? (Products are not removed from the left-hand column)'
      "
    />
  </div>
</template>

<style scoped lang="scss"></style>
