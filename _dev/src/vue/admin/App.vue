<script setup>
import { ref } from "vue";

const { drsoftfrfeaturemanager } = window;

const features = ref({});

const handleClick = async () => {
  const r = await fetch(drsoftfrfeaturemanager);

  features.value = await r.json();
};
</script>

<template>
  <main>
    <p>Hello world !</p>
    <button class="btn btn-primary" @click="handleClick">Click me</button>
    <div class="mt-3">
      <select>
        <TransitionGroup name="fade" mode="out-in" appear>
          <option v-for="(value, key) in features" :key="key" :value="key">{{ value }}</option>
        </TransitionGroup>
      </select>
    </div>
  </main>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-leave-active {
  position: absolute;
  width: 100%;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}
</style>
