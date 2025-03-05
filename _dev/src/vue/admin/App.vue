<script setup>
import { computed, ref } from 'vue'

const { drsoftfrfeaturemanager } = window
const features = ref({})
const selectedFeatureId = ref(0)
const feature = computed(() => {
  if (!Array.isArray(features.value)) {
    return {}
  }

  return (
    features.value.find((feature) => feature.id === selectedFeatureId.value) ||
    {}
  )
})
const handleFeatureSelect = (event) =>
  (selectedFeatureId.value = parseInt(event.target.value))

fetch(drsoftfrfeaturemanager)
  .then((res) => res.json())
  .then((data) => {
    features.value = data
    selectedFeatureId.value = data[0].id
  })
</script>

<template>
  <main class="card">
    <div class="card-body">
      <p>Hello world ! #{{ selectedFeatureId }}</p>
      <div class="mt-3">
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
      <Transition name="fade" mode="out-in" appear>
        <div v-if="feature.values" class="mt-3 relative">
          <TransitionGroup class="table table-striped" name="table" tag="table">
            <tr v-for="featureValue in feature.values" :key="featureValue.id">
              <td>{{ featureValue.id }}</td>
              <td>{{ featureValue.name }}</td>
            </tr>
          </TransitionGroup>
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

.table-enter-active,
.table-leave-active {
  transition: all 0.5s ease;
}

.table-leave-active {
  position: absolute;
  width: 100%;
}

.table-enter-from,
.table-leave-to {
  opacity: 0;
  transform: translateY(-30%);
}
</style>
