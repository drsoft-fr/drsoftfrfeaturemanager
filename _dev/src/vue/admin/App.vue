<script setup>
const handleClick = () => {
  console.log('Hello Vue')
}
</script>

<template>
  <main>
    <p>Hello world !</p>
    <button class="btn btn-accent" @click="handleClick">Click me</button>
  </main>
</template>

<style scoped>
main p {
  color: #4c1818;
  font-weight: bold;
}
</style>
