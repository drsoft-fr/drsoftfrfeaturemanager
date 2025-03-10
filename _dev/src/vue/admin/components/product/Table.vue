<script setup>
import { inject } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const {
  products,
  productTableLoading: loading,
  selectedProducts,
} = inject('product')
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <DataTable
      v-model:selection="selectedProducts"
      :value="products"
      dataKey="id_product"
      removableSort
      paginator
      :rows="10"
      :rowsPerPageOptions="[5, 10, 20, 50]"
      :loading="loading"
      v-show="products"
      class="border-t border-gray-200"
    >
      <template #empty>No products found.</template>
      <template #loading>Loading products data. Please wait.</template>
      <Column selectionMode="multiple" header="Select"></Column>
      <Column field="id_product" header="ID" sortable></Column>
      <Column field="id_supplier" header="Supplier ID" sortable></Column>
      <Column field="supplier" header="Supplier"></Column>
      <Column
        field="id_manufacturer"
        header="Manufacturer ID"
        sortable
      ></Column>
      <Column field="manufacturer" header="Manufacturer"></Column>
      <Column field="reference" header="Reference"></Column>
      <Column field="active" header="Active"></Column>
      <Column field="id_lang" header="Language ID" sortable></Column>
      <Column field="name" header="Name"></Column>
      <Column field="id_shop" header="Shop ID" sortable></Column>
      <Column
        field="id_category_default"
        header="Default category ID"
        sortable
      ></Column>
      <Column field="category" header="Category"></Column>
      <Column field="id_feature" header="Feature ID" sortable></Column>
      <Column field="feature" header="Feature"></Column>
      <Column
        field="id_feature_value"
        header="Feature value ID"
        sortable
      ></Column>
      <Column field="value" header="Feature value"></Column>
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
