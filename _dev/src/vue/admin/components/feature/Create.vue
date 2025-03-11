<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'

const { create, getAll } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const loading = ref(false)
const toast = useToast()
const handleFeatureCreate = async (event) => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true
  await create(event.currentTarget)
  await getAll()
  loading.value = false
  leftFeatureValueTableLoading.value = false
  rightFeatureValueTableLoading.value = false
  toast.add({
    severity: 'success',
    summary: 'Confirmed',
    detail: 'Feature created',
    life: 3000,
  })
}
</script>

<template>
  <form @submit.prevent="handleFeatureCreate">
    <div class="flex flex-col gap-2">
      <label for="create-feature"> Create new feature </label>
      <InputText
        placeholder="Feature name"
        type="text"
        id="create-feature"
        name="name"
      />
      <Message size="small" severity="secondary" variant="simple"
        >Would you like to create a new feature?</Message
      >
      <Button label="Create" type="submit" :loading="loading" severity="info" />
    </div>
  </form>
</template>

<style scoped lang="scss"></style>
