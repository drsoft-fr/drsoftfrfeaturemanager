<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  featureId: Number,
  featureValueId: Number,
  productIds: Array,
})

const { delete: productUnlink, get } = inject('product')
const { lifetime } = inject('toast')
const loading = ref(false)
const confirm = useConfirm()
const toast = useToast()
const handleProductDelete = async () => {
  confirm.require({
    message: 'Do you want to unlink this product?',
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

      await productUnlink(
        props.productIds,
        props.featureValueId,
        props.featureId,
      )
      await get(props.featureId, props.featureValueId)

      loading.value = false
      toast.add({
        severity: 'success',
        summary: 'Confirmed',
        detail: 'Product unlink',
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
    @click="handleProductDelete"
    severity="danger"
    :disabled="!(featureId && featureValueId && productIds.length)"
  />
</template>

<style scoped lang="scss"></style>
