<template>
  <div class="admin">
    <el-container>
      <el-header>
        <div class="header-content">
          <div class="logo">软件管理系统 - 管理后台</div>
          <div class="user-info">
            <span>{{ user ? user.name : '' }}</span>
            <el-button type="danger" size="small" @click="logout">退出登录</el-button>
          </div>
        </div>
      </el-header>
      
      <el-container>
        <el-aside width="200px">
          <el-menu
            default-active="users"
            class="el-menu-vertical"
            @select="handleMenuSelect">
            <el-menu-item index="users">
              <i class="el-icon-user"></i>
              <span>用户管理</span>
            </el-menu-item>
            <el-menu-item index="distributors">
              <i class="el-icon-s-cooperation"></i>
              <span>分销管理</span>
            </el-menu-item>
            <el-menu-item index="api-docs">
              <i class="el-icon-document"></i>
              <span>API文档</span>
            </el-menu-item>
            <el-menu-item index="settings">
              <i class="el-icon-setting"></i>
              <span>系统设置</span>
            </el-menu-item>
            <el-menu-item index="payment">
              <i class="el-icon-money"></i>
              <span>支付设置</span>
            </el-menu-item>
            <div class="menu-divider"></div>
            <el-menu-item index="logout" @click="logout" class="logout-menu-item">
              <i class="el-icon-switch-button"></i>
              <span>退出登录</span>
            </el-menu-item>
          </el-menu>
        </el-aside>
        
        <el-main>
          <!-- 用户管理 -->
          <div v-if="activeMenu === 'users'">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>用户列表</span>
                  <el-input
                    v-model="userKeyword"
                    placeholder="搜索用户"
                    style="width: 200px"
                    clearable
                    @clear="fetchUsers"
                    @keyup.enter="fetchUsers">
                    <template #suffix>
                      <el-button icon="el-icon-search" circle @click="fetchUsers"></el-button>
                    </template>
                  </el-input>
                </div>
              </template>
              
              <el-table :data="users" style="width: 100%" v-loading="loadingUsers">
                <el-table-column prop="id" label="ID" width="60"></el-table-column>
                <el-table-column prop="name" label="用户名"></el-table-column>
                <el-table-column prop="email" label="邮箱"></el-table-column>
                <el-table-column label="到期时间">
                  <template #default="scope">
                    <span :class="{ 'expired': isExpired(scope.row.expire_time) }">
                      {{ formatDateTime(scope.row.expire_time) }}
                    </span>
                  </template>
                </el-table-column>
                <el-table-column prop="inviter_nickname" label="邀请人"></el-table-column>
                <el-table-column label="操作" width="250">
                  <template #default="scope">
                    <el-button
                      size="small"
                      type="primary"
                      @click="editUserExpireTime(scope.row)">
                      修改到期时间
                    </el-button>
                    <el-button
                      size="small"
                      type="warning"
                      @click="editUserPassword(scope.row)">
                      修改密码
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
              
              <div class="pagination-container">
                <el-pagination
                  background
                  layout="prev, pager, next"
                  :total="userPagination.total"
                  :page-size="userPagination.per_page"
                  :current-page="userPagination.current_page"
                  @current-change="handleUserPageChange">
                </el-pagination>
              </div>
            </el-card>
          </div>
          
          <!-- 分销管理 -->
          <div v-if="activeMenu === 'distributors'">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>分销列表</span>
                  <div>
                    <el-input
                      v-model="distributorKeyword"
                      placeholder="搜索分销"
                      style="width: 200px; margin-right: 10px"
                      clearable
                      @clear="fetchDistributors"
                      @keyup.enter="fetchDistributors">
                      <template #suffix>
                        <el-button icon="el-icon-search" circle @click="fetchDistributors"></el-button>
                      </template>
                    </el-input>
                    <el-button type="primary" @click="showAddDistributorDialog">添加分销</el-button>
                  </div>
                </div>
              </template>
              
              <el-table :data="distributors" style="width: 100%" v-loading="loadingDistributors">
                <el-table-column prop="id" label="ID" width="60"></el-table-column>
                <el-table-column label="用户信息">
                  <template #default="scope">
                    <div>{{ scope.row.user ? scope.row.user.name : '未知' }}</div>
                    <div class="sub-text">{{ scope.row.user ? scope.row.user.email : '' }}</div>
                  </template>
                </el-table-column>
                <el-table-column prop="merchant_id" label="商户ID"></el-table-column>
                <el-table-column prop="nickname" label="分销昵称"></el-table-column>
                <el-table-column label="状态" width="100">
                  <template #default="scope">
                    <el-tag :type="scope.row.status ? 'success' : 'danger'">
                      {{ scope.row.status ? '启用' : '禁用' }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="250">
                  <template #default="scope">
                    <el-button
                      size="small"
                      type="primary"
                      @click="editDistributor(scope.row)">
                      编辑
                    </el-button>
                    <el-button
                      size="small"
                      :type="scope.row.status ? 'warning' : 'success'"
                      @click="toggleDistributorStatus(scope.row)">
                      {{ scope.row.status ? '禁用' : '启用' }}
                    </el-button>
                    <el-button
                      size="small"
                      type="danger"
                      @click="deleteDistributor(scope.row)">
                      删除
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
              
              <div class="pagination-container">
                <el-pagination
                  background
                  layout="prev, pager, next"
                  :total="distributorPagination.total"
                  :page-size="distributorPagination.per_page"
                  :current-page="distributorPagination.current_page"
                  @current-change="handleDistributorPageChange">
                </el-pagination>
              </div>
            </el-card>
          </div>
          
          <!-- API文档 -->
          <div v-if="activeMenu === 'api-docs'">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>API文档</span>
                </div>
              </template>
              
              <el-tabs type="border-card">
                <el-tab-pane label="公共API">
                  <api-endpoint 
                    title="用户信息" 
                    method="GET" 
                    url="/users/:id" 
                    description="获取指定用户的基本信息"
                    :params="[
                      { name: 'id', type: 'path', required: true, description: '用户ID' }
                    ]"
                    :response="{ 
                      status: true,
                      message: '获取用户信息成功',
                      data: { 
                        user: { 
                          id: 1, 
                          name: '测试用户', 
                          email: 'test@example.com',
                          role: 'user',
                          created_at: '2023-06-01T12:00:00'
                        } 
                      }
                    }"
                  ></api-endpoint>
                </el-tab-pane>
                
                <el-tab-pane label="认证API">
                  <api-endpoint 
                    title="用户登录" 
                    method="POST" 
                    url="/login" 
                    description="用户登录并获取认证令牌"
                    :request="{ email: 'user@example.com', password: 'password' }"
                    :params="[
                      { name: 'email', type: 'body', required: true, description: '用户邮箱' },
                      { name: 'password', type: 'body', required: true, description: '用户密码' }
                    ]"
                    :response="{ 
                      status: true,
                      message: '登录成功',
                      data: { 
                        token: 'eyJ0eXAiOi...', 
                        user: { 
                          id: 1, 
                          name: '测试用户', 
                          email: 'test@example.com',
                          role: 'user',
                          expire_time: '2023-12-31T23:59:59'
                        } 
                      }
                    }"
                  ></api-endpoint>
                  
                  <api-endpoint 
                    title="用户注册" 
                    method="POST" 
                    url="/register" 
                    description="注册新用户"
                    :request="{ 
                      name: '新用户', 
                      email: 'new@example.com', 
                      password: 'password',
                      inviter_id: 1
                    }"
                    :params="[
                      { name: 'name', type: 'body', required: true, description: '用户名' },
                      { name: 'email', type: 'body', required: true, description: '用户邮箱' },
                      { name: 'password', type: 'body', required: true, description: '用户密码' },
                      { name: 'inviter_id', type: 'body', required: false, description: '邀请人ID（分销商ID）' }
                    ]"
                    :response="{ 
                      status: true,
                      message: '注册成功',
                      data: { 
                        user: { 
                          id: 2, 
                          name: '新用户', 
                          email: 'new@example.com'
                        } 
                      }
                    }"
                  ></api-endpoint>
                  
                  <api-endpoint 
                    title="退出登录" 
                    method="POST" 
                    url="/logout" 
                    description="注销当前用户登录状态"
                    :auth="true"
                    :response="{ 
                      status: true,
                      message: '退出成功',
                      data: null
                    }"
                  ></api-endpoint>
                </el-tab-pane>
                
                <el-tab-pane label="用户API">
                  <api-endpoint 
                    title="获取个人资料" 
                    method="GET" 
                    url="/user/profile" 
                    description="获取当前登录用户的个人信息"
                    :auth="true"
                    :response="{ 
                      status: true,
                      message: '获取个人信息成功',
                      data: { 
                        user: { 
                          id: 1, 
                          name: '测试用户', 
                          email: 'test@example.com',
                          role: 'user',
                          expire_time: '2023-12-31T23:59:59',
                          created_at: '2023-01-01T10:00:00'
                        } 
                      }
                    }"
                  ></api-endpoint>
                </el-tab-pane>
                
                <el-tab-pane label="分销商API">
                  <api-endpoint 
                    title="获取邀请用户" 
                    method="GET" 
                    url="/distributor/invited-users" 
                    description="获取分销商邀请的用户列表"
                    :auth="true"
                    :response="{ 
                      status: true,
                      message: '获取邀请用户列表成功',
                      data: { 
                        users: [
                          { 
                            id: 2, 
                            name: '邀请用户1', 
                            email: 'invited1@example.com',
                            created_at: '2023-02-01T10:00:00',
                            expire_time: '2023-12-31T23:59:59',
                            total_recharge: 99.9
                          }
                        ] 
                      }
                    }"
                  ></api-endpoint>
                  
                  <api-endpoint 
                    title="获取分销商资料" 
                    method="GET" 
                    url="/distributor/profile" 
                    description="获取当前分销商的资料信息"
                    :auth="true"
                    :response="{ 
                      status: true,
                      message: '获取分销商资料成功',
                      data: { 
                        distributor: { 
                          id: 1, 
                          user_id: 1,
                          merchant_id: 10001,
                          nickname: '测试分销商',
                          status: true,
                          created_at: '2023-01-01T10:00:00',
                          updated_at: '2023-01-01T10:00:00'
                        } 
                      }
                    }"
                  ></api-endpoint>
                </el-tab-pane>
                
                <el-tab-pane label="管理员API">
                  <api-endpoint 
                    title="获取用户列表" 
                    method="GET" 
                    url="/admin/users" 
                    description="获取系统用户列表(分页)"
                    :auth="true"
                    :params="[
                      { name: 'page', type: 'query', required: false, description: '页码' },
                      { name: 'keyword', type: 'query', required: false, description: '搜索关键词' }
                    ]"
                    :response="{ 
                      status: true,
                      message: '获取用户列表成功',
                      data: { 
                        users: {
                          current_page: 1,
                          data: [
                            { 
                              id: 1, 
                              name: '测试用户', 
                              email: 'test@example.com',
                              expire_time: '2023-12-31T23:59:59',
                              inviter_id: null,
                              inviter_nickname: null
                            }
                          ],
                          total: 1,
                          per_page: 15
                        }
                      }
                    }"
                  ></api-endpoint>
                </el-tab-pane>
              </el-tabs>
            </el-card>
          </div>
          
          <!-- 系统设置 -->
          <div v-if="activeMenu === 'settings'">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>系统设置</span>
                </div>
              </template>
              
              <el-form label-width="180px" :model="settingsForm" ref="settingsForm">
                <el-form-item label="新用户默认试用时间">
                  <el-input-number 
                    v-model="settingsForm.trial_minutes" 
                    :min="0" 
                    :step="10"
                    style="width: 200px">
                  </el-input-number>
                  <span class="form-help-text">分钟（0表示不开启试用）</span>
                </el-form-item>
                
                <el-divider>安全设置</el-divider>
                
                <el-form-item label="允许多设备登录">
                  <el-switch
                    v-model="settingsForm.allow_multi_device_login"
                    active-color="#13ce66"
                    inactive-color="#ff4949">
                  </el-switch>
                  <span class="form-help-text">开启后用户可以在多台设备同时登录，关闭后新登录会挤掉旧会话</span>
                </el-form-item>
                
                <el-divider>套餐价格设置</el-divider>
                
                <el-form-item label="月卡价格">
                  <el-input-number 
                    v-model="settingsForm.monthly_plan_price" 
                    :min="0" 
                    :precision="1" 
                    :step="0.1"
                    style="width: 200px">
                  </el-input-number>
                  <span class="form-help-text">元（使用期限30天）</span>
                </el-form-item>
                
                <el-form-item label="季卡价格">
                  <el-input-number 
                    v-model="settingsForm.quarterly_plan_price" 
                    :min="0" 
                    :precision="1" 
                    :step="0.1"
                    style="width: 200px">
                  </el-input-number>
                  <span class="form-help-text">元（使用期限90天）</span>
                </el-form-item>
                
                <el-form-item label="年卡价格">
                  <el-input-number 
                    v-model="settingsForm.yearly_plan_price" 
                    :min="0" 
                    :precision="1" 
                    :step="0.1"
                    style="width: 200px">
                  </el-input-number>
                  <span class="form-help-text">元（使用期限365天）</span>
                </el-form-item>
                
                <el-form-item>
                  <el-button type="primary" @click="saveSettings" :loading="savingSettings">保存设置</el-button>
                </el-form-item>
              </el-form>
            </el-card>
          </div>
          
          <!-- 支付设置 -->
          <div v-if="activeMenu === 'payment'">
            <el-card>
              <template #header>
                <div class="card-header">
                  <span>支付方式设置</span>
                </div>
              </template>
              
              <el-table :data="paymentMethods" style="width: 100%" v-loading="loadingPaymentMethods">
                <el-table-column prop="id" label="ID" width="60"></el-table-column>
                <el-table-column prop="name" label="支付方式名称"></el-table-column>
                <el-table-column prop="code" label="代码"></el-table-column>
                <el-table-column prop="description" label="描述"></el-table-column>
                <el-table-column label="状态" width="100">
                  <template #default="scope">
                    <el-switch
                      v-model="scope.row.is_enabled"
                      @change="togglePaymentMethod(scope.row)"
                      active-color="#13ce66"
                      inactive-color="#ff4949">
                    </el-switch>
                  </template>
                </el-table-column>
                <el-table-column prop="sort_order" label="排序"></el-table-column>
              </el-table>
            </el-card>
            
            <el-card style="margin-top: 20px">
              <template #header>
                <div class="card-header">
                  <span>支付网关设置</span>
                </div>
              </template>
              
              <el-form label-width="180px" :model="paymentSettings" ref="paymentSettingsForm">
                <el-form-item label="支付网关地址" prop="payment_gateway_url">
                  <el-input v-model="paymentSettings.payment_gateway_url" placeholder="例如：https://pay.msblog.cc/submit.php" style="width: 400px"></el-input>
                </el-form-item>
                
                <el-form-item>
                  <el-button type="primary" @click="savePaymentSettings" :loading="savingPaymentSettings">保存支付设置</el-button>
                </el-form-item>
              </el-form>
            </el-card>
          </div>
        </el-main>
      </el-container>
    </el-container>
    
    <!-- 修改用户到期时间对话框 -->
    <el-dialog title="修改到期时间" v-model="expireTimeDialogVisible" width="400px">
      <el-form :model="expireTimeForm">
        <el-form-item label="用户" label-width="80px">
          <span>{{ selectedUser ? selectedUser.name : '' }}</span>
        </el-form-item>
        <el-form-item label="到期时间" label-width="80px">
          <el-date-picker
            v-model="expireTimeForm.expire_time"
            type="datetime"
            placeholder="选择日期时间"
            format="YYYY-MM-DD HH:mm:ss">
          </el-date-picker>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="expireTimeDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="updateUserExpireTime" :loading="updatingExpireTime">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 修改用户密码对话框 -->
    <el-dialog title="修改用户密码" v-model="passwordDialogVisible" width="400px">
      <el-form :model="passwordForm" :rules="passwordRules" ref="passwordFormRef">
        <el-form-item label="用户" label-width="80px">
          <span>{{ selectedUser ? selectedUser.name : '' }}</span>
        </el-form-item>
        <el-form-item label="新密码" label-width="80px" prop="password">
          <el-input
            v-model="passwordForm.password"
            type="password"
            show-password
            placeholder="请输入新密码">
          </el-input>
        </el-form-item>
        <el-form-item label="确认密码" label-width="80px" prop="confirmPassword">
          <el-input
            v-model="passwordForm.confirmPassword"
            type="password"
            show-password
            placeholder="请再次输入新密码">
          </el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="passwordDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="updateUserPassword" :loading="updatingPassword">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 添加分销对话框 -->
    <el-dialog title="添加分销" v-model="addDistributorDialogVisible" width="500px">
      <el-form :model="addDistributorForm" :rules="addDistributorRules" ref="addDistributorForm" label-width="100px">
        <el-form-item label="用户名" prop="name">
          <el-input v-model="addDistributorForm.name" placeholder="请输入用户名"></el-input>
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="addDistributorForm.email" placeholder="请输入邮箱"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input v-model="addDistributorForm.password" type="password" show-password placeholder="请输入密码"></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="confirmPassword">
          <el-input v-model="addDistributorForm.confirmPassword" type="password" show-password placeholder="请再次输入密码"></el-input>
        </el-form-item>
        <el-form-item label="分销昵称" prop="nickname">
          <el-input v-model="addDistributorForm.nickname" placeholder="请输入分销昵称"></el-input>
        </el-form-item>
        <el-form-item label="商户ID" prop="merchant_id">
          <el-input v-model.number="addDistributorForm.merchant_id" placeholder="请输入商户ID"></el-input>
        </el-form-item>
        <el-form-item label="商户密钥" prop="merchant_key">
          <el-input v-model="addDistributorForm.merchant_key" placeholder="请输入商户密钥"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="addDistributorDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="addDistributor" :loading="addingDistributor">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 编辑分销商对话框 -->
    <el-dialog title="编辑分销商信息" v-model="editDistributorDialogVisible" width="500px">
      <el-form :model="editDistributorForm" :rules="editDistributorRules" ref="editDistributorFormRef" label-width="100px">
        <el-form-item label="用户名" prop="user_name">
          <el-input v-model="editDistributorForm.user_name"></el-input>
        </el-form-item>
        <el-form-item label="分销昵称" prop="nickname">
          <el-input v-model="editDistributorForm.nickname"></el-input>
        </el-form-item>
        <el-form-item label="商户ID" prop="merchant_id">
          <el-input v-model.number="editDistributorForm.merchant_id"></el-input>
        </el-form-item>
        <el-form-item label="商户密钥" prop="merchant_key">
          <el-input v-model="editDistributorForm.merchant_key"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input v-model="editDistributorForm.password" type="password" show-password placeholder="不修改请留空"></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="confirmPassword">
          <el-input v-model="editDistributorForm.confirmPassword" type="password" show-password placeholder="不修改请留空"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editDistributorDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="updateDistributor" :loading="updatingDistributor">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import axios from 'axios'
import ApiEndpoint from '../components/ApiEndpoint.vue'

export default {
  name: 'Admin',
  components: {
    ApiEndpoint
  },
  data() {
    return {
      user: null,
      activeMenu: 'users',
      
      // 用户管理相关
      users: [],
      loadingUsers: false,
      userKeyword: '',
      userPagination: {
        current_page: 1,
        per_page: 10,
        total: 0
      },
      
      // 分销商相关
      distributors: [],
      loadingDistributors: false,
      distributorKeyword: '',
      distributorPagination: {
        current_page: 1,
        per_page: 10,
        total: 0
      },
      
      // 添加分销商
      addDistributorDialog: false,
      searchUserKeyword: '',
      searchingUsers: false,
      searchUserResults: [],
      newDistributor: {
        user_id: null,
        merchant_id: '',
        nickname: '',
        password: ''
      },
      addDistributorForm: {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        nickname: '',
        merchant_id: null,
        merchant_key: ''
      },
      selectedSearchUser: null,
      addingDistributor: false,
      
      settingsForm: {
        trial_minutes: 60,
        monthly_plan_price: 39.9,
        quarterly_plan_price: 99.9,
        yearly_plan_price: 299.9,
        allow_multi_device_login: false
      },
      savingSettings: false,
      
      // 支付方式相关
      paymentMethods: [],
      loadingPaymentMethods: false,
      paymentSettings: {
        payment_gateway_url: '',
      },
      savingPaymentSettings: false,
      
      // 表单相关
      selectedUser: null,
      expireTimeDialogVisible: false,
      expireTimeForm: {
        expire_time: null
      },
      updatingExpireTime: false,
      
      addDistributorDialogVisible: false,
      addDistributorRules: {
        name: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
        email: [
          { required: true, message: '请输入邮箱', trigger: 'blur' },
          { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
        ],
        password: [
          { required: true, message: '请输入密码', trigger: 'blur' },
          { min: 6, message: '密码长度不能少于6个字符', trigger: 'blur' }
        ],
        confirmPassword: [
          { required: true, message: '请再次输入密码', trigger: 'blur' },
          { 
            validator: (rule, value, callback) => {
              if (value !== this.addDistributorForm.password) {
                callback(new Error('两次输入的密码不一致'));
              } else {
                callback();
              }
            },
            trigger: 'blur'
          }
        ],
        nickname: [{ required: true, message: '请输入分销昵称', trigger: 'blur' }],
        merchant_id: [
          { required: true, message: '请输入商户ID', trigger: 'blur' },
          { type: 'number', message: '商户ID必须为数字', trigger: 'blur' }
        ],
        merchant_key: [{ required: true, message: '请输入商户密钥', trigger: 'blur' }]
      },
      addingDistributor: false,
      
      // 密码修改相关
      passwordDialogVisible: false,
      passwordForm: {
        password: '',
        confirmPassword: ''
      },
      passwordRules: {
        password: [
          { required: true, message: '请输入新密码', trigger: 'blur' },
          { min: 6, message: '密码长度不能少于6个字符', trigger: 'blur' }
        ],
        confirmPassword: [
          { required: true, message: '请再次输入新密码', trigger: 'blur' },
          { 
            validator: (rule, value, callback) => {
              if (value !== this.passwordForm.password) {
                callback(new Error('两次输入的密码不一致'))
              } else {
                callback()
              }
            }, 
            trigger: 'blur' 
          }
        ]
      },
      updatingPassword: false,
      
      // 编辑分销商相关
      editDistributorDialogVisible: false,
      editDistributorForm: {
        id: null,
        user_name: '',
        nickname: '',
        merchant_id: null,
        merchant_key: '',
        password: '',
        confirmPassword: ''
      },
      editDistributorRules: {
        user_name: [
          { required: true, message: '请输入用户名', trigger: 'blur' },
          { min: 2, max: 20, message: '用户名长度应在2-20个字符之间', trigger: 'blur' }
        ],
        nickname: [
          { required: true, message: '请输入分销昵称', trigger: 'blur' }
        ],
        merchant_id: [
          { required: true, message: '请输入商户ID', trigger: 'blur' },
          { type: 'number', message: '商户ID必须为数字', trigger: 'blur' }
        ],
        merchant_key: [
          { required: true, message: '请输入商户密钥', trigger: 'blur' }
        ],
        confirmPassword: [
          { 
            validator: (rule, value, callback) => {
              if (this.editDistributorForm.password && value !== this.editDistributorForm.password) {
                callback(new Error('两次输入的密码不一致'))
              } else {
                callback()
              }
            }, 
            trigger: 'blur' 
          }
        ]
      },
      updatingDistributor: false
    }
  },
  computed: {
    user() {
      return this.$store.getters.user;
    }
  },
  methods: {
    // 菜单选择
    handleMenuSelect(key) {
      if (key === 'logout') {
        // 对于logout项，直接调用logout方法，不改变activeMenu
        this.logout();
        return;
      }
      
      this.activeMenu = key
      
      if (key === 'users') {
        this.fetchUsers()
      } else if (key === 'distributors') {
        this.fetchDistributors()
      } else if (key === 'settings') {
        this.fetchSettings()
      } else if (key === 'api-docs') {
        // API文档不需要额外加载数据
      } else if (key === 'payment') {
        this.fetchPaymentMethods()
        this.fetchPaymentSettings()
      }
    },
    
    // 判断是否过期
    isExpired(expireTime) {
      if (!expireTime) return true
      return new Date(expireTime) < new Date()
    },
    
    // 格式化日期时间
    formatDateTime(dateTime) {
      if (!dateTime) return '未设置'
      return new Date(dateTime).toLocaleString()
    },
    
    // 获取用户列表
    async fetchUsers() {
      this.loadingUsers = true
      
      try {
        const params = {
          page: this.userPagination.current_page
        }
        
        if (this.userKeyword) {
          params.keyword = this.userKeyword
        }
        
        const response = await axios.get('/admin/users', { params })
        
        if (response.data.status) {
          this.users = response.data.data.users.data
          this.userPagination.total = response.data.data.users.total
          this.userPagination.per_page = response.data.data.users.per_page
          this.userPagination.current_page = response.data.data.users.current_page
        }
      } catch (error) {
        console.error('获取用户列表失败', error)
        ElMessage.error('获取用户列表失败')
      } finally {
        this.loadingUsers = false
      }
    },
    
    // 用户分页
    handleUserPageChange(page) {
      this.userPagination.current_page = page
      this.fetchUsers()
    },
    
    // 修改用户到期时间
    editUserExpireTime(user) {
      this.selectedUser = user
      this.expireTimeForm.expire_time = user.expire_time ? new Date(user.expire_time) : new Date()
      this.expireTimeDialogVisible = true
    },
    
    // 更新用户到期时间
    async updateUserExpireTime() {
      if (!this.selectedUser) return
      
      this.updatingExpireTime = true
      
      try {
        const response = await axios.patch(`/admin/users/${this.selectedUser.id}/expire-time`, {
          expire_time: this.expireTimeForm.expire_time
        })
        
        if (response.data.status) {
          ElMessage.success('更新成功')
          this.expireTimeDialogVisible = false
          
          // 更新列表中的数据
          const index = this.users.findIndex(u => u.id === this.selectedUser.id)
          if (index !== -1) {
            this.users[index].expire_time = this.expireTimeForm.expire_time
          }
        }
      } catch (error) {
        ElMessage.error('更新失败')
      } finally {
        this.updatingExpireTime = false
      }
    },
    
    // 获取分销列表
    async fetchDistributors() {
      this.loadingDistributors = true
      
      try {
        const params = {
          page: this.distributorPagination.current_page
        }
        
        if (this.distributorKeyword) {
          params.keyword = this.distributorKeyword
        }
        
        const response = await axios.get('/admin/distributors', { params })
        
        if (response.data.status) {
          this.distributors = response.data.data.distributors.data
          this.distributorPagination.total = response.data.data.distributors.total
          this.distributorPagination.per_page = response.data.data.distributors.per_page
          this.distributorPagination.current_page = response.data.data.distributors.current_page
        }
      } catch (error) {
        console.error('获取分销商列表失败', error)
        ElMessage.error('获取分销商列表失败')
      } finally {
        this.loadingDistributors = false
      }
    },
    
    // 分销分页
    handleDistributorPageChange(page) {
      this.distributorPagination.current_page = page
      this.fetchDistributors()
    },
    
    // 切换分销商状态
    async toggleDistributorStatus(distributor) {
      try {
        const response = await axios.patch(`/admin/distributors/${distributor.id}/status`, {
          status: !distributor.status
        })
        
        if (response.data.status) {
          ElMessage.success(distributor.status ? '已禁用' : '已启用')
          
          // 更新列表中的数据
          const index = this.distributors.findIndex(d => d.id === distributor.id)
          if (index !== -1) {
            this.distributors[index].status = !distributor.status
          }
        }
      } catch (error) {
        ElMessage.error('操作失败')
      }
    },
    
    // 删除分销商
    async deleteDistributor(distributor) {
      try {
        const confirmResult = await this.$confirm(
          `确定要删除分销商 ${distributor.nickname} 吗？`,
          '删除确认',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )
        
        if (confirmResult) {
          const response = await axios.delete(`/admin/distributors/${distributor.id}`)
          
          if (response.data.status) {
            ElMessage.success('删除成功')
            this.fetchDistributors()
          }
        }
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('删除失败')
        }
      }
    },
    
    // 显示添加分销商对话框
    showAddDistributorDialog() {
      this.addDistributorForm.name = ''
      this.addDistributorForm.email = ''
      this.addDistributorForm.password = ''
      this.addDistributorForm.confirmPassword = ''
      this.addDistributorForm.nickname = ''
      this.addDistributorForm.merchant_id = null
      this.addDistributorForm.merchant_key = ''
      this.addDistributorDialogVisible = true
    },
    
    // 添加分销商
    async addDistributor() {
      this.$refs.addDistributorForm.validate(async (valid) => {
        if (valid) {
          // 检查两次密码是否一致
          if (this.addDistributorForm.password !== this.addDistributorForm.confirmPassword) {
            ElMessage.error('两次输入的密码不一致');
            return;
          }
          
          this.addingDistributor = true;
          
          try {
            const formData = {
              name: this.addDistributorForm.name,
              email: this.addDistributorForm.email,
              password: this.addDistributorForm.password,
              nickname: this.addDistributorForm.nickname,
              merchant_id: this.addDistributorForm.merchant_id,
              merchant_key: this.addDistributorForm.merchant_key
            };
            
            const response = await axios.post('/admin/distributors', formData);
            
            if (response.data.status) {
              ElMessage.success('添加成功');
              this.addDistributorDialogVisible = false;
              this.fetchDistributors();
            }
          } catch (error) {
            if (error.response && error.response.data.message) {
              ElMessage.error(error.response.data.message);
            } else {
              ElMessage.error('添加失败');
            }
          } finally {
            this.addingDistributor = false;
          }
        }
      });
    },
    
    // 获取系统设置
    async fetchSettings() {
      try {
        const response = await axios.get('/admin/settings')
        
        if (response.data.status) {
          this.settingsForm.trial_minutes = response.data.data.settings.trial_minutes
          this.settingsForm.monthly_plan_price = response.data.data.settings.monthly_plan_price
          this.settingsForm.quarterly_plan_price = response.data.data.settings.quarterly_plan_price
          this.settingsForm.yearly_plan_price = response.data.data.settings.yearly_plan_price
          this.settingsForm.allow_multi_device_login = response.data.data.settings.allow_multi_device_login
        }
      } catch (error) {
        ElMessage.error('获取设置失败')
      }
    },
    
    // 保存系统设置
    async saveSettings() {
      this.savingSettings = true
      
      try {
        const response = await axios.patch('/admin/settings', this.settingsForm)
        
        if (response.data.status) {
          ElMessage.success('保存成功')
        }
      } catch (error) {
        ElMessage.error('保存失败')
      } finally {
        this.savingSettings = false
      }
    },
    
    // 退出登录
    async logout() {
      try {
        await this.$store.dispatch('logout')
        this.$router.push('/login')
      } catch (error) {
        ElMessage.error('退出失败')
      }
    },
    
    // 修改用户密码
    editUserPassword(user) {
      this.selectedUser = user
      this.passwordForm.password = ''
      this.passwordForm.confirmPassword = ''
      this.passwordDialogVisible = true
      if (this.$refs.passwordFormRef) {
        this.$refs.passwordFormRef.resetFields()
      }
    },
    
    // 更新用户密码
    async updateUserPassword() {
      if (!this.selectedUser) return
      
      this.$refs.passwordFormRef.validate(async (valid) => {
        if (valid) {
          this.updatingPassword = true
          
          try {
            const response = await axios.patch(`/admin/users/${this.selectedUser.id}/password`, {
              password: this.passwordForm.password
            })
            
            if (response.data.status) {
              ElMessage.success('密码修改成功')
              this.passwordDialogVisible = false
            }
          } catch (error) {
            if (error.response && error.response.data.message) {
              ElMessage.error(error.response.data.message)
            } else {
              ElMessage.error('密码修改失败')
            }
          } finally {
            this.updatingPassword = false
          }
        }
      })
    },
    
    // 编辑分销商
    editDistributor(distributor) {
      this.editDistributorForm = {
        id: distributor.id,
        user_name: distributor.user ? distributor.user.name : '',
        nickname: distributor.nickname,
        merchant_id: distributor.merchant_id,
        merchant_key: distributor.merchant_key,
        password: '',
        confirmPassword: ''
      }
      this.editDistributorDialogVisible = true
      
      // 重置表单验证
      if (this.$refs.editDistributorFormRef) {
        this.$refs.editDistributorFormRef.resetFields()
      }
    },
    
    // 更新分销商信息
    async updateDistributor() {
      if (!this.editDistributorForm.id) return
      
      this.$refs.editDistributorFormRef.validate(async (valid) => {
        if (valid) {
          this.updatingDistributor = true
          
          try {
            const data = {
              user_name: this.editDistributorForm.user_name,
              nickname: this.editDistributorForm.nickname,
              merchant_id: this.editDistributorForm.merchant_id,
              merchant_key: this.editDistributorForm.merchant_key
            }
            
            // 如果填写了密码，则一并更新
            if (this.editDistributorForm.password) {
              data.password = this.editDistributorForm.password
            }
            
            const response = await axios.patch(`/admin/distributors/${this.editDistributorForm.id}`, data)
            
            if (response.data.status) {
              ElMessage.success('更新成功')
              this.editDistributorDialogVisible = false
              this.fetchDistributors() // 刷新列表
            }
          } catch (error) {
            if (error.response && error.response.data.message) {
              ElMessage.error(error.response.data.message)
            } else {
              ElMessage.error('更新失败')
            }
          } finally {
            this.updatingDistributor = false
          }
        }
      })
    },
    
    /**
     * 获取支付方式列表
     */
    async fetchPaymentMethods() {
      this.loadingPaymentMethods = true
      
      try {
        const response = await axios.get('/admin/payment/methods')
        
        if (response.data.status) {
          this.paymentMethods = response.data.data.methods
        }
      } catch (error) {
        ElMessage.error('获取支付方式失败')
      } finally {
        this.loadingPaymentMethods = false
      }
    },
    
    /**
     * 获取支付设置
     */
    async fetchPaymentSettings() {
      try {
        const response = await axios.get('/admin/payment/settings')
        
        if (response.data.status) {
          this.paymentSettings = response.data.data.settings
        }
      } catch (error) {
        ElMessage.error('获取支付设置失败')
      }
    },
    
    /**
     * 切换支付方式状态
     */
    async togglePaymentMethod(method) {
      try {
        await axios.patch(`/admin/payment/methods/${method.id}/status`, {
          is_enabled: method.is_enabled
        })
        
        ElMessage.success(`${method.is_enabled ? '启用' : '禁用'}支付方式成功`)
      } catch (error) {
        method.is_enabled = !method.is_enabled // 恢复原来的状态
        ElMessage.error('操作失败')
      }
    },
    
    /**
     * 保存支付设置
     */
    async savePaymentSettings() {
      this.savingPaymentSettings = true
      
      try {
        const response = await axios.patch('/admin/payment/settings', {
          payment_gateway_url: this.paymentSettings.payment_gateway_url
        })
        
        if (response.data.status) {
          ElMessage.success('保存支付设置成功')
        }
      } catch (error) {
        if (error.response && error.response.data.message) {
          ElMessage.error(error.response.data.message)
        } else {
          ElMessage.error('保存支付设置失败')
        }
      } finally {
        this.savingPaymentSettings = false
      }
    }
  },
  mounted() {
    // 初始加载数据
    this.fetchUsers()
  }
}
</script>

<style scoped>
.admin {
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
  margin-right: 15px;
  color: #fff;
  font-weight: bold;
}

.el-aside {
  background-color: #fff;
  border-right: 1px solid #e6e6e6;
}

.el-menu-vertical {
  height: calc(100vh - 60px);
  border-right: none;
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

.pagination-container {
  margin-top: 20px;
  text-align: center;
}

.expired {
  color: #F56C6C;
}

.sub-text {
  font-size: 12px;
  color: #909399;
}

.form-help-text {
  margin-left: 10px;
  color: #909399;
}

.menu-divider {
  height: 1px;
  background-color: #e6e6e6;
  margin: 5px 0;
}

.logout-menu-item {
  color: #F56C6C !important;
  font-weight: bold;
}

.logout-menu-item i {
  color: #F56C6C !important;
}

.logout-section {
  margin-top: 20px;
  text-align: center;
}

.logout-section h4 {
  margin-bottom: 10px;
}

.logout-section p {
  margin-bottom: 10px;
}
</style> 