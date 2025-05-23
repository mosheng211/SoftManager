# Git提交步骤

以下是将更改提交到GitHub仓库的完整步骤：

## 一次性设置（如果尚未完成）

```bash
# 设置Git用户信息
git config --global user.name "你的GitHub用户名"
git config --global user.email "你的GitHub邮箱"

# 初始化Git仓库（如果项目尚未初始化）
git init

# 添加远程仓库（如果尚未添加）
git remote add origin https://github.com/你的用户名/软件管理系统.git
```

## 日常提交流程

```bash
# 1. 查看当前更改状态
git status

# 2. 添加更改的文件到暂存区
# 添加特定文件
git add README.md

# 或添加所有更改的文件
git add .

# 3. 提交更改
git commit -m "更新：添加支付测试截图到README.md"

# 4. 拉取远程仓库最新代码（避免冲突）
git pull origin main

# 5. 推送到远程仓库
# 如果是main分支
git push origin main

# 如果是master分支
git push origin master
```

## 常见问题解决

### 如果遇到冲突

```bash
# 解决冲突后，重新添加文件
git add .

# 继续合并过程
git commit

# 完成后推送
git push origin main
```

### 查看提交历史

```bash
# 查看简洁的提交历史
git log --oneline

# 查看详细的提交历史
git log
```

### 撤销最后一次提交（未推送）

```bash
# 撤销提交但保留更改
git reset --soft HEAD~1

# 撤销提交并丢弃更改
git reset --hard HEAD~1
```

## 分支操作（可选）

```bash
# 创建并切换到新分支
git checkout -b 新分支名

# 切换到已有分支
git checkout 分支名

# 合并分支到当前分支
git merge 来源分支名
```
