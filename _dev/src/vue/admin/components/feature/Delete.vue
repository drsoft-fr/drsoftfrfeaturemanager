<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  selection: {
    type: String,
    required: true,
    validator(value, props) {
      return ['left', 'right'].includes(value)
    },
  },
})

const { leftSelectedFeature, rightSelectedFeature, getAll } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { featureDelete: featureDeleteRoute } = inject('routes')
const { lifetime } = inject('toast')

const loading = ref(false)

const confirm = useConfirm()
const toast = useToast()

let feature
let otherFeature
let tableLoading

if ('left' === props.selection) {
  feature = leftSelectedFeature
  otherFeature = rightSelectedFeature
  tableLoading = leftFeatureValueTableLoading
} else {
  feature = rightSelectedFeature
  otherFeature = leftSelectedFeature
  tableLoading = rightFeatureValueTableLoading
}

/**
 * Deletes a feature by its ID and updates the selected feature based on the selection value.
 *
 * @param {number} featureId - The ID of the feature to be deleted.
 *
 * @returns {Promise<Object>} - A Promise that resolves with the response data after deleting the feature.
 */
const featureDelete = async (featureId) => {
  const form = new FormData()

  form.append('id_feature', featureId.toString())

  const res = await fetch(featureDeleteRoute, {
    method: 'POST',
    body: form,
  })

  feature.value = undefined

  if (featureId === otherFeature.value.id_feature) {
    otherFeature.value = undefined
  }

  return await res.json()
}

/**
 * Asynchronously handles the deletion of a feature.
 *
 * This method confirms with the user before deleting the feature and performs the following actions:
 * 1. Sets loading indicators.
 * 2. Validates the feature ID.
 * 3. Calls the featureDelete API to delete the feature.
 * 4. Calls the getAll API to refresh the data.
 * 5. Displays toast messages based on the API responses.
 *
 * If the user rejects the delete operation, a toast message will be displayed indicating rejection.
 */
const handleFeatureDelete = async () => {
  confirm.require({
    message: `Do you want to delete this feature (${feature.value.name || ''})?`,
    header: 'Danger Zone',
    icon: 'pi pi-info-circle',
    rejectLabel: 'Cancel',
    rejectProps: {
      label: 'Cancel',
      severity: 'secondary',
      outlined: true,
    },
    acceptProps: {
      label: 'Delete',
      severity: 'danger',
    },
    accept: async () => {
      loading.value = true
      tableLoading.value = true

      const featureId = parseInt(feature.value.id_feature || '')

      if (0 >= featureId || isNaN(featureId)) {
        toast.add({
          severity: 'error',
          summary: 'Info',
          detail: 'invalid id_feature',
          life: lifetime.value,
        })

        loading.value = false
        tableLoading.value = false

        return
      }

      const res = await featureDelete(featureId, props.selection)
      const res2 = await getAll()

      loading.value = false
      tableLoading.value = false
      toast.add({
        severity: res.success ? 'success' : 'error',
        summary: res.success ? 'Confirmed' : 'Error',
        detail: res.message,
        life: lifetime.value,
      })

      if (false === res2.success) {
        toast.add({
          severity: res2.success ? 'success' : 'error',
          summary: res2.success ? 'Confirmed' : 'Error',
          detail: res2.message,
          life: lifetime.value,
        })
      }
    },
    reject: () => {
      toast.add({
        severity: 'warn',
        summary: 'Rejected',
        detail: 'You have rejected',
        life: lifetime.value,
      })
    },
  })
}
</script>

<template>
  <Button
    label="Delete"
    type="button"
    :loading="loading"
    @click="handleFeatureDelete"
    :disabled="!feature"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
