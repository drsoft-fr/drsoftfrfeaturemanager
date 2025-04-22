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

const orphanFeatureValues = ref([])
const selectedFeatureValues = ref([])
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature_value: { value: null, matchMode: FilterMatchMode.EQUALS },
  value: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  id_feature: { value: null, matchMode: FilterMatchMode.EQUALS },
})
const loading = ref(false)

const confirm = useConfirm()
const toast = useToast()

/**
 * Asynchronously fetches orphan feature values from the server.
 *
 * This function sends a GET request to the specified route to retrieve
 * all orphan feature values. If the request is successful, it returns
 * the JSON-parsed response. In case of an HTTP error or network failure,
 * it returns an error object containing a success flag set to false and
 * an error message.
 *
 * @returns {Promise<Object>} A promise that resolves to the response data or an error object.
 */
const fetchOrphanFeatureValues = async () => {
  try {
    const response = await fetch(routes.orphanFeatureValueGetAll)

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    return response.json()
  } catch (e) {
    return {
      success: false,
      message: 'Error fetching orphan feature values:' + e.message,
      orphan_feature_values: [],
    }
  }
}
/**
 * Asynchronous function to fetch and handle all orphan feature values.
 * Updates the loading state, fetches the orphan feature values, and handles the result.
 * Displays a toast notification based on the success or failure of the operation.
 *
 * Effects:
 * - Sets the `loading` state to true while processing.
 * - Updates `orphanFeatureValues` with the fetched values on success.
 * - Displays a toast notification to indicate success or error with a summary and detailed message.
 * - Resets the `loading` state to false after processing.
 *
 * Dependencies:
 * - `fetchOrphanFeatureValues`: A function to fetch orphan feature values from an external source.
 * - `loading`, `orphanFeatureValues`, and `toast` are external reactive or state management variables or objects.
 */
const handleOrphanFeatureValueGetAll = async () => {
  loading.value = true
  const res = await fetchOrphanFeatureValues()
  orphanFeatureValues.value = res.orphan_feature_values
  loading.value = false
  toast.add({
    severity: res.success ? 'success' : 'error',
    summary: res.success ? 'Confirmed' : 'Error',
    detail: res.message,
    life: lifetime.value,
  })
}

/**
 * Deletes multiple orphan feature values given their IDs.
 *
 * This function sends a POST request to delete the specified feature values
 * by their IDs. The IDs are sent as a comma-separated list in a FormData object.
 * If the operation is successful, it returns the parsed JSON response.
 * If an error occurs during the fetch or the server responds with a non-OK status,
 * it returns an error object with details about the failure.
 *
 * @param {Array<number|string>} ids - An array of IDs representing the feature values to delete.
 *
 * @returns {Promise<Object>} Returns a promise resolving to the server's response as JSON if successful,
 * or an error object with `success: false` and an error message.
 */
const fetchDeleteFeatureValues = async (ids) => {
  try {
    const form = new FormData()

    form.append('feature_value_ids', ids.join(','))

    const response = await fetch(routes.orphanFeatureValueBulkDelete, {
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
      message: 'Error deleting orphan feature values:' + error.message,
    }
  }
}

/**
 * Deletes the selected feature values after user confirmation.
 *
 * This function performs the following actions:
 * - Checks if there are any selected feature values. If none, displays an informational toast message.
 * - Prompts the user for confirmation before proceeding with the deletion.
 * - If confirmed, marks appropriate loading states as true, resets certain states, fetches and deletes
 *   the selected feature values by their IDs, and retrieves updated feature values data.
 * - Displays corresponding toast messages based on the success or failure of the operations.
 * - Resets the loading states and selected feature value states after completion.
 * - If the user rejects the confirmation dialog, displays a warning toast message.
 *
 * Variables used:
 * - `selectedFeatureValues`: Holds the list of currently selected feature values.
 * - `toast`: Utility to display toast notifications.
 * - `lifetime`: Specifies the display duration for a toast message.
 * - `loading`: Indicates the loading state for the entire deletion operation.
 * - `leftFeatureValueTableLoading`: Indicates the loading state for the left feature values table.
 * - `rightFeatureValueTableLoading`: Indicates the loading state for the right feature values table.
 * - `leftSelectedFeature`: Represents the currently selected feature on the left.
 * - `rightSelectedFeature`: Represents the currently selected feature on the right.
 * - `fetchDeleteFeatureValues`: Function to perform the API call for deleting feature values.
 * - `getAll`: Function to fetch all feature values.
 * - `handleOrphanFeatureValueGetAll`: Function to handle fetching orphan feature values.
 *
 * Notes:
 * - This function operates asynchronously and involves multiple asynchronous operations.
 * - User rejection, success, or failure is conveyed using toast notifications.
 */
const deleteSelectedFeatureValues = async () => {
  if (
    !selectedFeatureValues.value ||
    selectedFeatureValues.value.length === 0
  ) {
    toast.add({
      severity: 'info',
      summary: 'Info',
      detail: 'No feature values selected',
      life: lifetime.value,
    })

    return
  }

  confirm.require({
    message: 'Do you want to delete these orphan feature values?',
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

      const featureValueIds = selectedFeatureValues.value.map(
        (featureValue) => featureValue.id_feature_value,
      )
      const res = await fetchDeleteFeatureValues(featureValueIds)
      const [res2] = await Promise.all([
        getAll(),
        handleOrphanFeatureValueGetAll(),
      ])

      selectedFeatureValues.value = []
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
      <h3>Orphan feature values</h3>
      <p>These characteristic values are not associated with any product.</p>

      <DataTable
        v-model:selection="selectedFeatureValues"
        :value="orphanFeatureValues"
        dataKey="id_feature_value"
        removableSort
        paginator
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        v-model:filters="filters"
        :globalFilterFields="['id_feature_value', 'value', 'id_feature']"
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
                :disabled="!selectedFeatureValues.length"
                @click="deleteSelectedFeatureValues"
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
                @click="handleOrphanFeatureValueGetAll"
              />
            </template>
          </Toolbar>
        </template>
        <template #empty>No orphan characteristic values found.</template>
        <template #loading>Loading data. Please wait.</template>
        <Column selectionMode="multiple" header="Selection"></Column>
        <Column field="id_feature_value" header="ID" sortable></Column>
        <Column field="value" header="Valeur" sortable></Column>
        <Column field="id_feature" header="ID Feature" sortable></Column>
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
