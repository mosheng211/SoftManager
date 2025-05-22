# 软件管理系统 (SoftManager)

<div align="center">

![版本](https://img.shields.io/badge/版本-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-v7.4+-green.svg)
![Laravel](https://img.shields.io/badge/Laravel-v10.x-red.svg)
![Vue](https://img.shields.io/badge/Vue-v3.x-brightgreen.svg)

</div>

<p align="center">一个基于Laravel和Vue的全功能软件管理系统，包含用户认证、权限管理、分销系统、多套餐订阅、聚合支付等功能。</p>

## 📑 目录

- [功能特点](#-功能特点)
- [系统功能详解](#-系统功能详解)
- [技术栈](#-技术栈)
- [环境要求](#-环境要求)
- [项目结构](#-项目结构)
- [核心数据表](#-核心数据表)
- [安装步骤](#-安装步骤)
- [使用说明](#-使用说明)
- [测试账号](#-测试账号)
- [注意事项](#-注意事项)
- [常见问题](#-常见问题)
- [系统界面截图](#-系统界面截图)
- [支持项目](#-支持项目)
- [联系方式](#-联系方式)
- [许可证](#-许可证)

## 🚀 功能特点

### 🔐 用户管理
- 用户注册与登录
- 权限管理（管理员、分销商、普通用户）
- 多设备登录控制（可在后台开启/关闭）
- 用户列表管理（包含搜索、修改到期时间、密码重置等功能）

### 🔗 分销系统
- 分销商管理（添加、编辑、删除、禁用/启用）
- 邀请用户系统
- 分销商收益跟踪
- 商户ID和密钥管理

### 💳 支付系统
- 聚合支付功能（支持多种支付方式）
- 支付设置（支付网关地址配置）
- 支付方式管理（启用/禁用不同支付渠道）

### ⚙️ 系统设置
- 套餐价格设置（月卡、季卡、年卡）
- 新用户默认试用时间
- 多设备登录控制
- 其他系统参数配置

### 📝 API文档
- 内置API文档展示
- API接口测试工具

## 📚 系统功能详解

### 🔗 分销系统详解

<details>
<summary>点击展开详情</summary>

这是一个多层级的营销模式实现，主要逻辑包括：

1. **用户关系链**：系统建立了分销商和普通用户之间的邀请关系，通过`distributor_user`表记录这种关系。

2. **分销商机制**：
   - 管理员通过后台手动添加分销商账户
   - 分销商拥有独立的"商户ID"和"商户密钥"，这是连接支付系统的关键
   - 分销商可以生成专属邀请链接，邀请新用户注册

3. **佣金追踪**：
   - 系统会记录每个分销商邀请的用户
   - 当被邀请用户进行充值或购买套餐时，交易会与邀请人关联

4. **分销管理**：
   - 管理后台提供了完整的分销商管理功能
   - 可以添加、编辑、禁用分销商
   - 可以查看分销商的邀请记录和收益情况
</details>

### 💳 支付系统详解

<details>
<summary>点击展开详情</summary>

这是一个聚合支付解决方案，有以下核心逻辑：

1. **聚合支付网关**：
   - 系统连接到外部聚合支付服务
   - 支持多种支付方式（支付宝、微信支付等）
   - 管理员可以配置支付网关地址

2. **商户信息传递**：
   - 支付时使用的不是平台统一的商户信息，而是用户的邀请人（分销商）的商户ID和密钥
   - 这意味着每笔交易都通过邀请人的支付渠道处理

3. **套餐管理**：
   - 系统提供了多种套餐（月卡、季卡、年卡）
   - 管理员可以在后台灵活配置套餐价格

4. **支付流程**：
   - 用户选择套餐和支付方式
   - 系统获取用户的邀请人(分销商)商户信息
   - 创建支付订单，生成支付链接
   - 完成支付后，系统自动更新用户账户到期时间
</details>

### 📝 注册系统详解

<details>
<summary>点击展开详情</summary>

注册系统与分销系统紧密关联，具有以下特点：

1. **基础注册流程**：
   - 用户通过填写常规信息（用户名、邮箱、密码）进行注册
   - 系统会为新用户分配一个默认试用期（可在管理后台配置时长）
   - 注册成功后，用户获得基础权限，可访问系统功能

2. **邀请机制**：
   - 注册页面支持通过URL参数`inviter`接收邀请人ID
   - 例如：`http://localhost:8080/register?inviter=1`中的`1`就是分销商ID
   - 当用户通过此类链接注册时，系统自动记录邀请关系

3. **试用期管理**：
   - 新注册用户获得一个可配置的试用期
   - 试用期结束后，用户需要充值购买套餐以继续使用
</details>

### 🔄 系统整合与商业闭环

<details>
<summary>点击展开详情</summary>

1. **注册-分销-支付闭环**：
   - 用户通过分销商邀请链接注册
   - 系统记录用户与分销商的关联关系
   - 用户购买套餐时使用关联分销商的支付渠道
   - 形成完整的邀请 → 注册 → 试用 → 购买 → 分成商业闭环

2. **去中心化支付**：
   - 每个分销商拥有独立支付通道，不依赖平台统一支付
   - 分销商直接通过自己的支付渠道收款，简化了分账流程

3. **多方共赢机制**：
   - 分销商有强烈动机邀请用户，直接获得收益
   - 平台通过分销网络实现用户快速增长
   - 用户获得试用期和高质量服务
</details>

## 💻 技术栈

### 后端
- Laravel 10.x：PHP框架
- Laravel Sanctum：API认证系统
- MySQL：数据库
- Composer：依赖管理

### 前端
- Vue.js 3.x：前端框架
- Element Plus：UI组件库
- Axios：HTTP客户端
- Vuex：状态管理
- Vue Router：前端路由

## 📋 环境要求

- PHP >= 7.4
- Composer
- MySQL >= 5.7
- Node.js >= 12.0
- npm或yarn

## 📁 项目结构

```
JdLaravel/
│
├── backend/                     # Laravel 后端
│   ├── app/                     # 应用核心代码
│   │   ├── Http/                
│   │   │   ├── Controllers/     # 控制器
│   │   │   │   ├── API/         # API控制器
│   │   │   │   │   ├── Admin/   # 管理员API
│   │   │   │   │   └── ...      # 其他API
│   │   │   └── Middleware/      # 中间件（包含认证、角色检查、单设备登录等）
│   │   ├── Models/              # 数据模型
│   │   └── Services/            # 服务层（如PaymentService）
│   ├── config/                  # 配置文件
│   ├── database/                # 数据库相关
│   │   ├── migrations/          # 数据库迁移
│   │   └── seeds/               # 数据库种子
│   ├── routes/                  # 路由定义
│   │   └── api.php              # API路由
│   └── ...
│
├── frontend/                    # Vue 前端
│   ├── src/                     # 源代码
│   │   ├── components/          # Vue组件
│   │   │   └── ApiEndpoint.vue  # API文档组件
│   │   ├── views/               # 页面视图
│   │   │   ├── Admin.vue        # 管理后台
│   │   │   ├── Login.vue        # 登录页面
│   │   │   └── UserCenter.vue   # 用户中心
│   │   ├── router/              # 路由配置
│   │   └── store/               # Vuex状态管理
│   └── ...
│
└── docs/                        # 项目文档
```

## 💾 核心数据表

| 表名 | 描述 |
|------|------|
| users | 用户表 |
| distributors | 分销商表 |
| distributor_user | 分销关系表 |
| payment_methods | 支付方式表 |
| recharge_records | 充值记录表 |
| settings | 系统设置表 |
| personal_access_tokens | API认证令牌表 |

## 🔧 安装步骤

### 后端设置

1. 克隆仓库并进入后端目录：
```bash
git clone https://github.com/yourusername/softmanager.git
cd softmanager/backend
```

2. 安装依赖：
```bash
composer install
```

3. 配置环境：
```bash
copy .env.example .env
php artisan key:generate
```

4. 配置数据库（在.env文件中）：
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=softmanager
DB_USERNAME=root
DB_PASSWORD=你的密码
```

5. 创建数据库：
```sql
CREATE DATABASE `softmanager` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. 数据库迁移和填充初始数据：
```bash
php artisan migrate
php artisan db:seed
```

7. 创建初始管理员账户：
```bash
php artisan tinker
User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin']);
```

8. 启动开发服务器：
```bash
php artisan serve
```

### 前端设置

1. 进入前端目录：
```bash
cd ../frontend
```

2. 安装依赖：
```bash
npm install
```

3. 配置API地址（在.env文件中）：
```
VUE_APP_API_URL=http://localhost:8000/api
```

4. 启动开发服务器：
```bash
npm run serve
```

## 📘 使用说明

- 后端API地址：http://localhost:8000/api
- 前端地址：http://localhost:8080
- 登录管理后台：使用管理员账户登录
- 系统设置：在管理后台 -> 系统设置 中配置
- 支付设置：在管理后台 -> 支付设置 中配置
- 分销管理：在管理后台 -> 分销管理 中配置
- API文档：在管理后台 -> API文档 中查看

## 👤 测试账号

系统可以使用以下预设账号进行测试：

| 类型 | 邮箱 | 密码 |
|------|------|------|
| 管理员 | admin@example.com | password |
| 普通用户 | user@example.com | password |
| 分销商 | distributor1_1747911780@example.com | password123 |

## ⚠️ 注意事项

1. 前端和后端必须同时运行才能正常使用系统
2. 如需在本地进行注册测试，请使用邀请链接：http://localhost:8080/register?inviter=1

## ❓ 常见问题

<details>
<summary><b>跨域问题</b></summary>
如果遇到跨域问题，请检查后端的CORS配置是否正确，确保backend/config/cors.php中的allowed_origins包含了前端的地址。
</details>

<details>
<summary><b>数据库连接问题</b></summary>
确保MySQL服务已启动，并且.env文件中的数据库配置信息正确。
</details>

<details>
<summary><b>依赖安装问题</b></summary>
如果遇到依赖安装问题，可尝试以下命令：
```bash
composer install --ignore-platform-reqs
npm install --legacy-peer-deps
```
</details>

## 📷 系统界面截图

<details open>
<summary><b>注册页</b></summary>
<p align="center">
  <img src="https://img.msblog.cc/image-20250523002417171.png" alt="使用携带邀请码的注册页" width="80%">
  <br>
  <em>使用携带邀请码的注册页</em>
</p>
<p align="center">
  <img src="https://img.msblog.cc/image-20250523002427155.png" alt="没有携带邀请码的注册页" width="80%">
  <br>
  <em>没有携带邀请码的注册页</em>
</p>
</details>

<details>
<summary><b>个人中心</b></summary>
<p align="center">
  <img src="https://img.msblog.cc/image-20250522205849927.png" alt="个人中心界面" width="80%">
  <br>
  <em>个人中心界面</em>
</p>
</details>

<details>
<summary><b>分销后台</b></summary>
<p align="center">
  <img src="https://img.msblog.cc/image-20250522201334708.png" alt="分销商后台界面" width="80%">
  <br>
  <em>分销商后台界面</em>
</p>
</details>

<details>
<summary><b>管理后台</b></summary>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002121304.png" alt="用户管理界面" width="80%">
  <br>
  <em>用户管理界面</em>
</p>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002136879.png" alt="分销商列表" width="80%">
  <br>
  <em>分销商列表</em>
</p>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002157933.png" alt="添加分销商" width="80%">
  <br>
  <em>添加分销商</em>
</p>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002223105.png" alt="API文档界面" width="80%">
  <br>
  <em>API文档界面</em>
</p>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002235106.png" alt="系统设置界面" width="80%">
  <br>
  <em>系统设置界面</em>
</p>

<p align="center">
  <img src="https://img.msblog.cc/image-20250523002301005.png" alt="支付设置界面" width="80%">
  <br>
  <em>支付设置界面</em>
</p>
</details>

## 💖 支持项目

如果您觉得这个项目对您有所帮助，可以请作者喝杯咖啡，以示支持！

<div align="center">
  <table>
    <tr>
      <th align="center" style="width: 50%">
        <img src="https://img.msblog.cc/image-20250523012804344.png" alt="支付宝" width="70%">
        <p>支付宝</p>
      </th>
      <th align="center" style="width: 50%">
        <img src="https://img.msblog.cc/image-20250523012814243.png" alt="微信" width="70%">
        <p>微信</p>
      </th>
    </tr>
  </table>
</div>

## 📞 联系方式

如有问题或建议，欢迎通过以下方式联系我（请注明来意）：

- **QQ**: 1098901025
- **微信**: zhx_ms

## 📄 许可证

[MIT](./LICENSE) © 软件管理系统 