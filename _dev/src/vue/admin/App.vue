<script setup>
import { provide, readonly, ref, watch } from 'vue'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueTable from '@/vue/admin/components/feature-value/Table.vue'

const { drsoftfrfeaturemanager } = window
const features = ref([])
const featureValues = ref([])
const selectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const selectedFeatureId = ref(0)
const selectedFeatureValues = ref([])

watch(selectedFeature, async () => {
  selectedFeatureValues.value = []
  await featureValueGet(selectedFeature.value.id_feature)
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

const selectedFeatureIdUpdate = async (featureId) => {
  if (typeof featureId !== 'number' || isNaN(featureId)) {
    throw new Error('featureId must be a number')
  }

  selectedFeatureId.value = featureId
}

provide('feature', {
  create: readonly(featureCreate),
  delete: readonly(featureDelete),
  feature: selectedFeature,
  features: readonly(features),
  getAll: readonly(featureGetAll),
  selectedFeatureId: readonly(selectedFeatureId),
})
provide('featureValue', {
  create: readonly(featureValueCreate),
  delete: readonly(featureValueDelete),
  featureValues: readonly(featureValues),
  get: readonly(featureValueGet),
  selectedFeatureValues,
})

featureGetAll()
</script>

<template>
  <main class="card">
    <div class="card-body">
      <div class="row">
        <div class="col col-md-6">
          <div class="row">
            <div class="col">
              <FeatureSelect />
              <div class="mt-3 text-right">
                <FeatureDelete />
              </div>
            </div>
            <div class="col">
              <FeatureCreate />
            </div>
          </div>
          <Transition name="fade" mode="out-in" appear>
            <div
              v-if="featureValues"
              class="mt-3 position-relative table-responsive"
            >
              <FeatureValueTable />
            </div>
          </Transition>
          <div class="mt-3">
            <FeatureValueCreate />
          </div>
        </div>
        <div class="col col-md-6"></div>
      </div>
      <div
        class="mx-auto mt-10 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 border-t border-gray-200 pt-10 sm:mt-16 sm:pt-16 lg:mx-0 lg:max-w-none lg:grid-cols-4"
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
            Selected feature ID:
          </h3>
          <div class="mt-5 line-clamp-3 text-sm/6 text-gray-600">
            <pre><code>{{ selectedFeatureId }}</code></pre>
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
    </div>
  </main>
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

.replace-enter-active,
.replace-leave-active {
  transition: all 0.5s ease;
}

.replace-leave-active {
  position: absolute;
}

.replace-enter-from,
.replace-leave-to {
  opacity: 0;
}
</style>
