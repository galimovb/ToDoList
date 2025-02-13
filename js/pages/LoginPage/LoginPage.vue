<template>
  <div class="login-page">
    <div class="login-page__form-container">

      <div class="login-page__login-section">
        <div class="login-page__header">
          <h2>Вход</h2>
        </div>

        <div class="login-page__form-wrapper">
          <el-card class="login-page__form-card">
            <el-form :model="form">
              <el-form-item
                  prop="email"
                  :rules="[ { required: true, message: 'Пожалуйста, введите ваш login!' } ]"
              >
                <el-input
                    v-model="form.login"
                    placeholder="Логин"
                    :prefix-icon="UserIcon"
                    size="large"
                />
              </el-form-item>

              <el-form-item
                  prop="password"
                  :rules="[ { required: true, message: 'Пожалуйста, введите пароль!' }, { min: 6, message: 'Пароль должен быть не короче 6 символов!' } ]"
              >
                <el-input
                    v-model="form.password"
                    :type="passwordFieldType"
                    placeholder="Введите пароль"
                    :prefix-icon="LockIcon"
                    size="large"
                >
                  <template #append>
                    <el-button
                        :icon="View"
                        @click="togglePasswordVisibility"
                        type="text"
                    ></el-button>
                  </template>
                </el-input>
              </el-form-item>

              <el-form-item>
                <el-button
                    type="primary"
                    html-type="submit"
                    class="login-page__submit-btn"
                    :loading="isLoading"
                    @click.prevent="loginFormSubmit"
                >
                  {{ isLoading ? 'Подождите..' : 'Войти' }}
                </el-button>
              </el-form-item>
            </el-form>
          </el-card>

          <div class="login-page__signup-link-wrapper">
            <div class="login-page__text-gray">У вас еще нет аккаунта?</div>
            <el-button type="text">
              <a href="/registr" class="login-page__signup-link">Зарегистрируйтесь в системе</a>
            </el-button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import {ElForm, ElFormItem, ElInput, ElButton, ElCard, ElMessage} from 'element-plus';
import {User as UserIcon, Lock as LockIcon, View} from '@element-plus/icons-vue';

const form = ref({
  login: '',
  password: ''
});

const isLoading = ref(false);
const passwordFieldType = ref('password');
const user = ref({});


const loginFormSubmit = async () => {
  isLoading.value = true;

  try {
    const response = await fetch('/login/check', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        login: form.value.login,
        password: form.value.password,
      }),
    });

    if (response.ok) {
      const data = await response.json();
      user.value = data.user;

      if (data.user.roles.length === 1 && data.user.roles.includes('ROLE_ADMIN')) {
        window.location.href = '/admin';
      } else {
        window.location.href = '/';
      }
    } else {
      ElMessage.error('Проверьте логин или пароль');
    }
  } catch (error) {
    ElMessage.error('Что-то пошло не так. Попробуйте снова.');
  } finally {
    isLoading.value = false;
  }
};


const togglePasswordVisibility = () => {
  passwordFieldType.value = passwordFieldType.value === 'password' ? 'text' : 'password';
};
</script>

<style scoped>
.login-page {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 24px;
}

.login-page__form-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 450px;
}

.login-page__login-section .login-page__header {
  display: flex;
  justify-content: space-between;
  padding: 0 12px;
  margin-bottom: 12px;
}

.login-page__form-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 350px;
}

.login-page__form-card {
  width: 100%;
  background-color: #f9f9f9;
}

.login-page__submit-btn {
  height: 40px;
  width: 100%;
}

.login-page__signup-link-wrapper {
  text-align: center;
}

.login-page__signup-link {
  display: block;
  color: #1e88e5;
}

.login-page__text-gray {
  color: #9e9e9e;
}
</style>
