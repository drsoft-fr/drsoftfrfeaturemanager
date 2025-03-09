<script setup>
import { inject } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import FeatureValueDelete from '@/vue/admin/components/feature-value/Delete.vue'

const { feature } = inject('feature')
const { featureValues, selectedFeatureValues } = inject('featureValue')
</script>

<template>
  <DataTable
    v-model:selection="selectedFeatureValues"
    :value="featureValues"
    dataKey="id_feature_value"
    removableSort
  >
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
