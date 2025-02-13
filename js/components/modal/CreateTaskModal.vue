<template>
  <div
      class="modal-overlay"
      @click="handleClose"
  >
    <div class="modal-content">
      <el-form
          :model="editableTask"
          :rules="rules"
          ref="taskForm"
          @submit.prevent="handleSubmit"
      >
        <el-form-item prop="title" @click.stop>
          <el-input
              v-model="editableTask.title"
              placeholder="Введите название задачи"
          />
        </el-form-item>

        <el-form-item prop="description" @click.stop>
          <el-input
              v-model="editableTask.description"
              type="textarea"
              placeholder="Введите описание задачи"
          />
        </el-form-item>

        <el-form-item prop="dueDate" @click.stop>
          <el-date-picker
              v-model="editableTask.dueDate"
              type="datetime"
              placeholder="Выберите дату выполнения"
          />
        </el-form-item>

        <el-form-item prop="priority" @click.stop>
          <el-select v-model="editableTask.priority" placeholder="Выберите приоритет">
            <el-option label="Высокий" value="3" />
            <el-option label="Средний" value="2" />
            <el-option label="Низкий" value="1" />
          </el-select>
        </el-form-item>

        <el-form-item @click.stop v-if="isEditMode">
          <el-switch v-model="editableTask.isCompleted" active-text="Завершено" />
        </el-form-item>

        <div class="modal-footer" @click.stop>
          <button type="button" @click="handleClose">Закрыть</button>
          <button type="submit" :disabled="!isSubmitEnabled">
            {{ isEditMode ? "Сохранить изменения" : "Создать задачу" }}
          </button>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElDatePicker, ElSwitch, ElMessage } from 'element-plus';
import { sendRequest } from "../../api/baseApi";

const props = defineProps({
  task: {
    type: Object,
    default: null,
  }
});

const emit = defineEmits(['close', 'updateTask', 'addNewTask']);

const isEditMode = computed(() => !!props.task);

const originalTask = ref(null);
const editableTask = ref({
  title: '',
  description: '',
  dueDate: '',
  priority: '',
  isCompleted: false,
});

const rules = ref({
  title: [{ required: true, message: 'Название задачи обязательно', trigger: 'blur' }],
  dueDate: [{ required: true, message: 'Дата выполнения обязательна', trigger: 'change' }],
});

onMounted(() => {
  if (props.task) {
    originalTask.value = { ...props.task };
    editableTask.value = { ...props.task };
  }
});

const hasChanges = computed(() => {
  if (!isEditMode.value) return false;
  return JSON.stringify(originalTask.value) !== JSON.stringify(editableTask.value);
});

const isSubmitEnabled = computed(() => {
  if (isEditMode.value) {
    return hasChanges.value;
  }
  return editableTask.value.title.trim() !== '' && editableTask.value.dueDate;
});

const handleClose = () => {
  emit('close');
};

const taskForm = ref(null);

const showMessage = (message, type) => {
  ElMessage({ message, type });
};

const handleSubmit = async () => {
  try {
    await taskForm.value.validate();

    if (isEditMode.value) {
      const response = await sendRequest('PUT', `/tasks/edit/${editableTask.value.id}`, editableTask.value);

      if (!response.ok) {
        throw new Error('Ошибка при обновлении задачи');
      }

      const updatedTask = await response.json();
      emit('updateTask', updatedTask);
      window.location.reload();

      setTimeout(() => {
        showMessage('Задача успешно обновлена', 'success');
      }, 1500);

    } else {
      const response = await sendRequest('POST', '/tasks/new', editableTask.value);

      if (!response.ok) {
        throw new Error('Ошибка при создании задачи');
      }

      const newTask = await response.json();
      emit('addNewTask', newTask);
      showMessage('Задача успешно создана', 'success');
    }

    handleClose();
  } catch (error) {
    showMessage(error.message, 'error');
  }
};

watch(editableTask, () => {
  isSubmitEnabled.value;
}, {deep: true});

</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 450px;
  max-width: 90%;
  position: relative;
}

h3 {
  font-size: 1.5rem;
  color: #e74c3c;
  margin-bottom: 20px;
  text-align: center;
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

button {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button[type="button"] {
  background-color: #ccc;
}

button[type="submit"] {
  background-color: #e74c3c;
  color: white;
}

button[type="submit"]:hover {
  background-color: #c0392b;
}

button:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}
</style>
