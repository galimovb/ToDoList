<template>
  <AppLayout>
    <div class="planned-tasks-page">
      <h1>Сегодня</h1>
      <TasksCountInfo
          :count="tasks.length"
      />
      <Tasks
          v-for="task in sortedTasks"
          :key="task.id"
          :task="task"
          @task-deleted="tasksArrayUpdate"
          @task-updated="tasksArrayUpdate"
      />
      <AddNewTaskButton
          @add-new-task="addNewTask"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "../../components/layout/AppLayout.vue";
import TasksCountInfo from "../../components/item/task/components/TasksCountInfo.vue";
import Tasks from "../../components/item/task/Task.vue";
import AddNewTaskButton from "../../components/button/AddNewTaskButton.vue";
import { onMounted, ref, computed } from "vue";
import { sendRequest } from "../../api/baseApi";
import { ElMessage } from "element-plus";

const tasks = ref([]);

onMounted(async () => {
  try {
    const response = await sendRequest('GET', '/tasks/today');
    const data = await response.json();
    tasks.value = data;
  } catch (error) {
    ElMessage.error('Произошла ошибка при запросе');
  }
});

const sortedTasks = computed(() => {
  return [...tasks.value].sort((a, b) => {
    if (a.isFavorite && !b.isFavorite) return -1;
    if (!a.isFavorite && b.isFavorite) return 1;
    return new Date(a.dueDate) - new Date(b.dueDate);
  });
});

const tasksArrayUpdate = (id) => {
  tasks.value = tasks.value.filter(task => task.id !== id);
};

const addNewTask = (newTask) => {
  const now = new Date();
  const today = new Date(now.getTime() + 3 * 60 * 60 * 1000);
  today.setUTCHours(0, 0, 0, 0);

  const taskDueDate = new Date(newTask.dueDate + "Z");
  taskDueDate.setUTCHours(0, 0, 0, 0);

  if (taskDueDate.toISOString().split("T")[0] === today.toISOString().split("T")[0]) {
    tasks.value.push(newTask);
  } else {
    ElMessage.warning('Задача задана на другой день, здесь ее не будет');
  }
};
</script>

<style scoped>
.planned-tasks-page {
  padding: 80px 52px;
}
</style>
