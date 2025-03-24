<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'

const { getAll } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { featureCreate: featureCreateRoute } = inject('routes')
const { lifetime } = inject('toast')

const loading = ref(false)

const toast = useToast()

/**
 * Asynchronously creates a new feature by sending a POST request to the specified route.
 *
 * @param {Element} elm - The HTML element containing the data to be submitted for feature creation.
 *
 * @returns {Promise} A Promise that resolves to the JSON response containing information about the created feature.
 */
const featureCreate = async (elm) => {
  const res = await fetch(featureCreateRoute, {
    method: 'POST',
    body: new FormData(elm),
  })

  return await res.json()
}

/**
 * Asynchronously handles the creation of a new feature based on the provided event.
 * Sets loading values to true while processing and false upon completion.
 * Displays toast notifications based on the success of the feature creation and retrieval of all features.
 *
 * @param {Event} event - The event triggering the feature creation process.
 *
 * @returns {void}
 */
const handleFeatureCreate = async (event) => {
  loading.value = true
  leftFeatureValueTableLoading.value = true
  rightFeatureValueTableLoading.value = true

  const res = await featureCreate(event.currentTarget)
  const res2 = await getAll()

  loading.value = false
  leftFeatureValueTableLoading.value = false
  rightFeatureValueTableLoading.value = false

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
      <Button
        label="Create"
        type="submit"
        :loading="loading"
        severity="success"
      />
    </div>
  </form>
</template>

<style scoped lang="scss"></style>
