<script setup>
import { inject } from 'vue'
import Select from 'primevue/select'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import Message from 'primevue/message'

const { features, leftSelectedFeature, rightSelectedFeature } =
  inject('feature')

const props = defineProps({
  selection: {
    type: String,
    required: true,
    validator(value, props) {
      return ['left', 'right'].includes(value)
    },
  },
})

let model
if ('left' === props.selection) {
  model = leftSelectedFeature
} else {
  model = rightSelectedFeature
}
</script>

<template>
  <div class="flex flex-col gap-2">
    <label for="features">Select {{ selection }} features</label>
    <Select
      v-model="model"
      :options="features"
      filter
      optionLabel="name"
      placeholder="Select a Feature"
      id="features"
    />
    <Message size="small" severity="secondary" variant="simple"
      >Would you like to delete this feature{{
        model ? ` (${model.name})` : ''
      }}?</Message
    >
    <FeatureDelete :selection />
  </div>
</template>

<style scoped lang="scss"></style>
