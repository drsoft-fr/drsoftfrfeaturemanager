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

const {
  delete: featureDelete,
  leftSelectedFeature,
  rightSelectedFeature,
  getAll,
} = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { lifetime } = inject('toast')
const loading = ref(false)
const confirm = useConfirm()
const toast = useToast()

let feature
let tableLoading

if ('left' === props.selection) {
  feature = leftSelectedFeature
  tableLoading = leftFeatureValueTableLoading
} else {
  feature = rightSelectedFeature
  tableLoading = rightFeatureValueTableLoading
}

const handleFeatureDelete = async () => {
  confirm.require({
    message: `Do you want to delete this feature (${feature.value.name})?`,
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

      await featureDelete(featureId)
      await getAll()

      loading.value = false
      tableLoading.value = false
      toast.add({
        severity: 'success',
        summary: 'Confirmed',
        detail: 'Feature deleted',
        life: lifetime.value,
      })
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
    :disabled="!feature.id_feature"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
