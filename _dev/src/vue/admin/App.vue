<script setup>
import { computed, provide, readonly, ref, watch } from 'vue'
import ConfirmDialog from 'primevue/confirmdialog'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueTable from '@/vue/admin/components/feature-value/Table.vue'
import ProductTable from '@/vue/admin/components/product/Table.vue'
import Toast from 'primevue/toast'

const { drsoftfrfeaturemanager } = window
const features = ref([])
const featureValues = ref([])
const products = ref([])
const selectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const selectedFeatureValues = ref([])
const selectedFeatureValueIds = computed(() =>
  selectedFeatureValues.value.map(
    (featureValue) => featureValue.id_feature_value,
  ),
)
const selectedProducts = ref([])
const selectedProductIds = computed(() =>
  selectedProducts.value.map((product) => product.id_product),
)
const featureValueTableLoading = ref(false)
const productTableLoading = ref(false)

watch(selectedFeature, async () => {
  featureValueTableLoading.value = true
  productTableLoading.value = true
  selectedFeatureValues.value = []
  selectedProducts.value = []
  await featureValueGet(selectedFeature.value.id_feature)
  featureValueTableLoading.value = false
  productTableLoading.value = false
})

watch(selectedFeatureValues, async () => {
  productTableLoading.value = true
  selectedProducts.value = []
  await productGet(
    selectedFeature.value.id_feature,
    selectedFeatureValueIds.value,
  )
  productTableLoading.value = false
})

const featureCreate = async (elm) => {
  const res = await fetch(drsoftfrfeaturemanager.routes.featureCreate, {
    method: 'POST',
    body: new FormData(elm),
  })
  const { id_feature, name } = await res.json()

  selectedFeature.value = { id_feature, name }
}

const featureDelete = async (featureId) => {
  const form = new FormData()

  form.append('id_feature', featureId.toString())

  await fetch(drsoftfrfeaturemanager.routes.featureDelete, {
    method: 'POST',
    body: form,
  })
  selectedFeature.value = { id_feature: 0, name: 'Sample feature' }
}

const featureGetAll = async () => {
  const res = await fetch(drsoftfrfeaturemanager.routes.featureGetAll)
  features.value = await res.json()
  featureValues.value = []

  return features
}

const featureValueCreate = async (elm) => {
  await fetch(drsoftfrfeaturemanager.routes.featureValueCreate, {
    method: 'POST',
    body: new FormData(elm),
  })
}

const featureValueDelete = async (featureValueId) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())

  await fetch(drsoftfrfeaturemanager.routes.featureValueDelete, {
    method: 'POST',
    body: form,
  })

  selectedFeatureValues.value = selectedFeatureValues.value.filter(
    (featureValue) => featureValue.id_feature_value !== featureValueId,
  )
}

const featureValueGet = async (featureId) => {
  if (typeof featureId !== 'number' || isNaN(featureId) || 0 >= featureId) {
    return []
  }

  const form = new FormData()

  form.append('id_feature', featureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueGet, {
    method: 'POST',
    body: form,
  })

  featureValues.value = await res.json()

  return featureValues
}

const productGet = async (featureId, featureValueIds) => {
  const form = new FormData()

  form.append('id_feature', featureId.toString())
  form.append('id_feature_values', featureValueIds.join(','))

  const res = await fetch(drsoftfrfeaturemanager.routes.productGet, {
    method: 'POST',
    body: form,
  })

  products.value = await res.json()

  return products
}

provide('feature', {
  create: readonly(featureCreate),
  delete: readonly(featureDelete),
  feature: selectedFeature,
  features: readonly(features),
  getAll: readonly(featureGetAll),
})
provide('featureValue', {
  create: readonly(featureValueCreate),
  delete: readonly(featureValueDelete),
  featureValues: readonly(featureValues),
  featureValueTableLoading,
  get: readonly(featureValueGet),
  selectedFeatureValueIds: readonly(selectedFeatureValueIds),
  selectedFeatureValues,
})
provide('product', {
  products: readonly(products),
  productTableLoading,
  get: readonly(productGet),
  selectedProductIds: readonly(selectedProductIds),
  selectedProducts,
})

featureGetAll()
</script>

<template>
  <main class="sm:text-lg">
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-5 bg-white dark:bg-gray-900 rounded-md"
    >
      <div class="flex max-w-xl flex-col col-span-2 gap-8">
        <div class="grid max-w-2xl grid-cols-2 gap-8">
          <div>
            <FeatureSelect />
            <div class="mt-3 text-right">
              <FeatureDelete />
            </div>
          </div>
          <FeatureCreate />
        </div>
        <FeatureValueTable />
        <FeatureValueCreate />
      </div>
      <div class="flex max-w-xl flex-col items-start"></div>
      <div class="flex max-w-xl flex-col items-start col-span-2"></div>
    </div>
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto mt-10 max-w-2xl sm:mt-16 lg:mx-0 lg:max-w-none bg-white dark:bg-gray-900 rounded-md"
    >
      <ProductTable />
    </div>
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto mt-10 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 sm:mt-16 lg:mx-0 lg:max-w-none lg:grid-cols-4 bg-white dark:bg-gray-900 rounded-md"
    >
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Selected feature:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ selectedFeature }}</code></pre>
        </div>
      </div>
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Feature:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ features }}</code></pre>
        </div>
      </div>
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Selected featureValues:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ selectedFeatureValues }}</code></pre>
        </div>
      </div>
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          FeatureValues:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ featureValues }}</code></pre>
        </div>
      </div>
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Products:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ products }}</code></pre>
        </div>
      </div>
      <div class="flex max-w-xl flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Selected products:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ selectedProducts }}</code></pre>
        </div>
      </div>
    </div>
    <ConfirmDialog></ConfirmDialog>
    <Toast />
  </main>
</template>

<style scoped lang="scss"></style>
