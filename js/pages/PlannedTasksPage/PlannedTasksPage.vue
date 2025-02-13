<template>
  <AppLayout>
    <div class="planned-tasks-page">
      <div
          v-for="(taskGroup, index) in tasks"
          :key="index"
          class="planned-tasks-page__item"
      >
        <h2 class="item__title">
          {{ formatDate(taskGroup.date) }}
        </h2>
        <TasksCountInfo
            v-if="taskGroup.tasks.length > 0"
            :count="taskGroup.tasks.length"
        />
        <div v-for="task in taskGroup.tasks" :key="task.id">
          <Tasks
              :task="task"
              @task-updated="handleTaskUpdated"
              @task-deleted="handleTaskUpdated"
          />
        </div>
        <AddNewTaskButton
            @add-new-task="addNewTask"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "../../components/layout/AppLayout.vue";
import TasksCountInfo from "../../components/item/task/components/TasksCountInfo.vue";
import Tasks from "../../components/item/task/Task.vue";
import AddNewTaskButton from "../../components/button/AddNewTaskButton.vue";
import { onMounted, ref } from "vue";
import { sendRequest } from "../../api/baseApi";
import { ElMessage } from "element-plus";

const tasks = ref([]);

const formatDate = (dateString) => {
  const options = { day: 'numeric', month: 'long' };
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', options);
};

const fetchTasks = async () => {
  try {
    const response = await sendRequest("GET", "/tasks/planned");
    tasks.value = await response.json();
  } catch (error) {
    ElMessage.error("Произошла ошибка при запросе задач");
  }
};

const handleTaskUpdated = (taskId) => {
  tasks.value.forEach((group) => {
    group.tasks = group.tasks.filter((task) => task.id !== taskId);
  });
};

const addNewTask = (newTask) => {
  const taskDate = newTask.dueDate.split(" ")[0];

  const taskGroup = tasks.value.find(group => group.date === taskDate);

  if (taskGroup) {
    taskGroup.tasks.push(newTask);
    tasks.value = [...tasks.value];
  } else {
    //tasks.value = [...tasks.value, { date: taskDate, tasks: [newTask] }];
    ElMessage.warning('Задача создана, но здесь ее не будет ))')
  }
};

onMounted(fetchTasks);

</script>

<style scoped>
.planned-tasks-page {
  padding: 40px 52px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.item__title {
  padding-bottom: 8px;
  border-bottom: 1px solid lightgray;
}

.item__tasks {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
</style>
