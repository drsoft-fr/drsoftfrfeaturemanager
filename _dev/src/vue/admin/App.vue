<script setup>
import { computed, provide, readonly, ref, watch } from 'vue'
import ConfirmDialog from 'primevue/confirmdialog'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueDuplicate from '@/vue/admin/components/feature-value/Duplicate.vue'
import FeatureValueRelocate from '@/vue/admin/components/feature-value/Relocate.vue'
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
const leftSelectedFeatureId = computed(() =>
  leftSelectedFeature.value ? leftSelectedFeature.value.id_feature : 0,
)
const rightSelectedFeatureId = computed(() =>
  rightSelectedFeature.value ? rightSelectedFeature.value.id_feature : 0,
)
const leftSelectedFeatureValue = ref()
const rightSelectedFeatureValue = ref()
const leftSelectedFeatureValueId = computed(() =>
  leftSelectedFeatureValue.value
    ? leftSelectedFeatureValue.value.id_feature_value
    : 0,
)
const rightSelectedFeatureValueId = computed(() =>
  rightSelectedFeatureValue.value
    ? rightSelectedFeatureValue.value.id_feature_value
    : 0,
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

/**
 * Duplicates a feature value by sending a POST request to the server.
 *
 * @param {number} featureValueId - The ID of the feature value to be duplicated.
 * @param {number} featureId - The ID of the feature associated with the value.
 *
 * @returns {Promise<void>} - A promise that resolves once the duplication request is completed.
 */
const featureValueDuplicate = async (featureValueId, featureId) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())

  await fetch(drsoftfrfeaturemanager.routes.featureValueDuplicate, {
    method: 'POST',
    body: form,
  })
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
 * Relocates a specific feature value to a new feature within the system.
 *
 * @param {number} featureValueId - The ID of the feature value to be relocated.
 * @param {number} featureId - The ID of the current feature that the value belongs to.
 * @param {number} newFeatureId - The ID of the new feature that the value will be moved to.
 *
 * @returns {Promise<object>} - A Promise that resolves to the JSON response of the relocation operation.
 */
const featureValueRelocate = async (
  featureValueId,
  featureId,
  newFeatureId,
) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())
  form.append('new_id_feature', newFeatureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueRelocate, {
    method: 'POST',
    body: form,
  })

  return await res.json()
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

/**
 * Relocates a product to a new feature value within a given feature.
 *
 * @param {Array<number>} productIds - Array of product IDs to relocate.
 * @param {number} newFeatureValueId - ID of the new feature value to assign to the product.
 * @param {number} newFeatureId - ID of the new feature in which the product will be located.
 * @param {number} featureValueId - ID of the current feature value of the product.
 * @param {number} featureId - ID of the feature where the product is currently located.
 *
 * @returns {Promise<Object>} - A promise that resolves to the JSON response from the API after relocating the product.
 */
const productRelocate = async (
  productIds,
  newFeatureValueId,
  newFeatureId,
  featureValueId,
  featureId,
) => {
  const form = new FormData()

  form.append('id_products', productIds.join(','))
  form.append('new_id_feature_value', newFeatureValueId.toString())
  form.append('new_id_feature', newFeatureId.toString())
  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.productRelocate, {
    method: 'POST',
    body: form,
  })

  return await res.json()
}

/**
 * Adds a product to the right column with the specified feature values.
 *
 * @param {Array<number>} productIds - The array of product IDs to which to add the new feature value.
 * @param {number} newFeatureValueId - The ID of the new feature value to add to the products.
 * @param {number} newFeatureId - The ID of the feature to which the new feature value belongs.
 *
 * @returns {Promise<Object>} - A Promise that resolves to the response JSON object from the server.
 */
const productAddToRightColumn = async (
  productIds,
  newFeatureValueId,
  newFeatureId,
) => {
  const form = new FormData()

  form.append('id_products', productIds.join(','))
  form.append('new_id_feature_value', newFeatureValueId.toString())
  form.append('new_id_feature', newFeatureId.toString())

  const res = await fetch(
    drsoftfrfeaturemanager.routes.productAddToRightColumn,
    {
      method: 'POST',
      body: form,
    },
  )

  return await res.json()
}

provide('feature', {
  create: readonly(featureCreate),
  delete: readonly(featureDelete),
  leftSelectedFeature,
  rightSelectedFeature,
  leftSelectedFeatureId: readonly(leftSelectedFeatureId),
  rightSelectedFeatureId: readonly(rightSelectedFeatureId),
  features: readonly(features),
  getAll: readonly(featureGetAll),
})
provide('featureValue', {
  create: readonly(featureValueCreate),
  delete: readonly(featureValueDelete),
  duplicate: readonly(featureValueDuplicate),
  leftFeatureValues: readonly(leftFeatureValues),
  rightFeatureValues: readonly(rightFeatureValues),
  leftFeatureValueTableLoading,
  rightFeatureValueTableLoading,
  get: readonly(featureValueGet),
  leftSelectedFeatureValueId: readonly(leftSelectedFeatureValueId),
  rightSelectedFeatureValueId: readonly(rightSelectedFeatureValueId),
  leftSelectedFeatureValue,
  rightSelectedFeatureValue,
  relocate: readonly(featureValueRelocate),
})
provide('product', {
  delete: readonly(productDelete),
  products: readonly(products),
  productTableLoading,
  get: readonly(productGet),
  relocate: readonly(productRelocate),
  addToRightColumn: readonly(productAddToRightColumn),
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
      <div class="col-span-2">
        <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
          Source
        </h2>
        <div class="flex flex-col gap-8">
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
      </div>
      <div>
        <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
          Action
        </h2>
        <div class="flex flex-col gap-8 justify-between">
          <FeatureValueDuplicate />
          <FeatureValueRelocate />
        </div>
      </div>
      <div class="col-span-2">
        <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
          Destination
        </h2>
        <div class="flex flex-col gap-8 justify-between">
          <FeatureSelect selection="right" />
          <FeatureValueTable selection="right" />
          <FeatureValueCreate selection="right" />
        </div>
      </div>
    </div>
    <div
      class="py-8 px-4 lg:py-16 lg:px-6 mx-auto mt-10 sm:mt-16 lg:mx-0 bg-white dark:bg-gray-900 rounded-md"
    >
      <h2 class="text-5xl font-semibold tracking-tight sm:text-7xl">
        Products
      </h2>
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
