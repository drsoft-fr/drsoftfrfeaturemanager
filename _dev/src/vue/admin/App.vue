<script setup>
import { computed, ref } from 'vue'

const { drsoftfrfeaturemanager } = window
const features = ref({})
const selectedFeatureId = ref(0)
const selectedFeatureValueIds = ref([])
const selectAll = ref(true)
const feature = computed(() => {
  if (!Array.isArray(features.value)) {
    return {}
  }

  return (
    features.value.find((feature) => feature.id === selectedFeatureId.value) ||
    {}
  )
})
const handleFeatureSelect = (event) => {
  selectedFeatureId.value = parseInt(event.target.value)
  selectedFeatureValueIds.value = []
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

const handleFeatureValueCreate = async (event) => {
  const form = new FormData(event.currentTarget)
  const res = await fetch(drsoftfrfeaturemanager.featureValueCreate, {
    method: 'POST',
    body: form,
  })
  await res.json()
}

fetch(drsoftfrfeaturemanager.getFeatures)
  .then((res) => res.json())
  .then((data) => {
    features.value = data
    selectedFeatureId.value = data[0].id
  })

const handleFeatureValueDelete = async (event) => {
  const featureValueId = parseInt(event.target.dataset.featureValueId || '')
  const form = new FormData()

  form.append('id', featureValueId.toString())

  const res = await fetch(drsoftfrfeaturemanager.featureValueDelete, {
    method: 'POST',
    body: form,
  })
  await res.json()
}
</script>

<template>
  <main class="card">
    <div class="card-body">
      <div class="row">
        <div class="col">feature: #{{ selectedFeatureId }}</div>
        <div class="col">
          featureValues: #{{ selectedFeatureValueIds.join(',') }}
        </div>
      </div>
      <div class="mt-3 row">
        <div class="col">
          <select @change="handleFeatureSelect">
            <option
              v-for="feature in features"
              :key="feature.id"
              :value="feature.id"
            >
              {{ feature.name }}
            </option>
          </select>
        </div>
        <div class="col">
          <form @submit.prevent="handleFeatureValueCreate">
            <input type="hidden" name="id_feature" :value="selectedFeatureId" />
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
      <Transition name="fade" mode="out-in" appear>
        <div
          v-if="feature.values"
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
              <tr v-for="featureValue in feature.values" :key="featureValue.id">
                <td>
                  <label
                    :for="`feature-value-${featureValue.id}`"
                    class="d-block m-0 w-100"
                  >
                    <input
                      class="align-middle js-feature-value-select p-5"
                      type="checkbox"
                      :id="`feature-value-${featureValue.id}`"
                      :value="featureValue.id"
                      @change="handleFeatureValueSelect"
                  /></label>
                </td>
                <td>{{ featureValue.id }}</td>
                <td>{{ featureValue.name }}</td>
                <td>{{ featureValue.custom }}</td>
                <td>
                  <button
                    type="button"
                    class="btn btn-danger"
                    @click="handleFeatureValueDelete"
                    :data-feature-value-id="featureValue.id"
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
        <pre>
          <samp>
            {{ features }}
          </samp>
        </pre>
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
