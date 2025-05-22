<template>
  <div class="api-endpoint">
    <div class="api-header">
      <h3>{{ title }}</h3>
      <el-tag :type="methodType" class="method-tag">{{ method }}</el-tag>
      <div class="api-url">{{ url }}</div>
    </div>
    
    <div class="api-description">
      <p>{{ description }}</p>
      <el-tag v-if="auth" type="warning" size="small">需要认证</el-tag>
    </div>
    
    <el-divider content-position="left">参数</el-divider>
    
    <div v-if="params && params.length > 0" class="api-params">
      <el-table :data="params" style="width: 100%">
        <el-table-column prop="name" label="参数名" width="180" />
        <el-table-column prop="type" label="位置" width="120">
          <template #default="scope">
            <el-tag size="small" :type="paramTypeTag(scope.row.type)">
              {{ paramTypeLabel(scope.row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="required" label="必填" width="80">
          <template #default="scope">
            <el-tag size="small" :type="scope.row.required ? 'danger' : 'info'">
              {{ scope.row.required ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="描述" />
      </el-table>
    </div>
    <div v-else class="no-params">
      <p>无需参数</p>
    </div>
    
    <el-divider content-position="left">请求格式</el-divider>
    
    <div v-if="request" class="api-request">
      <div class="code-editor">
        <pre v-html="formattedRequest"></pre>
      </div>
    </div>
    <div v-else class="no-request">
      <p>无请求内容</p>
    </div>
    
    <el-divider content-position="left">响应格式</el-divider>
    
    <div class="api-response">
      <div class="code-editor">
        <pre v-html="formattedResponse"></pre>
      </div>
    </div>
    
    <el-divider content-position="left">在线调试</el-divider>
    
    <div class="api-test">
      <el-form :model="testForm" label-width="120px" size="small">
        <!-- Path参数 -->
        <template v-for="param in pathParams" :key="param.name">
          <el-form-item :label="param.name">
            <el-input v-model="testForm.path[param.name]" :placeholder="param.description" />
          </el-form-item>
        </template>
        
        <!-- Query参数 -->
        <template v-for="param in queryParams" :key="param.name">
          <el-form-item :label="param.name">
            <el-input v-model="testForm.query[param.name]" :placeholder="param.description" />
          </el-form-item>
        </template>
        
        <!-- Body参数 -->
        <template v-if="bodyParams.length > 0">
          <el-form-item label="请求体">
            <el-input
              v-model="testForm.bodyJson"
              type="textarea"
              :rows="5"
              placeholder="请求体JSON"
            />
          </el-form-item>
        </template>
        
        <!-- 认证令牌 -->
        <el-form-item v-if="auth" label="认证令牌">
          <el-input v-model="testForm.token" placeholder="Bearer Token" />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="testApi" :loading="testing">发送请求</el-button>
          <el-button @click="resetTest">重置</el-button>
        </el-form-item>
      </el-form>
      
      <!-- 测试结果 -->
      <div v-if="testResult" class="api-test-result">
        <el-divider content-position="left">测试结果</el-divider>
        <div class="result-header">
          <el-tag :type="testResult.success ? 'success' : 'danger'">
            {{ testResult.status }}
          </el-tag>
          <span class="result-time">响应时间: {{ testResult.time }}ms</span>
        </div>
        <div class="code-editor">
          <pre v-html="formattedTestResult"></pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ApiEndpoint',
  props: {
    title: {
      type: String,
      required: true
    },
    method: {
      type: String,
      required: true,
      validator: (value) => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'].includes(value)
    },
    url: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    },
    auth: {
      type: Boolean,
      default: false
    },
    params: {
      type: Array,
      default: () => []
    },
    request: {
      type: Object,
      default: null
    },
    response: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      testForm: {
        path: {},
        query: {},
        bodyJson: this.request ? JSON.stringify(this.request, null, 2) : '',
        token: localStorage.getItem('token') || ''
      },
      testing: false,
      testResult: null
    }
  },
  computed: {
    methodType() {
      const types = {
        GET: 'success',
        POST: 'primary',
        PUT: 'warning',
        PATCH: 'warning',
        DELETE: 'danger'
      }
      return types[this.method] || 'info'
    },
    pathParams() {
      return this.params.filter(p => p.type === 'path')
    },
    queryParams() {
      return this.params.filter(p => p.type === 'query')
    },
    bodyParams() {
      return this.params.filter(p => p.type === 'body')
    },
    formattedRequest() {
      return this.formatJson(this.request)
    },
    formattedResponse() {
      return this.formatJson(this.response)
    },
    formattedTestResult() {
      if (!this.testResult) return ''
      return this.formatJson(this.testResult.data)
    }
  },
  methods: {
    paramTypeTag(type) {
      const types = {
        path: 'danger',
        query: 'info',
        body: 'warning'
      }
      return types[type] || 'info'
    },
    paramTypeLabel(type) {
      const labels = {
        path: '路径参数',
        query: '查询参数',
        body: '请求体参数'
      }
      return labels[type] || type
    },
    resetTest() {
      this.testForm = {
        path: {},
        query: {},
        bodyJson: this.request ? JSON.stringify(this.request, null, 2) : '',
        token: localStorage.getItem('token') || ''
      }
      this.testResult = null
    },
    async testApi() {
      this.testing = true
      this.testResult = null
      
      try {
        // 构建URL
        let testUrl = this.url
        for (const key in this.testForm.path) {
          testUrl = testUrl.replace(`:${key}`, this.testForm.path[key])
        }
        
        // 不再添加API前缀，因为axios已经配置了baseURL
        // 如果URL以/api开头，需要移除掉，避免重复
        if (testUrl.startsWith('/api/')) {
          testUrl = testUrl.substring(4) // 移除开头的'/api'
        } else if (testUrl.startsWith('/api')) {
          testUrl = testUrl.substring(4) // 移除开头的'/api'
        }
        
        // 处理查询参数
        const params = { ...this.testForm.query }
        
        // 处理请求体
        let data = null
        if (this.bodyParams.length > 0 && this.testForm.bodyJson) {
          try {
            data = JSON.parse(this.testForm.bodyJson)
          } catch (e) {
            this.$message.error('请求体JSON格式错误')
            this.testing = false
            return
          }
        }
        
        // 设置请求头
        const headers = {}
        if (this.auth && this.testForm.token) {
          headers['Authorization'] = `Bearer ${this.testForm.token}`
        }
        
        // 发送请求
        const startTime = Date.now()
        const response = await axios({
          method: this.method.toLowerCase(),
          url: testUrl,
          params,
          data,
          headers
        })
        const endTime = Date.now()
        
        // 处理结果
        this.testResult = {
          success: true,
          status: response.status,
          time: endTime - startTime,
          data: response.data
        }
      } catch (error) {
        this.testResult = {
          success: false,
          status: error.response && error.response.status ? error.response.status : 'Error',
          time: 0,
          data: error.response && error.response.data ? error.response.data : { message: error.message }
        }
      } finally {
        this.testing = false
      }
    },
    formatJson(obj) {
      if (!obj) return ''
      
      // 先将JSON转换为格式化字符串
      const str = JSON.stringify(obj, null, 2)
      
      // 使用更精确的方式处理JSON高亮
      return str.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        let cls = 'number'
        if (/^"/.test(match)) {
          if (/:$/.test(match)) {
            cls = 'key'
            // 去掉末尾的冒号
            match = match.substring(0, match.length - 1)
            return '<span class="' + cls + '">' + match + '</span>:'
          } else {
            cls = 'string'
          }
        } else if (/true|false/.test(match)) {
          cls = 'boolean'
        } else if (/null/.test(match)) {
          cls = 'null'
        }
        return '<span class="' + cls + '">' + match + '</span>'
      })
    }
  }
}
</script>

<style scoped>
.api-endpoint {
  margin-bottom: 30px;
  padding: 15px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.api-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.api-header h3 {
  margin: 0;
  margin-right: 15px;
}

.method-tag {
  margin-right: 10px;
}

.api-url {
  font-family: monospace;
  background-color: #f5f7fa;
  padding: 5px 10px;
  border-radius: 4px;
  flex-grow: 1;
  text-align: left;
}

.api-description {
  margin-bottom: 15px;
}

.api-description p {
  margin-top: 0;
  margin-bottom: 10px;
}

.code-editor {
  background-color: #282c34;
  border-radius: 4px;
  overflow: hidden;
  padding: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  position: relative;
}

.code-editor::before {
  content: "JSON";
  position: absolute;
  top: 0;
  right: 0;
  background: #3a3f4b;
  color: #abb2bf;
  padding: 2px 8px;
  font-size: 12px;
  border-bottom-left-radius: 4px;
}

.code-editor pre {
  margin: 0;
  padding: 16px;
  background-color: #282c34;
  color: #abb2bf;
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
  overflow-x: auto;
  text-align: left;
  border-radius: 4px;
}

/* JSON语法高亮 */
.code-editor .string { color: #98c379; }
.code-editor .number { color: #d19a66; }
.code-editor .boolean { color: #c678dd; }
.code-editor .null { color: #c678dd; }
.code-editor .key { color: #61afef; }

.no-params, .no-request {
  color: #909399;
  font-style: italic;
}

.api-test-result {
  margin-top: 20px;
}

.result-header {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.result-time {
  margin-left: 10px;
  color: #909399;
}
</style> 