<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'

const {
  copy: featureValueCopy,
  get,
  leftSelectedFeatureValueId,
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
} = inject('featureValue')
const { leftSelectedFeatureId, rightSelectedFeatureId } = inject('feature')
const loading = ref(false)
const toast = useToast()
const handleFeatureValueCopy = async () => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true

  const res = await featureValueCopy(
    leftSelectedFeatureValueId.value,
    leftSelectedFeatureId.value,
    rightSelectedFeatureId.value,
  )
  await Promise.all([
    get(leftSelectedFeatureId.value, 'left', false),
    get(rightSelectedFeatureId.value, 'right', false),
  ])

  loading.value = false
  leftFeatureValueTableLoading.value = false
  rightFeatureValueTableLoading.value = false
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: 5000,
  })
}
</script>

<template>
  <div>
    <Button
      class="w-full"
      label="Copy"
      type="button"
      :loading="loading"
      @click="handleFeatureValueCopy"
      severity="info"
      :disabled="
        !(
          leftSelectedFeatureId &&
          rightSelectedFeatureId &&
          leftSelectedFeatureValueId
        )
      "
    />
    <Message class="mt-2" size="small" severity="secondary" variant="simple"
      >Would you like to copy the value (and products) from the left-hand column
      to the right-hand column?
    </Message>
  </div>
</template>

<style scoped lang="scss"></style>
