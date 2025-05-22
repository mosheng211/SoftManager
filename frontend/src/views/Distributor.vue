<template>
  <div class="distributor-dashboard">
    <el-container>
      <el-header>
        <div class="header-content">
          <div class="logo">软件管理系统 - 分销后台</div>
          <div class="user-info">
            <span>{{ user ? user.name : '' }}</span>
            <el-button type="danger" size="small" @click="logout">退出登录</el-button>
          </div>
        </div>
      </el-header>
      
      <el-main>
        <el-card>
          <template #header>
            <div class="card-header">
              <span>我的邀请用户</span>
              <div>
                <el-button type="primary" size="small" @click="getInviteLink">获取邀请链接</el-button>
                <el-button type="success" size="small" @click="refreshData">刷新数据</el-button>
              </div>
            </div>
          </template>
          
          <!-- 邀请统计 -->
          <el-row :gutter="20" class="stat-row">
            <el-col :span="8">
              <el-card shadow="hover" class="stat-card">
                <h3>邀请用户总数</h3>
                <div class="stat-number">{{ invitedUsers.length }}</div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card shadow="hover" class="stat-card">
                <h3>累计充值金额</h3>
                <div class="stat-number">¥{{ totalRechargeAmount.toFixed(2) }}</div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card shadow="hover" class="stat-card">
                <h3>本月新增用户</h3>
                <div class="stat-number">{{ newUsersThisMonth }}</div>
              </el-card>
            </el-col>
          </el-row>
          
          <!-- 用户列表 -->
          <el-table :data="invitedUsers" style="width: 100%" v-loading="loading">
            <el-table-column prop="name" label="用户名称" />
            <el-table-column prop="email" label="电子邮箱" />
            <el-table-column prop="created_at" label="注册时间">
              <template #default="scope">
                {{ formatDate(scope.row.created_at) }}
              </template>
            </el-table-column>
            <el-table-column prop="total_recharge" label="累计充值金额">
              <template #default="scope">
                ¥{{ scope.row.total_recharge ? scope.row.total_recharge.toFixed(2) : '0.00' }}
              </template>
            </el-table-column>
            <el-table-column prop="expire_time" label="到期时间">
              <template #default="scope">
                {{ formatDate(scope.row.expire_time) }}
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-main>
    </el-container>
    
    <!-- 邀请链接对话框 -->
    <el-dialog
      title="我的邀请链接"
      v-model="inviteLinkDialogVisible"
      width="500px">
      <div class="invite-link-container">
        <p>分享以下链接邀请用户注册：</p>
        <el-input v-model="inviteLink" readonly>
          <template #append>
            <el-button @click="copyInviteLink">复制</el-button>
          </template>
        </el-input>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import axios from 'axios'

export default {
  name: 'Distributor',
  setup() {
    const store = useStore()
    const router = useRouter()
    const loading = ref(false)
    const invitedUsers = ref([])
    const inviteLinkDialogVisible = ref(false)
    const inviteLink = ref('')
    
    // 从store获取用户信息
    const user = computed(() => store.getters.user)
    
    // 计算累计充值金额
    const totalRechargeAmount = computed(() => {
      return invitedUsers.value.reduce((total, user) => {
        return total + (user.total_recharge || 0)
      }, 0)
    })
    
    // 计算本月新增用户
    const newUsersThisMonth = computed(() => {
      const now = new Date()
      const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
      
      return invitedUsers.value.filter(user => {
        return new Date(user.created_at) >= startOfMonth
      }).length
    })
    
    // 格式化日期
    const formatDate = (dateString) => {
      if (!dateString) return '未设置'
      return new Date(dateString).toLocaleString()
    }
    
    // 获取邀请用户列表
    const fetchInvitedUsers = async () => {
      loading.value = true
      
      try {
        const response = await axios.get('/distributor/invited-users')
        if (response.data.status) {
          invitedUsers.value = response.data.data.users
        }
      } catch (error) {
        console.error('获取邀请用户列表失败', error)
        ElMessage.error('获取邀请用户列表失败')
      } finally {
        loading.value = false
      }
    }
    
    // 获取邀请链接
    const getInviteLink = async () => {
      try {
        // 先获取分销商信息
        const response = await axios.get('/distributor/profile')
        if (response.data.status && response.data.data.distributor) {
          const distributorId = response.data.data.distributor.id
          const baseUrl = `${window.location.origin}/register`
          inviteLink.value = `${baseUrl}?inviter=${distributorId}`
          inviteLinkDialogVisible.value = true
        } else {
          ElMessage.error('获取分销商信息失败')
        }
      } catch (error) {
        console.error('获取邀请链接失败', error)
        ElMessage.error('获取邀请链接失败')
      }
    }
    
    // 复制邀请链接
    const copyInviteLink = () => {
      navigator.clipboard.writeText(inviteLink.value)
        .then(() => {
          ElMessage.success('邀请链接已复制到剪贴板')
        })
        .catch(err => {
          console.error('复制失败', err)
          ElMessage.error('复制失败，请手动选择并复制')
        })
    }
    
    // 刷新数据
    const refreshData = () => {
      fetchInvitedUsers()
    }
    
    // 退出登录
    const logout = async () => {
      try {
        await store.dispatch('logout')
        router.push('/login')
      } catch (error) {
        ElMessage.error('退出失败')
      }
    }
    
    // 初始化
    onMounted(() => {
      fetchInvitedUsers()
    })
    
    return {
      user,
      loading,
      invitedUsers,
      totalRechargeAmount,
      newUsersThisMonth,
      inviteLinkDialogVisible,
      inviteLink,
      formatDate,
      getInviteLink,
      copyInviteLink,
      refreshData,
      logout
    }
  }
}
</script>

<style scoped>
.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
}

.logo {
  font-size: 18px;
  font-weight: bold;
  color: #409EFF;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-info span {
  margin-right: 15px;
  color: #fff;
  font-weight: bold;
}

.el-header {
  background-color: #409EFF;
  color: #fff;
  line-height: 60px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-row {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
  padding: 20px;
}

.stat-number {
  font-size: 24px;
  color: #409EFF;
  font-weight: bold;
  margin-top: 10px;
}

.invite-link-container {
  margin: 10px 0;
}
</style> 