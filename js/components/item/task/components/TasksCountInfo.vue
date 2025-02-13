<template>
  <div class="tasks-count-info">
    <el-icon>
      <SuccessFilled/>
    </el-icon>
    <span>
          {{ count }} {{getTaskWord(count)}}
    </span>
  </div>
</template>

<script setup>

import {SuccessFilled} from "@element-plus/icons-vue";

const props = defineProps({
  count: {
    type: Number,
    required: true,
    default: 0
  }
});

const getTaskWord = (count) => {
  if (count === 1) {
    return 'задача';
  } else if (count > 1 && count < 5) {
    return 'задачи';
  } else {
    return 'задач';
  }
};
</script>

<style scoped lang="scss">
.tasks-count-info {
  display: flex;
  gap: 4px;
  align-items: center;
  color: gray;
  margin-bottom: 16px;
}
</style>