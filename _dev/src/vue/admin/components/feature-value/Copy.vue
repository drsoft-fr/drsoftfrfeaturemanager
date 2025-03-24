<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'

const {
  get,
  leftSelectedFeatureValueId,
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
} = inject('featureValue')
const { leftSelectedFeatureId, rightSelectedFeatureId } = inject('feature')
const { featureValueCopy: featureValueCopyRoute } = inject('routes')
const { lifetime } = inject('toast')

const loading = ref(false)

const toast = useToast()

/**
 * Copies a feature value to a new feature for a given feature ID and value ID.
 *
 * @param {number} featureValueId - The ID of the feature value to be copied.
 * @param {number} featureId - The ID of the feature associated with the feature value.
 * @param {number} newFeatureId - The ID of the new feature to copy the value to.
 *
 * @returns {Promise<Object>} - A promise that resolves with the copied feature value data.
 */
const featureValueCopy = async (featureValueId, featureId, newFeatureId) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())
  form.append('new_id_feature', newFeatureId.toString())

  const res = await fetch(featureValueCopyRoute, {
    method: 'POST',
    body: form,
  })

  return await res.json()
}

/**
 * Handles the feature value copy operation asynchronously.
 * Sets loading flags for left and right feature value tables to true.
 * Calls the featureValueCopy function with selected feature values.
 * Calls the get function to retrieve updated data for both left and right feature value tables.
 * Sets loading flags for left and right feature value tables to false.
 * Adds toast notification based on the result of the featureValueCopy operation.
 *
 * @returns {Promise<void>}
 */
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
    life: lifetime.value,
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
