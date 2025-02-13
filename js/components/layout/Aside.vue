<template>
  <el-aside class="aside">
    <div class="aside-header">
      <el-avatar :size="24" class="aside__header-avatar">
        {{ userName[0] || "?" }}
      </el-avatar>
      <span>{{ userName }}</span>
    </div>

    <el-menu
        :default-active="activeMenuItem"
        class="aside-menu"
    >
      <el-menu-item
          v-for="(item, index) in menuItems"
          :key="index"
          :index="String(index + 1)"
      >
        <a :href="item.link">
          <el-icon>
            <component :is="item.icon"/>
          </el-icon>
          {{ item.name }}
        </a>
      </el-menu-item>
      <el-menu-item
          v-if="hasAdminRoles"
      >
        <a href="/admin">
          <el-icon>
            <User/>
          </el-icon>
           Админка
        </a>
      </el-menu-item>
    </el-menu>

    <el-button
        type="info"
        text class="exit-btn"
    >
      <a href="/logout"
         class="exit-btn-link"
      >
        Выйти
      </a>

      <el-icon
          class="el-icon--right"
      >
        <ArrowRight/>
      </el-icon>
    </el-button>
  </el-aside>
</template>

<script setup>
import {computed, ref, onMounted} from 'vue';
import {ElMessage} from 'element-plus';
import {ArrowRight, Message, User, Warning} from '@element-plus/icons-vue';

const menuItems = ref([
  {name: 'Сегодня', link: '/', icon: Message},
  {name: 'Запланированные', link: '/planned', icon: Warning},
]);

const userName = ref("Загрузка...");

const hasAdminRoles = ref(false);

const activeMenuItem = computed(() => {
  const currentPath = window.location.pathname;
  const activeItem = menuItems.value.findIndex((item) => item.link === currentPath);
  return String(activeItem + 1);
});

onMounted(async () => {
  try {
    const response = await fetch('/user');
    if (response.ok) {
      const userData = await response.json();
      userName.value = userData.login || "Неизвестный";
      hasAdminRoles.value = userData.roles.includes("ROLE_ADMIN")
    } else {
      ElMessage.error("Ошибка загрузки данных пользователя");
    }
  } catch (error) {
    ElMessage.error("Ошибка сети: " + error.message);
  }
});
</script>

<style scoped>
.aside {
  width: 280px;
  height: 100vh;
  max-height: 100vh;
  background-color: #fcfaf8 !important;
  font-size: 16px;
  padding-bottom: 12px;
  display: flex;
  flex-direction: column;
}

.aside-menu {
  background-color: #fafafa;
  border-right: none;
  flex-grow: 1;
  margin-top: 12px;
}

.aside-header {
  padding: 12px;
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.el-menu-item {
  height: 48px;
  font-size: 16px !important;
  transition: background-color 0.3s, color 0.3s;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  padding: 0 16px;
}

.el-menu-item a {
  text-decoration: none;
  color: inherit;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
}

.el-menu-item.is-active {
  background-color: #ffefe5;
  color: red;
  border-radius: 12px;
}

.el-menu-item:hover {
  background-color: #ffefe5;
  color: red;
  border-radius: 12px;
}

.exit-btn {
  margin-top: auto;
}

.exit-btn-link {
  text-decoration: none;
  color: gray;
}

.aside__header-avatar {
  background-color: orange;
}
</style>
