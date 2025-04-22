<script setup>
import { ref, inject } from 'vue'
import DataTable from 'primevue/datatable'
import Toolbar from 'primevue/toolbar'
import Column from 'primevue/column'
import { FilterMatchMode } from '@primevue/core/api'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const routes = inject('routes')
const { lifetime } = inject('toast')
const { getAll } = inject('feature')
const { leftFeatureValueTableLoading, rightFeatureValueTableLoading } =
  inject('featureValue')
const { reset } = inject('core')

const orphanFeatures = ref([])
const selectedFeatures = ref([])
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature: { value: null, matchMode: FilterMatchMode.EQUALS },
  name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
})
const loading = ref(false)

const confirm = useConfirm()
const toast = useToast()

/**
 * An asynchronous function to fetch orphan features from the server.
 *
 * This function makes a GET request to the endpoint defined by `routes.orphanFeatureGetAll`
 * and retrieves the list of orphan features. If the request is successful and returns
 * a valid response, the response is parsed into a JSON object and returned.
 *
 * In the case of an unsuccessful HTTP response or any other error during the request,
 * the function catches the error and returns an object indicating failure with an
 * error message.
 *
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response containing fetched orphan features
 * when successful, or an error object with `success` set to `false` and an error message otherwise.
 */
const fetchOrphanFeatures = async () => {
  try {
    const response = await fetch(routes.orphanFeatureGetAll)

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    return response.json()
  } catch (e) {
    return {
      success: false,
      message: 'Error fetching orphan features:' + e.message,
      orphan_features: [],
    }
  }
}

/**
 * An asynchronous method to handle fetching all orphan features.
 * This function sets the loading state to true while fetching the data,
 * updates the `orphanFeatures` variable with the fetched response, and
 * then sets the loading state back to false. It also displays a toast message
 * based on the success or failure of the operation using the response data.
 */
const handleOrphanFeatureGetAll = async () => {
  loading.value = true
  const res = await fetchOrphanFeatures()
  orphanFeatures.value = res.orphan_features
  loading.value = false
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: lifetime.value,
  })
}

/**
 * Sends a request to delete multiple orphan features based on their IDs.
 *
 * @param {Array<string>} ids - An array of feature IDs to be deleted.
 *
 * @returns {Promise<Object>} - Resolves with a response object containing the operation status and any relevant data.
 * If the deletion is successful, it returns the parsed JSON response from the server.
 * In case of an error, it returns an object with success set to false and an error message.
 */
const fetchDeleteFeatures = async (ids) => {
  try {
    const form = new FormData()

    form.append('feature_ids', ids.join(','))

    const response = await fetch(routes.orphanFeatureBulkDelete, {
      method: 'POST',
      body: form,
    })

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    return response.json()
  } catch (error) {
    return {
      success: false,
      message: 'Error deleting orphan features:' + error.message,
    }
  }
}

/**
 * Deletes the selected orphan features and updates the related state.
 *
 * This function performs the following operations:
 * - Validates whether any features are selected. If none are selected, an informational toast message is displayed.
 * - Prompts the user with a confirmation dialog to delete the selected features.
 * - If accepted, it performs asynchronous operations:
 *   - Marks the relevant loading states as active.
 *   - Resets the currently selected features.
 *   - Sends a request to delete the selected features using their IDs.
 *   - Refreshes the features data by fetching all features and handling orphan feature updates.
 *   - Clears the selected features and resets loading states.
 *   - Displays a success or error toast based on the response from the delete operation.
 *   - Handles additional messages if the orphan feature retrieval task fails.
 * - If rejected, a warning toast message is displayed indicating the rejection.
 *
 * Variables/Dependencies:
 * - `selectedFeatures`: Contains the list of selected features to be deleted.
 * - `toast`: Used to display status messages to the user.
 * - `lifetime`: Defines the display duration for toast messages.
 * - `loading`, `leftFeatureValueTableLoading`, `rightFeatureValueTableLoading`: Reactive flags for UI states.
 * - `leftSelectedFeature`, `rightSelectedFeature`: References to currently selected features for the UI.
 * - `fetchDeleteFeatures`: Function to delete features by their IDs.
 * - `getAll`: Function to fetch all updated features.
 * - `handleOrphanFeatureGetAll`: Function to handle retrieval of orphan features.
 * - `confirm`: Used to display a confirmation dialog to the user.
 */
const deleteSelectedFeatures = async () => {
  if (!selectedFeatures.value || selectedFeatures.value.length === 0) {
    toast.add({
      severity: 'info',
      summary: 'Info',
      detail: 'No features selected',
      life: lifetime.value,
    })

    return
  }

  confirm.require({
    message: 'Do you want to delete these orphan features?',
    header: 'Danger Zone',
    icon: 'pi pi-info-circle',
    rejectLabel: 'Cancel',
    rejectProps: {
      label: 'Cancel',
      severity: 'secondary',
      outlined: true,
    },
    acceptProps: {
      label: 'Delete',
      severity: 'danger',
    },
    accept: async () => {
      loading.value = true
      leftFeatureValueTableLoading.value = true
      rightFeatureValueTableLoading.value = true

      await reset()

      const featureIds = selectedFeatures.value.map(
        (feature) => feature.id_feature,
      )
      const res = await fetchDeleteFeatures(featureIds)
      const [res2] = await Promise.all([getAll(), handleOrphanFeatureGetAll()])

      selectedFeatures.value = []
      loading.value = false
      leftFeatureValueTableLoading.value = false
      rightFeatureValueTableLoading.value = false
      toast.add({
        severity: res.success ? 'success' : 'error',
        summary: res.success ? 'Confirmed' : 'Error',
        detail: res.message,
        life: lifetime.value,
      })

      if (false === res2.success) {
        toast.add({
          severity: res2.success ? 'success' : 'error',
          summary: res2.success ? 'Confirmed' : 'Error',
          detail: res2.message,
          life: lifetime.value,
        })
      }
    },
    reject: () => {
      toast.add({
        severity: 'warn',
        summary: 'Rejected',
        detail: 'You have rejected',
        life: lifetime.value,
      })
    },
  })
}
</script>

<template>
  <Transition name="fade" mode="out-in" appear>
    <div>
      <h3>Orphan features</h3>
      <p>These features are not associated with any product.</p>

      <DataTable
        v-model:selection="selectedFeatures"
        :value="orphanFeatures"
        dataKey="id_feature"
        removableSort
        paginator
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        v-model:filters="filters"
        :globalFilterFields="['id_feature', 'name']"
        :loading="loading"
        class="border-t border-gray-200"
        stripedRows
      >
        <template #header>
          <Toolbar>
            <template #start>
              <Button
                label="Delete"
                severity="danger"
                icon="pi pi-trash"
                :disabled="!selectedFeatures.length"
                @click="deleteSelectedFeatures"
              />
            </template>
            <template #center>
              <InputText
                v-model="filters['global'].value"
                placeholder="Search by keyword"
              />
            </template>
            <template #end>
              <Button
                label="Refresh"
                icon="pi pi-refresh"
                @click="handleOrphanFeatureGetAll"
              />
            </template>
          </Toolbar>
        </template>
        <template #empty>No orphan features found.</template>
        <template #loading>Loading data. Please wait.</template>
        <Column selectionMode="multiple" header="Selection"></Column>
        <Column field="id_feature" header="ID" sortable></Column>
        <Column field="name" header="Nom" sortable></Column>
      </DataTable>
    </div>
  </Transition>
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
</style>
