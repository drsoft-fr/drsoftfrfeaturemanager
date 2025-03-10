<script setup>
import { provide, readonly, ref, watch } from 'vue'
import ConfirmDialog from 'primevue/confirmdialog'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueTable from '@/vue/admin/components/feature-value/Table.vue'
import Toast from 'primevue/toast'

const { drsoftfrfeaturemanager } = window
const features = ref([])
const featureValues = ref([])
const selectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const selectedFeatureValues = ref([])
const featureValueTableLoading = ref(false)

watch(selectedFeature, async () => {
  featureValueTableLoading.value = true
  selectedFeatureValues.value = []
  await featureValueGet(selectedFeature.value.id_feature)
  featureValueTableLoading.value = false
})

const featureCreate = async (elm) => {
  await fetch(drsoftfrfeaturemanager.routes.featureCreate, {
    method: 'POST',
    body: new FormData(elm),
  })
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
  selectedFeatureValues,
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
    </div>
    <ConfirmDialog></ConfirmDialog>
    <Toast />
  </main>
</template>

<style scoped lang="scss"></style>
