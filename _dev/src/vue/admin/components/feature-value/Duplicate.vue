<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'

const {
  duplicate: featureValueDuplicate,
  get,
  leftSelectedFeatureValueId,
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
} = inject('featureValue')
const { leftSelectedFeatureId, rightSelectedFeatureId } = inject('feature')
const { lifetime } = inject('toast')
const loading = ref(false)
const toast = useToast()
const handleFeatureValueDuplicate = async () => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true

  await featureValueDuplicate(
    leftSelectedFeatureValueId.value,
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
    severity: 'success',
    summary: 'Confirmed',
    detail: 'Feature value duplicated',
    life: lifetime.value,
  })
}
</script>

<template>
  <div>
    <Button
      class="w-full"
      label="Duplicate"
      type="button"
      :loading="loading"
      @click="handleFeatureValueDuplicate"
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
      >Would you like to duplicate the value from the left-hand column to the
      right-hand column?
    </Message>
  </div>
</template>

<style scoped lang="scss"></style>
