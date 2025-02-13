<template>
  <div class="common-layout">
    <el-container>
      <Aside/>
      <el-main>
        <slot/>
      </el-main>
    </el-container>
  </div>
</template>

<script setup>

import Aside from "./Aside.vue";
</script>

<style scoped lang="scss">
body{
  padding: 0;
  margin: 0;
  min-height: 100%;
}
.el-main{
  padding: 0;
  max-height: 100vh;
}
</style>