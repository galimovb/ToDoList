<template>
  <el-button
      type="danger"
      link
      @click="showedTaskCreateModal"
  >
    <el-icon :size="18">
      <CirclePlus />
    </el-icon>
    <span>
       Добавить задачу
    </span>
  </el-button>

  <CreateTaskModal
      v-if="showTaskCreateModal"
      @close="showedTaskCreateModal"
      @add-new-task="(task) => emit('addNewTask', task)"
  />

</template>

<script setup>
import { CirclePlus } from "@element-plus/icons-vue";
import {ref} from "vue";
import CreateTaskModal from "../modal/CreateTaskModal.vue";

const emit = defineEmits(['addNewTask'])

const showTaskCreateModal = ref(false);

const showedTaskCreateModal = () => {
  showTaskCreateModal.value = !showTaskCreateModal.value;
};
</script>

<style scoped lang="scss">
.el-button {
  justify-content: left;
  padding: 0 !important;
  font-size: 15px !important;
  color: red !important;
}
</style>
