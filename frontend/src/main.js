import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import axios from 'axios'

// 设置axios默认配置
axios.defaults.baseURL = 'http://localhost:8000/api'

// 添加请求拦截器
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

// 添加响应拦截器
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // token过期或无效
      store.commit('SET_USER', null)
      store.commit('SET_AUTH', false)
      store.commit('SET_ADMIN', false)
      store.commit('SET_DISTRIBUTOR', false)
      localStorage.removeItem('token')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

// 创建应用实例
const app = createApp(App)

// 挂载组件
app.use(store)
  .use(router)
  .use(ElementPlus)

// 直接挂载应用，路由守卫会处理认证
app.mount('#app') 