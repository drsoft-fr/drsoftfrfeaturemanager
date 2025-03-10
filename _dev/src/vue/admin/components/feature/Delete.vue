<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const { delete: featureDelete, feature, getAll } = inject('feature')
const { featureValueTableLoading } = inject('featureValue')
const loading = ref(false)
const confirm = useConfirm()
const toast = useToast()
const handleFeatureDelete = async () => {
  confirm.require({
    message: 'Do you want to delete this feature?',
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
      featureValueTableLoading.value = true
      const featureId = parseInt(feature.value.id_feature || '')

      if (0 >= featureId || isNaN(featureId)) {
        toast.add({
          severity: 'error',
          summary: 'Info',
          detail: 'invalid id_feature',
          life: 3000,
        })

        loading.value = false

        return
      }

      await featureDelete(featureId)
      await getAll()

      loading.value = false
      featureValueTableLoading.value = false
      toast.add({
        severity: 'success',
        summary: 'Confirmed',
        detail: 'Feature deleted',
        life: 3000,
      })
    },
    reject: () => {
      toast.add({
        severity: 'warn',
        summary: 'Rejected',
        detail: 'You have rejected',
        life: 3000,
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
