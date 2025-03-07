<script setup>
import { computed, ref } from 'vue'

const { drsoftfrfeaturemanager } = window
const features = ref({})
const featureValues = ref({})
const selectedFeatureId = ref(0)
const selectedFeatureValueIds = ref([])
const selectAll = ref(true)

const feature = computed(() => {
  if (!Array.isArray(features.value)) {
    return {}
  }

  return (
    features.value.find(
      (feature) => feature.id_feature === selectedFeatureId.value,
    ) || {}
  )
})

const assignFeature = async (json) => {
  features.value = json
  selectedFeatureId.value =
    selectedFeatureId.value === 0 ? json[0].id_feature : selectedFeatureId.value
}

const assignFeatureValue = async (json) => {
  featureValues.value = json
  selectedFeatureValueIds.value = []
}

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
  return await res.json()
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
  const form = new FormData()

  form.append('id_feature', featureId.toString())

  const res = await fetch(drsoftfrfeaturemanager.routes.featureValueGet, {
    method: 'POST',
    body: form,
  })
  return await res.json()
}

const handleFeatureSelect = (event) => {
  selectedFeatureId.value = parseInt(event.target.value)
  selectedFeatureValueIds.value = []
  featureValueGet(selectedFeatureId.value).then((json) =>
    assignFeatureValue(json),
  )
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

const handleFeatureCreate = async (event) => {
  await featureCreate(event.currentTarget)
  const json = await featureGetAll()
  await assignFeature(json)
}

const handleFeatureDelete = async (event) => {
  await featureDelete(parseInt(event.target.dataset.featureId || ''))
  const json = await featureGetAll()
  await assignFeature(json)
}

const handleFeatureValueCreate = async (event) => {
  const featureId = parseInt(event.currentTarget.dataset.featureId || '')
  await featureValueCreate(event.currentTarget)
  const json = await featureValueGet(featureId)
  await assignFeatureValue(json)
}

const handleFeatureValueDelete = async (event) => {
  const featureId = parseInt(event.target.dataset.featureId || '')
  await featureValueDelete(parseInt(event.target.dataset.featureValueId || ''))
  const json = await featureValueGet(featureId)
  await assignFeatureValue(json)
}

featureGetAll()
  .then((json) => assignFeature(json))
  .then(() => featureValueGet(selectedFeatureId.value))
  .then((json) => assignFeatureValue(json))
</script>

<template>
  <main class="card">
    <div class="card-body">
      <div class="row">
        <div class="col col-md-6">
          <div class="row">
            <div class="col">
              <div>
                <label for="features" class="form-label">Features</label>
                <select
                  id="features"
                  class="form-control form-select"
                  @change="handleFeatureSelect"
                >
                  <option>No feature</option>
                  <option
                    v-for="feature in features"
                    :key="feature.id_feature"
                    :value="feature.id_feature"
                    :selected="selectedFeatureId === feature.id_feature"
                  >
                    {{ feature.name }}
                  </option>
                </select>
              </div>
              <div class="mt-3 text-right">
                <button
                  type="button"
                  class="js-feature-delete btn btn-danger"
                  :class="{ disabled: !selectedFeatureId }"
                  :data-feature-id="selectedFeatureId"
                  @click="handleFeatureDelete"
                  :disabled="!selectedFeatureId"
                >
                  Delete
                </button>
              </div>
            </div>
            <div class="col">
              <form @submit.prevent="handleFeatureCreate">
                <div class="form-group">
                  <label class="form-label" for="create-feature">
                    Create new feature
                  </label>
                  <input
                    class="form-control"
                    type="text"
                    id="create-feature"
                    name="name"
                  />
                  <div class="form-text">
                    Would you like to create a new feature?
                  </div>
                </div>
                <div class="form-group">
                  <button class="btn btn-primary" type="submit">Create</button>
                </div>
              </form>
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
                      <button
                        type="button"
                        class="btn btn-danger"
                        @click="handleFeatureValueDelete"
                        :data-feature-value-id="featureValue.id_feature_value"
                        :data-feature-id="feature.id_feature"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </TransitionGroup>
              </table>
            </div>
          </Transition>
          <div class="mt-3">
            <form
              @submit.prevent="handleFeatureValueCreate"
              :data-feature-id="selectedFeatureId"
            >
              <input
                type="hidden"
                name="id_feature"
                :value="selectedFeatureId"
              />
              <div class="form-group">
                <label class="form-label" for="create-feature-value">
                  Create value for {{ feature.name }} feature
                </label>
                <input
                  class="form-control"
                  type="text"
                  id="create-feature-value"
                  name="value"
                />
                <div class="form-text">
                  Would you like to create feature value?
                </div>
              </div>
              <div class="form-group">
                <button class="btn btn-primary" type="submit">Create</button>
              </div>
            </form>
          </div>
        </div>
        <div class="col col-md-6"></div>
      </div>
      <div>
        <div class="row">
          <div class="col">Selected feature: #{{ selectedFeatureId }}</div>
          <div class="col">
            Selected featureValues: #{{ selectedFeatureValueIds.join(',') }}
          </div>
        </div>
        <div class="row">
          <div class="col mt-3">
            <pre>
          <samp>
            {{ features }}
          </samp>
        </pre>
          </div>
          <div class="col mt-3">
            <pre>
          <samp>
            {{ featureValues }}
          </samp>
        </pre>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
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
