<template>
  <div class="admin-page">
    <a href="/logout" class="exit-btn-link">
      <el-icon><ArrowLeft/></el-icon>
    </a>

    <div class="admin-content">
      <h2>Список пользователей</h2>
      <el-row :gutter="20">
        <el-col v-for="user in users" :key="user.id" :span="10">
          <el-card class="user-card" @click="openEditModal(user)">
            <template #header>
              <div class="card-header">
                <el-avatar :style="{ backgroundColor: getRandomColor(user.id) }" class="user-avatar">
                  {{ getInitials(user.firstname, user.lastname) }}
                </el-avatar>
                <div class="user-details">
                  <span class="user-login">{{ user.login }}</span>
                  <el-tag type="success">{{ user.roles.join(", ") }}</el-tag>
                </div>
              </div>
            </template>
            <div class="user-info">
              <p><strong>Имя:</strong> {{ user.firstname || "Не указано" }}</p>
              <p><strong>Фамилия:</strong> {{ user.lastname || "Не указано" }}</p>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Модальное окно редактирования пользователя -->
    <el-dialog v-model="isEditModalOpen" title="Редактирование пользователя" width="500px">
      <el-form :model="editedUser" label-width="100px">
        <el-form-item label="Логин">
          <el-input v-model="editedUser.login" />
        </el-form-item>
        <el-form-item label="Имя">
          <el-input v-model="editedUser.firstname" />
        </el-form-item>
        <el-form-item label="Фамилия">
          <el-input v-model="editedUser.lastname" />
        </el-form-item>
        <el-form-item label="Роли">
          <el-select v-model="editedUser.roles" multiple placeholder="Выберите роли">
            <el-option label="Админ" value="ROLE_ADMIN" />
            <el-option label="Пользователь" value="ROLE_USER" />
          </el-select>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="isEditModalOpen = false">Отмена</el-button>
        <el-button type="primary" @click="saveUser">Сохранить</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { ElMessage } from "element-plus";
import { ArrowLeft } from "@element-plus/icons-vue";

const users = ref([]);
const isEditModalOpen = ref(false);
const editedUser = ref({});

onMounted(async () => {
  try {
    const response = await fetch("/admin/users");
    if (response.ok) {
      users.value = await response.json();
    } else {
      ElMessage.error("Ошибка загрузки пользователей");
    }
  } catch (error) {
    ElMessage.error("Ошибка сети: " + error.message);
  }
});

const openEditModal = (user) => {
  editedUser.value = { ...user };
  isEditModalOpen.value = true;
};

const saveUser = async () => {
  try {
    const response = await fetch(`/admin/users/${editedUser.value.id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(editedUser.value),
    });

    if (response.ok) {
      ElMessage.success("Пользователь обновлен!");
      isEditModalOpen.value = false;

      users.value = users.value.map((user) =>
          user.id === editedUser.value.id ? { ...editedUser.value } : user
      );
    } else {
      ElMessage.error("Ошибка при обновлении пользователя");
    }
  } catch (error) {
    ElMessage.error("Ошибка сети: " + error.message);
  }
};

const getRandomColor = (id) => {
  const colors = ["#f56a00", "#7265e6", "#ffbf00", "#00a2ae", "#673ab7", "#03a9f4"];
  return colors[id % colors.length];
};

const getInitials = (firstname, lastname) => {
  return `${(firstname?.charAt(0) || "").toUpperCase()}${(lastname?.charAt(0) || "").toUpperCase()}`;
};
</script>

<style scoped lang="scss">
.admin-page {
  position: relative;
  margin: 0 auto;
  padding: 20px;
}

.admin-content {
  max-width: 800px;
  margin: 0 auto;
}

h2 {
  text-align: center;
  font-size: 1.8rem;
  margin-bottom: 20px;
}

.user-card {
  border-radius: 10px;
  transition: 0.3s;
  cursor: pointer;
  &:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }
}

.card-header {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-avatar {
  width: 50px;
  height: 50px;
  font-size: 1.2rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-login {
  font-weight: bold;
  font-size: 1rem;
}

.user-info {
  p {
    margin: 5px 0;
    font-size: 1rem;
  }
}

.exit-btn-link {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 8px;
  background-color: lightgray;
  color: gray;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
  text-decoration: none;
}
</style>
