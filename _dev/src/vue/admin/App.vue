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
const leftFeatureValues = ref([])
const rightFeatureValues = ref([])
const products = ref([])
const leftSelectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const rightSelectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const leftSelectedFeatureValue = ref()
const rightSelectedFeatureValue = ref()
const leftSelectedFeatureValueId = computed(
  () => leftSelectedFeatureValue.value.id_feature_value,
)
const rightSelectedFeatureValueId = computed(
  () => rightSelectedFeatureValue.value.id_feature_value,
)
const selectedProducts = ref([])
const selectedProductIds = computed(() =>
  selectedProducts.value.map((product) => product.id_product),
)
const leftFeatureValueTableLoading = ref(false)
const rightFeatureValueTableLoading = ref(false)
const productTableLoading = ref(false)

watch(leftSelectedFeature, async () => {
  leftFeatureValueTableLoading.value = true
  productTableLoading.value = true
  leftSelectedFeatureValue.value = undefined
  selectedProducts.value = []
  await featureValueGet(leftSelectedFeature.value.id_feature, 'left')
  leftFeatureValueTableLoading.value = false
  productTableLoading.value = false
})

watch(rightSelectedFeature, async () => {
  rightFeatureValueTableLoading.value = true
  rightSelectedFeatureValue.value = undefined
  await featureValueGet(rightSelectedFeature.value.id_feature, 'right')
  rightFeatureValueTableLoading.value = false
})

watch(leftSelectedFeatureValue, async () => {
  productTableLoading.value = true
  selectedProducts.value = []
  await productGet(
    leftSelectedFeature.value.id_feature,
    leftSelectedFeatureValueId.value,
  )
  productTableLoading.value = false
})

const featureCreate = async (elm) => {
  const res = await fetch(drsoftfrfeaturemanager.routes.featureCreate, {
    method: 'POST',
    body: new FormData(elm),
  })
  const { id_feature, name } = await res.json()

  leftSelectedFeature.value = { id_feature, name }
}

const featureDelete = async (featureId) => {
  const form = new FormData()

  form.append('id_feature', featureId.toString())

  await fetch(drsoftfrfeaturemanager.routes.featureDelete, {
    method: 'POST',
    body: form,
  })
  leftSelectedFeature.value = { id_feature: 0, name: 'Sample feature' }

  if (featureId === rightSelectedFeature.value.id_feature) {
    rightSelectedFeature.value = { id_feature: 0, name: 'Sample feature' }
  }
}

const featureGetAll = async () => {
  const res = await fetch(drsoftfrfeaturemanager.routes.featureGetAll)
  features.value = await res.json()
  leftFeatureValues.value = []
  rightFeatureValues.value = []

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

  if (
    typeof leftSelectedFeatureValue.value !== 'undefined' &&
    featureValueId === leftSelectedFeatureValue.value.id_feature_value
  ) {
    leftSelectedFeatureValue.value = undefined
  }

  if (
    typeof rightSelectedFeatureValue.value !== 'undefined' &&
    featureValueId === rightSelectedFeatureValue.value.id_feature_value
  ) {
    rightSelectedFeatureValue.value = undefined
  }
}

const featureValueGet = async (featureId, selection, all = false) => {
  if (typeof featureId !== 'number' || isNaN(featureId) || 0 >= featureId) {
    return []
  }

  const form = new FormData()

  form.append('id_feature', featureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueGet, {
    method: 'POST',
    body: form,
  })

  const json = await res.json()

  if ('left' === selection) {
    leftFeatureValues.value = json

    if (true === all) {
      rightFeatureValues.value = json
    }

    return leftFeatureValues
  } else {
    rightFeatureValues.value = json

    if (true === all) {
      leftFeatureValues.value = json
    }

    return rightFeatureValues
  }
}

/**
 * Asynchronously deletes a product by sending a POST request with the specified product ids, feature value id, and feature id.
 *
 * @param {Array<number>} productIds - An array of product IDs to be deleted.
 * @param {number} featureValueId - The ID of the feature value associated with the product.
 * @param {number} featureId - The ID of the feature associated with the product.
 *
 * @returns {void}
 */
const productDelete = async (productIds, featureValueId, featureId) => {
  const form = new FormData()

  form.append('id_products', productIds.join(','))
  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())

  await fetch(drsoftfrfeaturemanager.routes.productDelete, {
    method: 'POST',
    body: form,
  })

  selectedProducts.value = selectedProducts.value.filter(
    (product) => !productIds.includes(product.id_product),
  )
}

const productGet = async (featureId, featureValueId) => {
  const form = new FormData()

  form.append('id_feature', featureId.toString())
  form.append('id_feature_value', featureValueId.toString())

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
  leftSelectedFeature,
  rightSelectedFeature,
  features: readonly(features),
  getAll: readonly(featureGetAll),
})
provide('featureValue', {
  create: readonly(featureValueCreate),
  delete: readonly(featureValueDelete),
  leftFeatureValues: readonly(leftFeatureValues),
  rightFeatureValues: readonly(rightFeatureValues),
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
  get: readonly(featureValueGet),
  leftSelectedFeatureValueId: readonly(leftSelectedFeatureValueId),
  rightSelectedFeatureValueId: readonly(rightSelectedFeatureValueId),
  leftSelectedFeatureValue,
  rightSelectedFeatureValue,
})
provide('product', {
  delete: readonly(productDelete),
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
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto grid grid-cols-1 gap-x-8 gap-y-16 lg:mx-0 lg:grid-cols-5 bg-white dark:bg-gray-900 rounded-md"
    >
      <div class="flex flex-col col-span-2 gap-8">
        <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
          Source
        </h2>
        <div class="grid grid-cols-2 gap-8 justify-between">
          <div>
            <FeatureSelect selection="left" />
            <div class="mt-3 text-right">
              <FeatureDelete />
            </div>
          </div>
          <FeatureCreate />
        </div>
        <FeatureValueTable selection="left" />
        <FeatureValueCreate selection="left" />
      </div>
      <div class="flex flex-col gap-8 justify-between"></div>
      <div class="flex flex-col col-span-2 gap-8 justify-between">
        <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
          Destination
        </h2>
        <FeatureSelect selection="right" />
        <FeatureValueTable selection="right" />
        <FeatureValueCreate selection="right" />
      </div>
    </div>
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto mt-10 sm:mt-16 lg:mx-0 bg-white dark:bg-gray-900 rounded-md"
    >
      <ProductTable />
    </div>
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto mt-10 grid grid-cols-1 gap-x-8 gap-y-16 sm:mt-16 lg:mx-0 lg:grid-cols-4 bg-white dark:bg-gray-900 rounded-md"
    >
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Left Selected feature:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ leftSelectedFeature }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Right Selected feature:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ rightSelectedFeature }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Feature:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ features }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Left Selected featureValue:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ leftSelectedFeatureValue }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Right Selected featureValue:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ rightSelectedFeatureValue }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Left FeatureValues:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ leftFeatureValues }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Right FeatureValues:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ rightFeatureValues }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
        <h3
          class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600"
        >
          Products:
        </h3>
        <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
          <pre><code>{{ products }}</code></pre>
        </div>
      </div>
      <div class="flex flex-col items-start">
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
