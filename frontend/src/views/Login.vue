<template>
  <div class="login-container">
    <el-card class="login-card">
      <h2>软件管理系统</h2>
      <el-form :model="loginData" :rules="loginRules" ref="loginFormRef" class="login-form">
        <el-form-item prop="email">
          <el-input v-model="loginData.email" type="email" placeholder="电子邮箱" prefix-icon="el-icon-user"></el-input>
        </el-form-item>
        <el-form-item prop="password">
          <el-input v-model="loginData.password" type="password" placeholder="密码" prefix-icon="el-icon-lock"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" class="login-button" @click="submitLogin" :loading="loading">登录</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

export default {
  name: 'Login',
  setup() {
    const store = useStore()
    const router = useRouter()
    const loginFormRef = ref(null)
    const loading = ref(false)
    
    const loginData = reactive({
      email: '',
      password: ''
    })
    
    const loginRules = {
      email: [
        { required: true, message: '请输入电子邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入有效的电子邮箱', trigger: 'blur' }
      ],
      password: [
        { required: true, message: '请输入密码', trigger: 'blur' },
        { min: 8, message: '密码长度不能少于8个字符', trigger: 'blur' }
      ]
    }
    
    const submitLogin = async () => {
      if (!loginFormRef.value) return
      
      await loginFormRef.value.validate(async (valid) => {
        if (valid) {
          loading.value = true
          
          const result = await store.dispatch('login', loginData)
          
          loading.value = false
          
          if (result.success) {
            // 根据用户角色跳转到不同页面
            if (store.getters.isAdmin) {
              router.push('/admin')
            } else if (store.getters.isDistributor) {
              router.push('/distributor')
            } else {
              router.push('/user-center')
            }
          } else {
            ElMessage.error(result.message || '登录失败')
          }
        } else {
          console.log('表单验证失败')
          return false
        }
      })
    }
    
    return {
      loginFormRef,
      loginRules,
      loginData,
      loading,
      submitLogin
    }
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f0f2f5;
}

.login-card {
  width: 350px;
  padding: 20px;
}

.login-form {
  margin-top: 20px;
}

.login-button {
  width: 100%;
}
</style> 