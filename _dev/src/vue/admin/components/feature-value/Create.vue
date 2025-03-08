<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'

const { feature } = inject('feature')
const { create, get } = inject('featureValue')
const loading = ref(false)
const handleFeatureValueCreate = async (event) => {
  loading.value = true
  const featureId = parseInt(event.currentTarget.dataset.featureId || '')
  await create(event.currentTarget)
  await get(featureId)
  loading.value = false
}
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <form
      v-show="feature.id_feature"
      @submit.prevent="handleFeatureValueCreate"
      :data-feature-id="feature.id_feature"
    >
      <input type="hidden" name="id_feature" :value="feature.id_feature" />
      <div class="flex flex-col gap-2">
        <label for="create-feature-value">
          Create value for {{ feature.name }} feature
        </label>
        <InputText
          placeholder="Feature value name"
          type="text"
          id="create-feature-value"
          name="value"
        />
        <Message size="small" severity="secondary" variant="simple"
          >Would you like to create feature value?
        </Message>
        <Button
          label="Create"
          type="submit"
          :loading="loading"
          severity="info"
        />
      </div>
    </form>
  </Transition>
</template>

<style scoped lang="scss"></style>
