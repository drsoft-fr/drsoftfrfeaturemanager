<script setup>
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import OrphanFeatures from './OrphanFeatures.vue'
import OrphanFeatureValues from './OrphanFeatureValues.vue'
</script>

<template>
  <div
    class="mt-10 sm:mt-16 py-8 px-4 lg:py-16 lg:px-6 mx-auto lg:mx-0 bg-white dark:bg-gray-900 rounded-md"
  >
    <h2 class="mb-5 sm:mb-8">Orphan element manager</h2>
    <Tabs value="0">
      <TabList>
        <Tab value="0">Orphan features</Tab>
        <Tab value="1">Orphan feature values</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <OrphanFeatures />
        </TabPanel>
        <TabPanel value="1">
          <OrphanFeatureValues />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>
