<script setup>
import { inject, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import { FilterMatchMode } from '@primevue/core/api'
import FeatureValueDelete from '@/vue/admin/components/feature-value/Delete.vue'

const { feature } = inject('feature')
const {
  featureValues,
  featureValueTableLoading: loading,
  selectedFeatureValues,
} = inject('featureValue')

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature_value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  custom: { value: null, matchMode: FilterMatchMode.EQUALS },
})
</script>

<template>
  <DataTable
    v-model:selection="selectedFeatureValues"
    :value="featureValues"
    dataKey="id_feature_value"
    removableSort
    paginator
    :rows="10"
    :rowsPerPageOptions="[5, 10, 20, 50]"
    v-model:filters="filters"
    :globalFilterFields="['custom', 'id_feature_value', 'value']"
    :loading="loading"
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
    <Column selectionMode="multiple" header="Select"></Column>
    <Column field="id_feature_value" header="ID" sortable></Column>
    <Column field="value" header="Value"></Column>
    <Column field="custom" header="Is custom"></Column>
    <Column header="Action">
      <template #body="{ data }">
        <FeatureValueDelete
          :feature-id="feature.id_feature"
          :feature-value-id="data.id_feature_value"
        />
      </template>
    </Column>
  </DataTable>
</template>

<style scoped lang="scss"></style>
