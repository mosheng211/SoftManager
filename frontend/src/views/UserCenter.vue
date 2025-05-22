<template>
  <div class="user-center">
    <el-container>
      <el-header>
        <div class="header-content">
          <div class="logo">软件管理系统</div>
          <div class="user-info">
            <template v-if="isAdmin">
              <el-button type="text" @click="goToAdmin">管理后台</el-button>
            </template>
            <template v-if="isDistributor">
              <el-button type="text" @click="goToDistributor">分销后台</el-button>
            </template>
            <span>{{ user ? user.name : '' }}</span>
            <el-button type="text" @click="logout">退出</el-button>
          </div>
        </div>
      </el-header>
      
      <el-main>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>个人信息</span>
                </div>
              </template>
              <div v-if="user" class="info-item">
                <label>账号名称：</label>
                <span>{{ user.name }}</span>
              </div>
              <div v-if="user" class="info-item">
                <label>电子邮箱：</label>
                <span>{{ user.email }}</span>
              </div>
              <div v-if="user" class="info-item">
                <label>软件到期时间：</label>
                <span :class="{ 'expired': isExpired }">
                  {{ formatExpireTime(user.expire_time) }}
                  <el-tag v-if="isExpired" type="danger" size="small">已过期</el-tag>
                </span>
              </div>
              <div v-if="inviter" class="info-item">
                <label>销售经理：</label>
                <span>{{ inviter.nickname }}</span>
              </div>
              
              <div class="info-action" style="margin-top: 20px; text-align: center;">
                <el-button type="danger" @click="logout">退出登录</el-button>
              </div>
            </el-card>
          </el-col>
          
          <el-col :span="16">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>软件功能</span>
                </div>
              </template>
              
              <div class="function-area">
                <el-button 
                  type="primary" 
                  size="large" 
                  :disabled="isExpired"
                  @click="startUsing">
                  开始使用
                </el-button>
                
                <div v-if="isExpired" class="expire-notice">
                  <p>您的软件使用权限已过期，请充值后继续使用</p>
                  <el-button type="danger" @click="showRechargeDialog">立即充值</el-button>
                </div>
              </div>
            </el-card>
            
            <el-card style="margin-top: 20px">
              <template #header>
                <div class="card-header">
                  <span>充值套餐</span>
                </div>
              </template>
              
              <div class="plan-list">
                <el-row :gutter="20">
                  <el-col :span="8" v-for="plan in plans" :key="plan.id">
                    <el-card shadow="hover" class="plan-card">
                      <h3>{{ plan.name }}</h3>
                      <div class="plan-price">¥{{ plan.price }}</div>
                      <div class="plan-desc">{{ plan.description }}</div>
                      <el-button type="primary" @click="selectPlan(plan)">选择</el-button>
                    </el-card>
                  </el-col>
                </el-row>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </el-main>
    </el-container>
    
    <!-- 充值对话框 -->
    <el-dialog
      title="软件充值"
      v-model="rechargeDialogVisible"
      width="400px">
      <div v-if="selectedPlan">
        <h3>{{ selectedPlan.name }}</h3>
        <p>价格: ¥{{ selectedPlan.price }}</p>
        <p>{{ selectedPlan.description }}</p>
        
        <div class="payment-methods">
          <div class="method-title">支付方式</div>
          <el-radio-group v-model="paymentMethod">
            <el-radio v-for="method in paymentMethods" :key="method.id" :label="method.code">
              {{ method.name }}
            </el-radio>
          </el-radio-group>
        </div>
      </div>
      
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="rechargeDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="doRecharge" :loading="rechargingLoading">
            确认支付
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import axios from 'axios'

export default {
  name: 'UserCenter',
  setup() {
    const store = useStore()
    const router = useRouter()
    const plans = ref([])
    const paymentMethods = ref([])
    const inviter = ref(null)
    const rechargeDialogVisible = ref(false)
    const selectedPlan = ref(null)
    const paymentMethod = ref('')
    const rechargingLoading = ref(false)
    
    // 从store获取用户信息
    const user = computed(() => store.getters.user)
    
    // 判断是否为管理员
    const isAdmin = computed(() => store.getters.isAdmin)
    
    // 判断是否为分销商
    const isDistributor = computed(() => store.getters.isDistributor)
    
    // 判断是否过期
    const isExpired = computed(() => {
      if (!user.value || !user.value.expire_time) return true
      return new Date(user.value.expire_time) < new Date()
    })
    
    // 格式化到期时间
    const formatExpireTime = (time) => {
      if (!time) return '未设置'
      return new Date(time).toLocaleString()
    }
    
    // 加载数据
    onMounted(async () => {
      // 获取用户详细信息
      const profileResult = await store.dispatch('fetchUserProfile')
      if (profileResult.success && profileResult.data.inviter) {
        inviter.value = profileResult.data.inviter
      }
      
      // 获取充值套餐
      try {
        const response = await axios.get('/user/recharge/plans')
        if (response.data.status) {
          plans.value = response.data.data.plans
        }
      } catch (error) {
        console.error('获取充值套餐失败', error)
      }
      
      // 获取支付方式
      try {
        const response = await axios.get('/payment/methods')
        if (response.data.status) {
          paymentMethods.value = response.data.data.methods
          if (paymentMethods.value.length > 0) {
            paymentMethod.value = paymentMethods.value[0].code
          }
        }
      } catch (error) {
        console.error('获取支付方式失败', error)
      }
    })
    
    // 退出登录
    const logout = async () => {
      try {
        await store.dispatch('logout')
        router.push('/login')
      } catch (error) {
        ElMessage.error('退出失败')
      }
    }
    
    // 开始使用
    const startUsing = () => {
      if (isExpired.value) {
        ElMessage.warning('您的软件使用权限已过期，请充值后继续使用')
        showRechargeDialog()
        return
      }
      
      ElMessage.success('正在启动软件...')
      // 这里实际项目中应该跳转到软件使用页面或启动软件
    }
    
    // 显示充值对话框
    const showRechargeDialog = () => {
      if (plans.value.length > 0) {
        selectedPlan.value = plans.value[0]
        
        // 如果没有已启用的支付方式
        if (paymentMethods.value.length === 0) {
          ElMessage.error('暂无可用支付方式，请联系管理员')
          return
        }
        
        rechargeDialogVisible.value = true
      } else {
        ElMessage.error('获取充值套餐失败')
      }
    }
    
    // 选择套餐
    const selectPlan = (plan) => {
      selectedPlan.value = plan
      rechargeDialogVisible.value = true
    }
    
    // 执行充值
    const doRecharge = async () => {
      if (!selectedPlan.value) {
        ElMessage.warning('请选择充值套餐')
        return
      }
      
      if (!paymentMethod.value) {
        ElMessage.warning('请选择支付方式')
        return
      }
      
      rechargingLoading.value = true
      
      try {
        const response = await axios.post('/payment/create', {
          plan_id: selectedPlan.value.id,
          payment_method: paymentMethod.value
        })
        
        if (response.data.status) {
          rechargingLoading.value = false
          rechargeDialogVisible.value = false
          
          // 打开支付页面
          window.open(response.data.data.payment_url, '_blank')
          
          ElMessageBox.alert(
            '请在新打开的页面完成支付，支付完成后刷新页面查看最新状态',
            '支付提示',
            {
              confirmButtonText: '我已支付',
              callback: () => {
                // 刷新用户信息
                store.dispatch('fetchUserProfile')
              }
            }
          )
        }
      } catch (error) {
        ElMessage.error('发起充值失败')
        rechargingLoading.value = false
      }
    }
    
    // 跳转到管理后台
    const goToAdmin = () => {
      router.push('/admin')
    }
    
    // 跳转到分销后台
    const goToDistributor = () => {
      router.push('/distributor')
    }
    
    return {
      user,
      inviter,
      plans,
      paymentMethods,
      isExpired,
      isAdmin,
      isDistributor,
      formatExpireTime,
      logout,
      startUsing,
      showRechargeDialog,
      rechargeDialogVisible,
      selectedPlan,
      paymentMethod,
      selectPlan,
      doRecharge,
      rechargingLoading,
      goToAdmin,
      goToDistributor
    }
  }
}
</script>

<style scoped>
.user-center {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.el-header {
  background-color: #409EFF;
  color: #fff;
  line-height: 60px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 18px;
  font-weight: bold;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-info span {
  margin-right: 10px;
}

.el-main {
  padding: 20px;
  background-color: #f5f7fa;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-item {
  margin-bottom: 15px;
}

.info-item label {
  font-weight: bold;
  margin-right: 10px;
}

.expired {
  color: #F56C6C;
}

.function-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 0;
}

.expire-notice {
  margin-top: 20px;
  text-align: center;
  color: #F56C6C;
}

.plan-list {
  margin-top: 20px;
}

.plan-card {
  text-align: center;
  margin-bottom: 20px;
}

.plan-price {
  font-size: 24px;
  color: #F56C6C;
  margin: 10px 0;
}

.plan-desc {
  color: #606266;
  margin-bottom: 15px;
}

.payment-methods {
  margin-top: 20px;
}

.method-title {
  margin-bottom: 10px;
  font-weight: bold;
}
</style> 