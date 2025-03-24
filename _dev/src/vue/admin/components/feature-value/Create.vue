<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'

const { leftSelectedFeature, rightSelectedFeature } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { create, get } = inject('featureValue')
const { lifetime } = inject('toast')

const props = defineProps({
  selection: {
    type: String,
    required: true,
    validator(value, props) {
      return ['left', 'right'].includes(value)
    },
  },
})

let tableLoading
let selectedFeature

if ('left' === props.selection) {
  tableLoading = leftFeatureValueTableLoading
  selectedFeature = leftSelectedFeature
} else {
  tableLoading = rightFeatureValueTableLoading
  selectedFeature = rightSelectedFeature
}

const loading = ref(false)
const toast = useToast()

const handleFeatureValueCreate = async (event) => {
  loading.value = true
  tableLoading.value = true
  const featureId = parseInt(selectedFeature.value.id_feature || '')
  await create(event.currentTarget)
  await get(
    featureId,
    props.selection,
    leftSelectedFeature.value.id_feature ===
      rightSelectedFeature.value.id_feature,
  )
  loading.value = false
  tableLoading.value = false
  toast.add({
    severity: 'success',
    summary: 'Confirmed',
    detail: 'Feature value created',
    life: lifetime.value,
  })
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
