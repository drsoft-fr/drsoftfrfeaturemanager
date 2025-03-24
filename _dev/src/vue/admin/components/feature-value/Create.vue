<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
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

const { leftSelectedFeature, rightSelectedFeature } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { get } = inject('featureValue')
const { featureValueCreate: featureValueCreateRoute } = inject('routes')
const { lifetime } = inject('toast')

const loading = ref(false)

const toast = useToast()

let tableLoading
let selectedFeature

if ('left' === props.selection) {
  tableLoading = leftFeatureValueTableLoading
  selectedFeature = leftSelectedFeature
} else {
  tableLoading = rightFeatureValueTableLoading
  selectedFeature = rightSelectedFeature
}

/**
 * Asynchronously creates a new feature value based on the provided element.
 *
 * @param {Element} elm - The DOM element containing the data to be used for creating the feature value.
 *
 * @returns {Promise} A promise that resolves to the feature value data after it has been created.
 */
const featureValueCreate = async (elm) => {
  const res = await fetch(featureValueCreateRoute, {
    method: 'POST',
    body: new FormData(elm),
  })

  return await res.json()
}

/**
 * Asynchronously handles feature value creation based on the event triggered.
 *
 * @param {Event} event - The event triggering the function.
 */
const handleFeatureValueCreate = async (event) => {
  loading.value = true
  tableLoading.value = true

  const featureId = parseInt(selectedFeature.value.id_feature || '')
  const res = await featureValueCreate(event.currentTarget)
  const all =
    typeof leftSelectedFeature.value !== 'undefined' &&
    typeof rightSelectedFeature.value !== 'undefined' &&
    leftSelectedFeature.value.id_feature ===
      rightSelectedFeature.value.id_feature
  const res2 = await get(featureId, props.selection, all)

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
}
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <form v-if="selectedFeature" @submit.prevent="handleFeatureValueCreate">
      <input
        type="hidden"
        name="id_feature"
        :value="selectedFeature.id_feature"
      />
      <div class="flex flex-col gap-2">
        <label for="create-feature-value">
          Create value for {{ selectedFeature.name }} feature
        </label>
        <InputText
          placeholder="Feature value name"
          type="text"
          id="create-feature-value"
          name="value"
        />
        <Message size="small" severity="secondary" variant="simple"
          >Would you like to create feature value?
        </Message>
        <Button
          label="Create"
          type="submit"
          :loading="loading"
          severity="success"
        />
      </div>
    </form>
    <div v-else>
      <Message size="small" severity="warn" variant="simple"
        >Select a feature to add a value to it
      </Message>
    </div>
  </Transition>
</template>

<style scoped lang="scss"></style>
