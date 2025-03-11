<script setup>
import { inject, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import { FilterMatchMode } from '@primevue/core/api'
import FeatureValueDelete from '@/vue/admin/components/feature-value/Delete.vue'

const { leftSelectedFeature, rightSelectedFeature } = inject('feature')
const {
  leftFeatureValues,
  rightFeatureValues,
  leftSelectedFeatureValues,
  rightSelectedFeatureValue,
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
} = inject('featureValue')

const props = defineProps({
  mode: {
    type: String,
    required: true,
    validator(value, props) {
      return ['multiple', 'single'].includes(value)
    },
  },
  selection: {
    type: String,
    required: true,
    validator(value, props) {
      return ['left', 'right'].includes(value)
    },
  },
})

let feature
let loading
let model
let value
if ('left' === props.selection) {
  feature = leftSelectedFeature
  loading = leftFeatureValueTableLoading
  model = leftSelectedFeatureValues
  value = leftFeatureValues
} else {
  feature = rightSelectedFeature
  loading = rightFeatureValueTableLoading
  model = rightSelectedFeatureValue
  value = rightFeatureValues
}

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature_value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  custom: { value: null, matchMode: FilterMatchMode.EQUALS },
})
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <DataTable
      v-model:selection="model"
      :value="value"
      dataKey="id_feature_value"
      removableSort
      paginator
      :rows="10"
      :rowsPerPageOptions="[5, 10, 20, 50]"
      v-model:filters="filters"
      :globalFilterFields="['custom', 'id_feature_value', 'value']"
      :loading="loading"
      v-show="value"
      class="border-t border-gray-200"
    >
      <template #header>
        <div class="flex justify-end">
          <InputText
            v-model="filters['global'].value"
            placeholder="Keyword Search"
          />
        </div>
      </template>
      <template #empty>No feature values found.</template>
      <template #loading>Loading feature values data. Please wait.</template>
      <Column :selectionMode="mode" header="Select"></Column>
      <Column field="id_feature_value" header="ID" sortable></Column>
      <Column field="value" header="Value"></Column>
      <Column field="custom" header="Is custom"></Column>
      <Column header="Action">
        <template #body="{ data }">
          <FeatureValueDelete
            :feature-id="feature.id_feature"
            :feature-value-id="data.id_feature_value"
            :selection
          />
        </template>
      </Column>
    </DataTable>
  </Transition>
</template>

<style scoped lang="scss">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
