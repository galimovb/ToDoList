<template>
  <div class="task">
    <el-checkbox v-model="isDone" @change="taskComplete" />
    <div class="task__title">
      {{ task.title }}
    </div>

    <div class="task__buttons">
      <el-button link @click="showEditTaskModal = !showEditTaskModal">
        <el-icon :size="18">
          <Edit />
          <CreateTaskModal
              v-if="showEditTaskModal"
              :task="task"
              @close="showEditTaskModal = !showEditTaskModal"
          />
        </el-icon>
      </el-button>
      <el-button link @click="deleteTask">
        <el-icon :size="18">
          <Delete />
        </el-icon>
      </el-button>
      <el-button link @click="toggleFavorite"  v-if="isAddStar">
        <el-icon :size="18" :class="{ 'favorite': isFavorite }">
          <Star />
        </el-icon>
      </el-button>
    </div>
  </div>
</template>

<script setup>
import {ref, defineProps, defineEmits, computed} from "vue";
import {Delete, Edit, Star} from "@element-plus/icons-vue";
import {sendRequest} from "../../../api/baseApi";
import {ElMessage} from "element-plus";
import CreateTaskModal from "../../modal/CreateTaskModal.vue";

const props = defineProps({
  task: {
    type: Object,
    required: true,
    default: () => ({
      title: "Задача 1",
      isFavorite: false,
    }),
  },
});
const emit = defineEmits(["taskUpdated", "taskDeleted"]);

const isDone = ref(props.task.isCompleted);
const isFavorite = ref(props.task.isFavorite);
const showEditTaskModal = ref(false);

const taskComplete = async () => {
  const updatedTask = {...props.task, isCompleted: isDone.value};
  try {
    const response = await sendRequest("PATCH", `/tasks/complete/${props.task.id}`, updatedTask);
    if (response.ok) {
      emit("taskUpdated", props.task.id);
      showMessage("Вы завершили задачу", "success");
    }
  } catch (error) {
    console.error("Ошибка при изменении статуса задачи:", error);
    showMessage("Ошибка при изменении статуса", "warning");
  }
};

const deleteTask = async () => {
  try {
    const response = await sendRequest("DELETE", `/tasks/delete/${props.task.id}`);
    if (response.ok) {
      emit("taskDeleted", props.task.id);
      showMessage("Задача удалена", "success");
    }
  } catch (error) {
    showMessage("Ошибка при удалении задачи", "warning");
  }
};

const toggleFavorite = async () => {
  try {
    const response = await sendRequest("POST", `/tasks/favorite/${props.task.id}`);
    if (response.ok) {
      isFavorite.value = !isFavorite.value;
      showMessage(isFavorite.value ? "Добавлено в избранное" : "Удалено из избранного", "success");
    }
  } catch (error) {
    showMessage("Ошибка при изменении избранного", "warning");
  }
};

const isAddStar = computed(() => {
  return  window.location.pathname === '/';
});

const showMessage = (message, type) => {
  ElMessage({
    message: message,
    type: type,
  });
};
</script>

<style scoped lang="scss">
.task {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  height: 40px;
  border-bottom: 1px solid lightgray;
  padding-bottom: 8px;
  margin-bottom: 16px;
}

.task__title {
  flex-grow: 1;
}

.task__buttons {
  margin-left: auto;
}

.favorite {
  color: gold;
}
</style>
