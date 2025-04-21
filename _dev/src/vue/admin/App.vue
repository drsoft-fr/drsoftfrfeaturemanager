<script setup>
import { computed, provide, readonly, ref, watch } from 'vue'
import ConfirmDialog from 'primevue/confirmdialog'
import Divider from 'primevue/divider'
import Splitter from 'primevue/splitter'
import SplitterPanel from 'primevue/splitterpanel'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueCopy from '@/vue/admin/components/feature-value/Copy.vue'
import FeatureValueDuplicate from '@/vue/admin/components/feature-value/Duplicate.vue'
import FeatureValueMove from '@/vue/admin/components/feature-value/Move.vue'
import FeatureValueTable from '@/vue/admin/components/feature-value/Table.vue'
import ProductTable from '@/vue/admin/components/product/Table.vue'
import Orphan from '@/vue/admin/components/orphan/Main.vue'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const { drsoftfrfeaturemanager } = window
const routes = drsoftfrfeaturemanager.routes || {}
const features = ref([])
const leftFeatureValues = ref([])
const rightFeatureValues = ref([])
const products = ref([])
const leftSelectedFeature = ref()
const rightSelectedFeature = ref()
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
const toastLifetime = ref(5000)
const toast = useToast()

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

/**
 * Asynchronously fetches all features from a specific route and populates the leftFeatureValues and rightFeatureValues arrays with the results.
 *
 * @returns {Promise<Object>} A Promise that resolves with the JSON response containing the features.
 */
const featureGetAll = async () => {
  const res = await fetch(drsoftfrfeaturemanager.routes.featureGetAll)
  const json = await res.json()

  features.value = json.features
  leftFeatureValues.value = []
  rightFeatureValues.value = []

  return json
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

/**
 * Asynchronously fetches feature values based on the specified feature ID and selection.
 *
 * @param {number} featureId - The ID of the feature to retrieve values for.
 * @param {string} selection - The selection indicating whether to update 'left' or 'right' feature values.
 * @param {boolean} [all=false] - Indicates whether to update both 'left' and 'right' feature values.
 *
 * @returns {object} Returns the updated feature values based on the selection and optional 'all' parameter.
 *
 * @throws {Error} Throws an error if featureId is not a number or is less than or equal to 0.
 */
const featureValueGet = async (featureId, selection, all = false) => {
  if (typeof featureId !== 'number' || isNaN(featureId) || 0 >= featureId) {
    throw new Error('featureId must be a number')
  }

  const form = new FormData()

  form.append('id_feature', featureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueGet, {
    method: 'POST',
    body: form,
  })
  const json = await res.json()

  if ('left' === selection) {
    leftFeatureValues.value = json.feature_values

    if (true === all) {
      rightFeatureValues.value = json.feature_values
    }
  } else {
    rightFeatureValues.value = json.feature_values

    if (true === all) {
      leftFeatureValues.value = json.feature_values
    }
  }

  return json
}

/**
 * Moves a specific feature value to a new feature within the system.
 *
 * @param {number} featureValueId - The ID of the feature value to be relocated.
 * @param {number} featureId - The ID of the current feature that the value belongs to.
 * @param {number} newFeatureId - The ID of the new feature that the value will be moved to.
 *
 * @returns {Promise<object>} - A Promise that resolves to the JSON response of the relocation operation.
 */
const featureValueMove = async (featureValueId, featureId, newFeatureId) => {
  const form = new FormData()

  form.append('id_feature_value', featureValueId.toString())
  form.append('id_feature', featureId.toString())
  form.append('new_id_feature', newFeatureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueMove, {
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
 * Moves a product to a new feature value within a given feature.
 *
 * @param {Array<number>} productIds - Array of product IDs to relocate.
 * @param {number} newFeatureValueId - ID of the new feature value to assign to the product.
 * @param {number} newFeatureId - ID of the new feature in which the product will be located.
 * @param {number} featureValueId - ID of the current feature value of the product.
 * @param {number} featureId - ID of the feature where the product is currently located.
 *
 * @returns {Promise<Object>} - A promise that resolves to the JSON response from the API after relocating the product.
 */
const productMove = async (
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

  const res = await fetch(drsoftfrfeaturemanager.routes.productMove, {
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
const productCopy = async (productIds, newFeatureValueId, newFeatureId) => {
  const form = new FormData()

  form.append('id_products', productIds.join(','))
  form.append('new_id_feature_value', newFeatureValueId.toString())
  form.append('new_id_feature', newFeatureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.productCopy, {
    method: 'POST',
    body: form,
  })

  return await res.json()
}

/**
 * Resets the state of selected products and related feature values.
 *
 * This asynchronous function clears the current selection state by:
 * - Emptying the array of selected products.
 * - Clearing the values of the left and right selected feature values.
 * - Clearing the values of the left and right selected features.
 *
 * Designed to be called when a reset or reinitialization of the selection state is needed.
 */
const reset = async () => {
  selectedProducts.value = []
  leftSelectedFeatureValue.value = undefined
  rightSelectedFeatureValue.value = undefined
  leftSelectedFeature.value = undefined
  rightSelectedFeature.value = undefined
}

provide('feature', {
  leftSelectedFeature,
  rightSelectedFeature,
  leftSelectedFeatureId: readonly(leftSelectedFeatureId),
  rightSelectedFeatureId: readonly(rightSelectedFeatureId),
  features: readonly(features),
  getAll: readonly(featureGetAll),
})
provide('featureValue', {
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
  move: readonly(featureValueMove),
})
provide('product', {
  copy: readonly(productCopy),
  delete: readonly(productDelete),
  products: readonly(products),
  productTableLoading,
  get: readonly(productGet),
  move: readonly(productMove),
  selectedProductIds: readonly(selectedProductIds),
  selectedProducts,
})
provide('routes', routes)
provide('toast', {
  lifetime: readonly(toastLifetime),
})
provide('core', {
  reset: readonly(reset),
})

toast.add({
  severity: 'info',
  summary: 'Loading',
  detail: 'Loading features in progress ...',
  life: toastLifetime.value,
})
featureGetAll().then((res) =>
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: toastLifetime.value,
  }),
)
</script>

<template>
  <main class="sm:text-lg">
    <Splitter class="bg-white dark:bg-gray-900 rounded-md" layout="vertical">
      <SplitterPanel :size="80">
        <Splitter>
          <SplitterPanel class="py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0">
            <div class="flex flex-col gap-8 justify-between h-100">
              <h2 class="flex-none">Source</h2>
              <div class="flex-none grid grid-cols-2 gap-8 justify-between">
                <FeatureSelect selection="left" />
                <FeatureCreate />
              </div>
              <FeatureValueTable class="grow" selection="left" />
              <FeatureValueCreate class="flex-none" selection="left" />
            </div>
          </SplitterPanel>
          <SplitterPanel class="py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0">
            <div class="flex flex-col gap-8 justify-between h-100">
              <h2 class="flex-none">Destination</h2>
              <FeatureSelect class="flex-none" selection="right" />
              <FeatureValueTable class="grow" selection="right" />
              <FeatureValueCreate class="flex-none" selection="right" />
            </div>
          </SplitterPanel>
        </Splitter>
      </SplitterPanel>
      <SplitterPanel
        class="py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0"
        :size="20"
      >
        <h2 class="mb-8">Action</h2>
        <div class="flex flex-row gap-8 justify-between">
          <FeatureValueCopy />
          <Divider layout="vertical" />
          <FeatureValueDuplicate />
          <Divider layout="vertical" />
          <FeatureValueMove />
        </div>
      </SplitterPanel>
    </Splitter>
    <div
      class="mt-10 sm:mt-16 py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0 bg-white dark:bg-gray-900 rounded-md"
    >
      <h2 class="mb-5 sm:mb-8">Products</h2>
      <ProductTable />
    </div>
    <Orphan />
    <div
      class="grid grid-cols-1 gap-x-8 gap-y-16 lg:grid-cols-4 mt-10 sm:mt-16 py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0 bg-white dark:bg-gray-900 rounded-md"
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
