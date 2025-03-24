<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  featureId: Number,
  featureValueId: Number,
  selection: {
    type: String,
    required: true,
    validator(value, props) {
      return ['left', 'right'].includes(value)
    },
  },
})

const { get, leftSelectedFeatureValue, rightSelectedFeatureValue } =
  inject('featureValue')
const { leftSelectedFeature, rightSelectedFeature } = inject('feature')
const { featureValueDelete: featureValueDeleteRoute } = inject('routes')
const { lifetime } = inject('toast')

const loading = ref(false)

const confirm = useConfirm()
const toast = useToast()

/**
 * Deletes a feature value with the provided ID.
 *
 * @param {number} featureValueId - The ID of the feature value to delete.
 *
 * @returns {Promise<any>} A promise that resolves to the JSON response from the deletion request.
 */
const featureValueDelete = async (featureValueId) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())

  const res = await fetch(featureValueDeleteRoute, {
    method: 'POST',
    body: form,
  })

  if (
    typeof leftSelectedFeatureValue.value !== 'undefined' &&
    featureValueId === leftSelectedFeatureValue.value.id_feature_value
  ) {
    leftSelectedFeatureValue.value = undefined
  }

  if (
    typeof rightSelectedFeatureValue.value !== 'undefined' &&
    featureValueId === rightSelectedFeatureValue.value.id_feature_value
  ) {
    rightSelectedFeatureValue.value = undefined
  }

  return await res.json()
}

const handleFeatureValueDelete = async () => {
  confirm.require({
    message: 'Do you want to delete this feature value?',
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

      const res = await featureValueDelete(props.featureValueId)
      const all =
        typeof leftSelectedFeature.value !== 'undefined' &&
        typeof rightSelectedFeature.value !== 'undefined' &&
        leftSelectedFeature.value.id_feature ===
          rightSelectedFeature.value.id_feature
      const res2 = await get(props.featureId, props.selection, all)

      loading.value = false

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
    @click="handleFeatureValueDelete"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
