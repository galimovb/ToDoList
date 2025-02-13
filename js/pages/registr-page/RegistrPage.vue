<template>
  <div class="registration-page">
    <div class="registration-page__form-container">
      <div class="registration-page__login-section">
        <div class="registration-page__header">
          <h2>Регистрация</h2>
        </div>

        <div class="registration-page__form-wrapper">
          <el-card class="registration-page__form-card">
            <el-form :model="form"  @submit.prevent="registerFormSubmit">
              <el-form-item
                  prop="login"
                  :rules="[ { required: true, message: 'Пожалуйста, введите ваш логин!' }, { min: 3, message: 'Логин должен содержать не менее 3 символов!' } ]"
              >
                <el-input
                    v-model="form.login"
                    placeholder="Введите логин"
                    :prefix-icon="UserIcon"
                    size="large"
                />
              </el-form-item>

              <el-form-item
                  prop="firstname"
                  :rules="[ { required: true, message: 'Пожалуйста, введите ваше имя!' } ]"
              >
                <el-input
                    v-model="form.firstname"
                    placeholder="Введите ваше имя"
                    size="large"
                />
              </el-form-item>

              <el-form-item
                  prop="lastname"
                  :rules="[ { required: true, message: 'Пожалуйста, введите вашу фамилию!' } ]"
              >
                <el-input
                    v-model="form.lastname"
                    placeholder="Введите вашу фамилию"
                    size="large"
                />
              </el-form-item>

              <el-form-item
                  prop="password"
                  :rules="[ { required: true, message: 'Пожалуйста, введите пароль!' }, { min: 6, message: 'Пароль должен быть не короче 5 символов!' } ]"
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
                    class="registration-page__submit-btn"
                    :loading="isLoading"
                    @click="registerFormSubmit"
                >
                  {{ isLoading ? 'Подождите..' : 'Зарегистрироваться' }}
                </el-button>
              </el-form-item>
            </el-form>
          </el-card>

          <div class="registration-page__login-link-wrapper">
            <div class="registration-page__text-gray">Уже есть аккаунт?</div>
            <el-button
                type="text"
            >
              <a href="/login" class="registration-page__login-link">Войдите в систему</a>
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
import { User as UserIcon, Lock as LockIcon, View } from '@element-plus/icons-vue';
import {sendRequest} from "../../api/baseApi";

const form = ref({
  login: '',
  firstname: '',
  lastname: '',
  password: '',
});

const isLoading = ref(false);
const passwordFieldType = ref('password');

const registrError = (message, type) => {
  ElMessage({
    message: message,
    type: 'warning',
  })
}

const registerFormSubmit = async () => {
  try {
    const data = {
      login: form.value.login,
      password: form.value.password,
      firstName: form.value.firstname,
      lastName: form.value.lastname,
    };

    const response = await sendRequest('POST', '/registr/new', data);

    if (response.ok) {
      window.location.href = '/login';
    } else if (response.status === 400) {
      registrError('Такой пользователь есть', 'warning');
    } else {
      ElMessage.error('Сервер сломался');
    }
  } catch (error) {
    console.error('Ошибка при регистрации:', error);
    ElMessage.error('Произошла ошибка при регистрации');
  }
};



const togglePasswordVisibility = () => {
  passwordFieldType.value = passwordFieldType.value === 'password' ? 'text' : 'password';
};
</script>

<style scoped>
.registration-page {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 24px;
}

.registration-page__form-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 450px;
}

.registration-page__login-section .registration-page__header {
  display: flex;
  justify-content: space-between;
  padding: 0 12px;
  margin-bottom: 12px;
}

.registration-page__form-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 350px;
}

.registration-page__form-card {
  width: 100%;
  background-color: #f9f9f9;
}

.registration-page__submit-btn {
  height: 40px;
  width: 100%;
}

.registration-page__login-link-wrapper {
  text-align: center;
}

.registration-page__login-link {
  display: block;
  color: #1e88e5;
}

.registration-page__text-gray {
  color: #9e9e9e;
}
</style>
