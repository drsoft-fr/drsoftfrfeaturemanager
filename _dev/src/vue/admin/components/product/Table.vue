<script setup>
import { inject, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Toolbar from 'primevue/toolbar'
import Column from 'primevue/column'
import { FilterMatchMode } from '@primevue/core/api'
import InputText from 'primevue/inputtext'
import ProductCopy from '@/vue/admin/components/product/Copy.vue'
import ProductDelete from '@/vue/admin/components/product/Delete.vue'
import ProductMove from '@/vue/admin/components/product/Move.vue'

const { leftSelectedFeature, rightSelectedFeature } = inject('feature')
const { leftSelectedFeatureValue, rightSelectedFeatureValue } =
  inject('featureValue')
const {
  products,
  productTableLoading: loading,
  selectedProducts,
  selectedProductIds,
} = inject('product')

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_product: { value: null, matchMode: FilterMatchMode.EQUALS },
  id_supplier: { value: null, matchMode: FilterMatchMode.EQUALS },
  supplier: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_manufacturer: { value: null, matchMode: FilterMatchMode.EQUALS },
  manufacturer: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  reference: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  active: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_lang: { value: null, matchMode: FilterMatchMode.EQUALS },
  name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_shop: { value: null, matchMode: FilterMatchMode.EQUALS },
  id_category_default: { value: null, matchMode: FilterMatchMode.EQUALS },
  category: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature: { value: null, matchMode: FilterMatchMode.EQUALS },
  feature: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature_value: { value: null, matchMode: FilterMatchMode.EQUALS },
  value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
})
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
      v-model:filters="filters"
      :globalFilterFields="[
        'id_product',
        'id_supplier',
        'supplier',
        'id_manufacturer',
        'manufacturer',
        'reference',
        'active',
        'id_lang',
        'name',
        'id_shop',
        'id_category_default',
        'category',
        'id_feature',
        'feature',
        'id_feature_value',
        'value',
      ]"
      :loading="loading"
      v-show="products"
      class="border-t border-gray-200"
      stripedRows
    >
      <template #header>
        <Toolbar>
          <template #center>
            <InputText
              v-model="filters['global'].value"
              placeholder="Keyword Search"
            />
          </template>
          <template #end>
            <ProductDelete
              :feature-id="
                leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
              "
              :feature-value-id="
                leftSelectedFeatureValue
                  ? leftSelectedFeatureValue.id_feature_value
                  : undefined
              "
              :product-ids="selectedProductIds"
            />
            <ProductMove
              class="ml-3"
              :new-feature-id="
                rightSelectedFeature
                  ? rightSelectedFeature.id_feature
                  : undefined
              "
              :new-feature-value-id="
                rightSelectedFeatureValue
                  ? rightSelectedFeatureValue.id_feature_value
                  : undefined
              "
              :feature-id="
                leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
              "
              :feature-value-id="
                leftSelectedFeatureValue
                  ? leftSelectedFeatureValue.id_feature_value
                  : undefined
              "
              :product-ids="selectedProductIds"
            />
            <ProductCopy
              class="ml-3"
              :new-feature-id="
                rightSelectedFeature
                  ? rightSelectedFeature.id_feature
                  : undefined
              "
              :new-feature-value-id="
                rightSelectedFeatureValue
                  ? rightSelectedFeatureValue.id_feature_value
                  : undefined
              "
              :feature-id="
                leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
              "
              :feature-value-id="
                leftSelectedFeatureValue
                  ? leftSelectedFeatureValue.id_feature_value
                  : undefined
              "
              :product-ids="selectedProductIds"
            />
          </template>
        </Toolbar>
      </template>
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
      <Column header="Unlink">
        <template #body="{ data }">
          <ProductDelete
            :feature-id="
              leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
            "
            :feature-value-id="
              leftSelectedFeatureValue
                ? leftSelectedFeatureValue.id_feature_value
                : undefined
            "
            :product-ids="[data.id_product]"
          />
        </template>
      </Column>
      <Column header="Move">
        <template #body="{ data }">
          <ProductMove
            :new-feature-id="
              rightSelectedFeature ? rightSelectedFeature.id_feature : undefined
            "
            :new-feature-value-id="
              rightSelectedFeatureValue
                ? rightSelectedFeatureValue.id_feature_value
                : undefined
            "
            :feature-id="
              leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
            "
            :feature-value-id="
              leftSelectedFeatureValue
                ? leftSelectedFeatureValue.id_feature_value
                : undefined
            "
            :product-ids="[data.id_product]"
          />
        </template>
      </Column>
      <Column header="Add to right column">
        <template #body="{ data }">
          <ProductCopy
            :new-feature-id="
              rightSelectedFeature ? rightSelectedFeature.id_feature : undefined
            "
            :new-feature-value-id="
              rightSelectedFeatureValue
                ? rightSelectedFeatureValue.id_feature_value
                : undefined
            "
            :feature-id="
              leftSelectedFeature ? leftSelectedFeature.id_feature : undefined
            "
            :feature-value-id="
              leftSelectedFeatureValue
                ? leftSelectedFeatureValue.id_feature_value
                : undefined
            "
            :product-ids="[data.id_product]"
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
