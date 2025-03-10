<script setup>
import { inject, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'

const { feature } = inject('feature')
const { create, featureValueTableLoading, get } = inject('featureValue')
const loading = ref(false)
const toast = useToast()
const handleFeatureValueCreate = async (event) => {
  loading.value = true
  featureValueTableLoading.value = true
  const featureId = parseInt(feature.value.id_feature || '')
  await create(event.currentTarget)
  await get(featureId)
  loading.value = false
  featureValueTableLoading.value = false
  toast.add({
    severity: 'success',
    summary: 'Confirmed',
    detail: 'Feature value created',
    life: 3000,
  })
}
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <form
      v-show="feature.id_feature"
      @submit.prevent="handleFeatureValueCreate"
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
