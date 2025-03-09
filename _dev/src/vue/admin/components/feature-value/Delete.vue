<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  featureId: Number,
  featureValueId: Number,
})

const { delete: featureValueDelete, get } = inject('featureValue')
const loading = ref(false)
const confirm = useConfirm()
const toast = useToast()
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

      await featureValueDelete(props.featureValueId)
      await get(props.featureId)

      loading.value = false
      toast.add({
        severity: 'success',
        summary: 'Confirmed',
        detail: 'Feature value deleted',
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
    @click="handleFeatureValueDelete"
    severity="danger"
  />
</template>

<style scoped lang="scss"></style>
