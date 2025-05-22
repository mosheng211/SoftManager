import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import UserCenter from '../views/UserCenter.vue'
import Admin from '../views/Admin.vue'
import Distributor from '../views/Distributor.vue'
import store from '../store'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false }
  },
  {
    path: '/user-center',
    name: 'UserCenter',
    component: UserCenter,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: Admin,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/distributor',
    name: 'Distributor',
    component: Distributor,
    meta: { requiresAuth: true, requiresDistributor: true }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// 路由守卫
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token')
  let isAuthenticated = !!token && store.getters.authenticated
  
  // 如果有token但未验证，尝试获取用户信息
  if (token && !isAuthenticated) {
    try {
      const result = await store.dispatch('fetchUserProfile')
      isAuthenticated = result.success
    } catch (error) {
      isAuthenticated = false
      localStorage.removeItem('token')
    }
  }
  
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
  const requiresDistributor = to.matched.some(record => record.meta.requiresDistributor)
  
  // 检查是否需要认证
  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } 
  // 检查是否需要管理员权限
  else if (requiresAdmin && !store.getters.isAdmin) {
    next('/user-center')
  } 
  // 检查是否需要分销商权限
  else if (requiresDistributor && !store.getters.isDistributor) {
    next('/user-center')
  } 
  // 如果已登录，不要访问登录和注册页
  else if (isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    next('/user-center')
  } 
  else {
    next()
  }
})

export default router 