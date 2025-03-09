<script setup>
import { provide, readonly, ref, watch } from 'vue'
import FeatureCreate from '@/vue/admin/components/feature/Create.vue'
import FeatureDelete from '@/vue/admin/components/feature/Delete.vue'
import FeatureSelect from '@/vue/admin/components/feature/Select.vue'
import FeatureValueCreate from '@/vue/admin/components/feature-value/Create.vue'
import FeatureValueDelete from '@/vue/admin/components/feature-value/Delete.vue'

const { drsoftfrfeaturemanager } = window
const features = ref([])
const featureValues = ref([])
const selectedFeature = ref({ name: 'Sample feature', id_feature: 0 })
const selectedFeatureId = ref(0)
const selectedFeatureValueIds = ref([])
const selectAll = ref(true)

watch(selectedFeature, async () => {
  selectedFeatureValueIds.value = []
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

const handleFeatureValueSelect = (event) => {
  if (event.target.checked) {
    selectedFeatureValueIds.value.push(event.target.value)
  } else {
    selectedFeatureValueIds.value = selectedFeatureValueIds.value.filter(
      (id) => id !== event.target.value,
    )
  }
}

const handleToggleSelect = () => {
  let ids = []
  Array.from(document.querySelectorAll('.js-feature-value-select')).map(
    (elm) => {
      elm.checked = true
      ids.push(parseInt(elm.value))
    },
  )

  selectAll.value = false
  selectedFeatureValueIds.value = ids
}

const handleToggleUnselect = () => {
  Array.from(document.querySelectorAll('.js-feature-value-select')).map(
    (elm) => (elm.checked = false),
  )

  selectAll.value = true
  selectedFeatureValueIds.value = []
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
  get: readonly(featureValueGet),
})
provide('selectedFeatureId', {
  selectedFeatureId: readonly(selectedFeatureId),
  update: selectedFeatureIdUpdate,
})
provide('selectedFeatureValueIds', readonly(selectedFeatureValueIds))
provide('selectAll', readonly(selectAll))

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
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th class="position-relative" width="20%">
                      <TransitionGroup name="replace">
                        <button
                          v-if="true === selectAll"
                          class="btn btn-link"
                          type="button"
                          @click="handleToggleSelect"
                        >
                          <i aria-hidden="true" class="material-icons">check</i>
                          Select
                        </button>
                        <button
                          v-else
                          class="btn btn-link"
                          type="button"
                          @click="handleToggleUnselect"
                        >
                          <i aria-hidden="true" class="material-icons">close</i>
                          Unselect
                        </button>
                      </TransitionGroup>
                    </th>
                    <th width="20%">ID</th>
                    <th width="20%">Name</th>
                    <th width="20%">Is custom</th>
                    <th width="20%">Action</th>
                  </tr>
                </thead>
                <TransitionGroup name="slide" tag="tbody">
                  <tr
                    v-for="featureValue in featureValues"
                    :key="featureValue.id_feature_value"
                  >
                    <td>
                      <label
                        :for="`feature-value-${featureValue.id_feature_value}`"
                        class="d-block m-0 w-100"
                      >
                        <input
                          class="align-middle js-feature-value-select p-5"
                          type="checkbox"
                          :id="`feature-value-${featureValue.id_feature_value}`"
                          :value="featureValue.id_feature_value"
                          @change="handleFeatureValueSelect"
                      /></label>
                    </td>
                    <td>{{ featureValue.id_feature_value }}</td>
                    <td>{{ featureValue.value }}</td>
                    <td>{{ featureValue.custom }}</td>
                    <td>
                      <FeatureValueDelete
                        :feature-id="selectedFeature.id_feature"
                        :feature-value-id="featureValue.id_feature_value"
                      />
                    </td>
                  </tr>
                </TransitionGroup>
              </table>
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
            <pre><code>{{ selectedFeatureValueIds.join(',') }}</code></pre>
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

.slide-enter-active,
.slide-leave-active {
  transition: all 0.5s ease;
}

.slide-leave-active {
  position: absolute;
  width: 100%;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-30%);
}
</style>
