import { createStore } from 'vuex'
import axios from 'axios'

// 设置axios默认值
axios.defaults.baseURL = 'http://localhost:8000/api'
axios.defaults.headers.common['Accept'] = 'application/json'

// 请求拦截器添加token
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

// 响应拦截器处理过期token和其他设备登录
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response) {
      // 处理401错误
      if (error.response.status === 401) {
        // token过期或无效
        localStorage.removeItem('token')
        store.commit('SET_USER', null)
        store.commit('SET_AUTH', false)
        
        // 如果是设备改变导致的401
        if (error.response.data.code === 'device_changed') {
          // 提示用户
          alert('您的账号已在其他设备登录，请重新登录')
        }
        
        // 跳转到登录页
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

const store = createStore({
  state: {
    user: null,
    authenticated: false,
    isAdmin: false,
    isDistributor: false
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user
    },
    SET_AUTH(state, status) {
      state.authenticated = status
    },
    SET_ADMIN(state, status) {
      state.isAdmin = status
    },
    SET_DISTRIBUTOR(state, status) {
      state.isDistributor = status
    }
  },
  actions: {
    async login({ commit }, credentials) {
      try {
        const response = await axios.post('/login', credentials)
        
        if (response.data.status) {
          const token = response.data.data.token
          const user = response.data.data.user
          
          // 存储token
          localStorage.setItem('token', token)
          
          // 更新状态
          commit('SET_USER', user)
          commit('SET_AUTH', true)
          commit('SET_ADMIN', user.role === 'admin')
          commit('SET_DISTRIBUTOR', user.role === 'distributor')
          
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response ? error.response.data.message : '登录失败，请检查网络连接'
        }
      }
    },
    
    async register({ commit }, userData) {
      try {
        const response = await axios.post('/register', userData)
        
        if (response.data.status) {
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response ? error.response.data.message : '注册失败，请检查网络连接'
        }
      }
    },
    
    async logout({ commit }) {
      try {
        await axios.post('/logout')
        
        // 清除token
        localStorage.removeItem('token')
        
        // 更新状态
        commit('SET_USER', null)
        commit('SET_AUTH', false)
        commit('SET_ADMIN', false)
        commit('SET_DISTRIBUTOR', false)
        
        return { success: true }
      } catch (error) {
        return { success: false, message: '退出失败' }
      }
    },
    
    async fetchUserProfile({ commit }) {
      try {
        const response = await axios.get('/user/profile')
        
        if (response.data.status) {
          const user = response.data.data.user
          commit('SET_USER', user)
          commit('SET_AUTH', true)
          commit('SET_ADMIN', user.role === 'admin')
          commit('SET_DISTRIBUTOR', user.role === 'distributor')
          return { success: true, data: response.data.data }
        }
        
        // 如果接口返回失败状态
        localStorage.removeItem('token')
        commit('SET_USER', null)
        commit('SET_AUTH', false)
        commit('SET_ADMIN', false)
        commit('SET_DISTRIBUTOR', false)
        return { success: false, message: response.data.message || '获取用户信息失败' }
      } catch (error) {
        // 如果请求出现错误
        localStorage.removeItem('token')
        commit('SET_USER', null)
        commit('SET_AUTH', false)
        commit('SET_ADMIN', false)
        commit('SET_DISTRIBUTOR', false)
        let errorMessage = '获取用户信息失败'
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        }
        return { success: false, message: errorMessage }
      }
    }
  },
  getters: {
    user: state => state.user,
    authenticated: state => state.authenticated,
    isAdmin: state => state.isAdmin,
    isDistributor: state => state.isDistributor
  }
})

export default store 