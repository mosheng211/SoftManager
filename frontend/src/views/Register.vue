<template>
  <div class="register-container">
    <el-card class="register-card">
      <h2>软件管理系统 - 注册</h2>
      
      <!-- 有邀请人ID时显示注册表单 -->
      <template v-if="inviterId">
        <p v-if="inviterName" class="inviter-info">邀请人: {{ inviterName }}</p>
        
        <el-form :model="registerData" :rules="registerRules" ref="registerFormRef" class="register-form">
          <el-form-item prop="name">
            <el-input v-model="registerData.name" placeholder="用户名" prefix-icon="el-icon-user"></el-input>
          </el-form-item>
          <el-form-item prop="email">
            <el-input v-model="registerData.email" type="email" placeholder="电子邮箱" prefix-icon="el-icon-message"></el-input>
          </el-form-item>
          <el-form-item prop="password">
            <el-input v-model="registerData.password" type="password" placeholder="密码" prefix-icon="el-icon-lock"></el-input>
          </el-form-item>
          <el-form-item prop="password_confirmation">
            <el-input v-model="registerData.password_confirmation" type="password" placeholder="确认密码" prefix-icon="el-icon-lock"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="register-button" @click="submitRegister" :loading="loading">注册</el-button>
          </el-form-item>
        </el-form>
      </template>
      
      <!-- 没有邀请人ID时只显示提示信息 -->
      <div v-else class="error-message">
        <p>您需要通过邀请链接注册</p>
        <el-button type="primary" @click="goToLogin" style="margin-top: 15px;">返回登录</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import axios from 'axios'

export default {
  name: 'Register',
  setup() {
    const store = useStore()
    const router = useRouter()
    const route = useRoute()
    const registerFormRef = ref(null)
    const loading = ref(false)
    const inviterId = ref(null)
    const inviterName = ref('')
    
    const registerData = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      inviter_id: ''
    })
    
    const registerRules = {
      name: [
        { required: true, message: '请输入用户名', trigger: 'blur' },
        { min: 2, max: 20, message: '用户名长度应在2-20个字符之间', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入电子邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入有效的电子邮箱', trigger: 'blur' }
      ],
      password: [
        { required: true, message: '请输入密码', trigger: 'blur' },
        { min: 8, message: '密码长度不能少于8个字符', trigger: 'blur' }
      ],
      password_confirmation: [
        { required: true, message: '请确认密码', trigger: 'blur' },
        { 
          validator: (rule, value, callback) => {
            if (value !== registerData.password) {
              callback(new Error('两次输入的密码不一致'))
            } else {
              callback()
            }
          }, 
          trigger: 'blur' 
        }
      ]
    }
    
    // 检查邀请参数
    onMounted(async () => {
      const inviter = route.query.inviter
      
      if (inviter) {
        inviterId.value = inviter
        registerData.inviter_id = inviter
        
        // 获取邀请人信息
        try {
          // 由于API可能未实现，暂时设置默认值
          inviterName.value = `邀请人(ID:${inviter})`
          
          /* 
          // 此API暂未实现，暂时注释掉
          const response = await axios.get(`/users/${inviter}`)
          if (response.data.status) {
            inviterName.value = response.data.data.user.name
          }
          */
        } catch (error) {
          console.error('获取邀请人信息失败', error)
          // 设置默认值，防止UI显示空白
          inviterName.value = `邀请人(ID:${inviter})`
        }
      }
    })
    
    const submitRegister = async () => {
      if (!registerFormRef.value) return
      
      // 检查是否有邀请人ID
      if (!inviterId.value) {
        ElMessage.error('您需要通过邀请链接注册')
        return
      }
      
      await registerFormRef.value.validate(async (valid) => {
        if (valid) {
          loading.value = true
          
          const result = await store.dispatch('register', registerData)
          
          loading.value = false
          
          if (result.success) {
            ElMessage.success('注册成功')
            router.push('/login')
          } else {
            ElMessage.error(result.message || '注册失败')
          }
        }
      })
    }
    
    const goToLogin = () => {
      router.push('/login')
    }
    
    return {
      registerFormRef,
      registerRules,
      registerData,
      loading,
      inviterId,
      inviterName,
      submitRegister,
      goToLogin
    }
  }
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f0f2f5;
}

.register-card {
  width: 400px;
  padding: 20px;
}

.register-form {
  margin-top: 20px;
}

.register-button {
  width: 100%;
}

.inviter-info {
  color: #409EFF;
  margin-bottom: 20px;
}

.error-message {
  color: #F56C6C;
  text-align: center;
  margin-top: 20px;
}
</style> 