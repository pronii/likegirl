# 数据库备份增强功能实现计划

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 为数据库备份系统添加文件删除（单个/批量）和本地上传功能，并加强安全防护

**Architecture:** 创建两个新的后端接口（deleteBackup.php、uploadBackup.php）处理删除和上传请求，修改前端页面（backupManager.php）添加复选框、批量操作按钮和上传模态框。所有操作都包含严格的安全验证。

**Tech Stack:** PHP 7.4+, MySQL 5.7+, jQuery 3.x, Bootstrap, Toastr

---

## 文件结构

**新建文件:**
- `admin/deleteBackup.php` - 删除备份文件接口（单个/批量）
- `admin/uploadBackup.php` - 上传备份文件接口

**修改文件:**
- `admin/backupManager.php` - 前端页面，添加复选框、批量删除、上传功能

**测试文件:**
- 手动测试所有接口的功能和安全性

---

## Task 1: 创建删除备份文件接口

**Files:**
- Create: `admin/deleteBackup.php`

- [ ] **Step 1: 创建 deleteBackup.php 文件（第1部分：基础结构和会话验证）**

```php
<?php
session_start();
include_once 'connect.php';

// 验证登录会话
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 获取请求数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 验证参数
if (!isset($data['filenames']) || !is_array($data['filenames']) || empty($data['filenames'])) {
    die(json_encode(['success' => false, 'message' => '参数错误']));
}

$filenames = $data['filenames'];

// 限制批量删除数量
if (count($filenames) > 100) {
    die(json_encode(['success' => false, 'message' => '单次最多删除100个文件']));
}
```

- [ ] **Step 2: 添加文件删除逻辑和安全验证（第2部分）**

在上述代码后继续添加：

```php
// 备份目录
$backupDir = __DIR__ . '/../backups';
$backupDirReal = realpath($backupDir);

if ($backupDirReal === false) {
    die(json_encode(['success' => false, 'message' => '备份目录不存在']));
}

$deleted = 0;
$failed = 0;
$deletedFiles = [];

// 遍历删除文件
foreach ($filenames as $filename) {
    // 文件名白名单验证：只允许 backup_YYYY-MM-DD_HH-ii-ss.sql 格式
    if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
        $failed++;
        continue;
    }
    
    // 构建文件路径
    $filepath = $backupDir . '/' . $filename;
    
    // 路径规范化验证：确保文件在备份目录内
    $filepathReal = realpath($filepath);
    if ($filepathReal === false || strpos($filepathReal, $backupDirReal) !== 0) {
        $failed++;
        continue;
    }
    
    // 检查文件是否存在
    if (!file_exists($filepathReal)) {
        $failed++;
        continue;
    }
    
    // 删除文件
    if (unlink($filepathReal)) {
        $deleted++;
        $deletedFiles[] = $filename;
    } else {
        $failed++;
    }
}
```

- [ ] **Step 3: 添加操作日志和返回结果（第3部分）**

继续添加：

```php
// 记录操作日志到 warning 表
if ($deleted > 0) {
    $logContent = '';
    if ($deleted == 1 && count($filenames) == 1) {
        $logContent = '管理员删除备份文件: ' . $deletedFiles[0];
    } else {
        $logContent = '管理员批量删除备份文件: ' . $deleted . '个文件';
    }
    
    $logTime = date('Y-m-d H:i:s');
    $logStmt = $connect->prepare("INSERT INTO warning (Warr_content, Warr_time) VALUES (?, ?)");
    $logStmt->bind_param("ss", $logContent, $logTime);
    $logStmt->execute();
    $logStmt->close();
}

// 返回结果
if ($failed == 0) {
    echo json_encode([
        'success' => true,
        'message' => '成功删除 ' . $deleted . ' 个备份文件',
        'deleted' => $deleted
    ]);
} else if ($deleted > 0) {
    echo json_encode([
        'success' => true,
        'message' => '部分文件删除成功',
        'deleted' => $deleted,
        'failed' => $failed
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '删除失败',
        'failed' => $failed
    ]);
}
?>
```

- [ ] **Step 4: 测试删除接口**

使用浏览器开发者工具控制台测试：

```javascript
// 先登录后台，然后在控制台执行
fetch('deleteBackup.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({filenames: ['backup_2026-06-15_14-30-00.sql']})
})
.then(r => r.json())
.then(d => console.log(d));
```

预期输出：
- 如果未登录：`{success: false, message: "未授权"}`
- 如果文件名不合法：`{success: false, message: "删除失败", failed: 1}`
- 如果删除成功：`{success: true, message: "成功删除 1 个备份文件", deleted: 1}`

- [ ] **Step 5: 提交 deleteBackup.php**

```bash
git add admin/deleteBackup.php
git commit -m "feat: 添加备份文件删除接口（支持单个/批量删除）"
```

---

## Task 2: 创建上传备份文件接口

**Files:**
- Create: `admin/uploadBackup.php`

- [ ] **Step 1: 创建 uploadBackup.php 文件（第1部分：基础验证）**

```php
<?php
session_start();
include_once 'connect.php';

// 验证登录会话
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 验证文件上传
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = '文件上传失败';
    if (isset($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = '文件大小超过限制';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = '未选择文件';
                break;
        }
    }
    die(json_encode(['success' => false, 'message' => $errorMsg]));
}

$uploadedFile = $_FILES['file'];
$originalName = $uploadedFile['name'];
$tmpPath = $uploadedFile['tmp_name'];
$fileSize = $uploadedFile['size'];
```

- [ ] **Step 2: 添加文件验证逻辑（第2部分）**

继续添加：

```php
// 文件大小限制：50MB
$maxSize = 50 * 1024 * 1024;
if ($fileSize > $maxSize) {
    die(json_encode(['success' => false, 'message' => '文件大小超过限制（最大50MB）']));
}

// 验证文件扩展名
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if ($ext !== 'sql') {
    die(json_encode(['success' => false, 'message' => '文件格式不正确，仅支持 .sql 文件']));
}

// 验证 MIME 类型
$allowedMimes = ['text/plain', 'application/sql', 'application/x-sql', 'application/octet-stream'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    die(json_encode(['success' => false, 'message' => '文件类型不正确']));
}
```

- [ ] **Step 3: 添加SQL内容验证（第3部分）**

继续添加：

```php
// 读取文件前1KB，检查是否包含SQL关键字
$handle = fopen($tmpPath, 'r');
if ($handle === false) {
    die(json_encode(['success' => false, 'message' => '无法读取文件']));
}

$firstKB = fread($handle, 1024);
fclose($handle);

// 检查SQL关键字
$sqlKeywords = ['CREATE', 'INSERT', 'DROP', 'SET', 'TABLE', 'DATABASE', 'SELECT', 'UPDATE'];
$foundKeyword = false;
foreach ($sqlKeywords as $keyword) {
    if (stripos($firstKB, $keyword) !== false) {
        $foundKeyword = true;
        break;
    }
}

if (!$foundKeyword) {
    die(json_encode(['success' => false, 'message' => '文件内容不是有效的SQL文件']));
}
```

- [ ] **Step 4: 添加文件保存和路径验证（第4部分）**

继续添加：

```php
// 生成标准格式文件名
$newFilename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// 备份目录
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupDirReal = realpath($backupDir);
if ($backupDirReal === false) {
    die(json_encode(['success' => false, 'message' => '备份目录创建失败']));
}

// 目标路径
$targetPath = $backupDir . '/' . $newFilename;

// 移动上传文件
if (!move_uploaded_file($tmpPath, $targetPath)) {
    die(json_encode(['success' => false, 'message' => '文件保存失败']));
}

// 路径规范化验证：确保文件在备份目录内
$targetPathReal = realpath($targetPath);
if ($targetPathReal === false || strpos($targetPathReal, $backupDirReal) !== 0) {
    // 安全问题，删除文件
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    die(json_encode(['success' => false, 'message' => '路径验证失败']));
}
```

- [ ] **Step 5: 添加操作日志和返回结果（第5部分）**

继续添加：

```php
// 记录操作日志
$logContent = '管理员上传备份文件: ' . $newFilename . ' (' . round($fileSize / 1024 / 1024, 2) . ' MB)';
$logTime = date('Y-m-d H:i:s');
$logStmt = $connect->prepare("INSERT INTO warning (Warr_content, Warr_time) VALUES (?, ?)");
$logStmt->bind_param("ss", $logContent, $logTime);
$logStmt->execute();
$logStmt->close();

// 返回成功结果
echo json_encode([
    'success' => true,
    'message' => '上传成功',
    'filename' => $newFilename,
    'size' => $fileSize
]);
?>
```

- [ ] **Step 6: 测试上传接口**

创建一个简单的测试SQL文件用于测试：

```bash
echo "CREATE TABLE test (id INT);" > test_backup.sql
```

在浏览器登录后台后，使用开发者工具测试上传：

```javascript
// 方式1：通过表单测试（在后续前端页面完成后使用）
// 方式2：手动创建FormData测试
var formData = new FormData();
var file = document.createElement('input');
file.type = 'file';
file.accept = '.sql';
// 用户选择文件后
file.onchange = function() {
    formData.append('file', file.files[0]);
    fetch('uploadBackup.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(d => console.log(d));
};
file.click();
```

预期输出：
- 如果未登录：`{success: false, message: "未授权"}`
- 如果文件不是.sql：`{success: false, message: "文件格式不正确，仅支持 .sql 文件"}`
- 如果上传成功：`{success: true, message: "上传成功", filename: "backup_2026-06-15_16-30-00.sql", size: 12345}`

- [ ] **Step 7: 提交 uploadBackup.php**

```bash
git add admin/uploadBackup.php
git commit -m "feat: 添加备份文件上传接口（含安全验证）"
```

---

## Task 3: 修改前端页面 - 添加复选框和批量删除功能

**Files:**
- Modify: `admin/backupManager.php`

- [ ] **Step 1: 修改表格结构，添加复选框列**

找到表格的 `<thead>` 部分（约第27-33行），修改为：

```php
<thead>
    <tr>
        <th width="50"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
        <th>备份文件</th>
        <th>文件大小</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
</thead>
```

- [ ] **Step 2: 修改按钮区，添加上传和批量删除按钮**

找到"创建新备份"按钮（约第22行），修改为：

```php
<button type="button" class="btn btn-primary mb-3" onclick="createBackup()">
    <i class="mdi mdi-database-export"></i> 创建新备份
</button>
<button type="button" class="btn btn-success mb-3" onclick="showUploadModal()">
    <i class="mdi mdi-upload"></i> 上传备份
</button>
<button type="button" class="btn btn-danger mb-3" id="batchDeleteBtn" onclick="batchDeleteBackups()" disabled>
    <i class="mdi mdi-delete"></i> 批量删除
</button>
```

- [ ] **Step 3: 修改 loadBackups() 函数，生成带复选框的表格**

找到 `loadBackups()` 函数（约第50-76行），修改其中的 HTML 生成部分：

```javascript
function loadBackups() {
    $.get('backupList.php', function(res) {
        if (res.success) {
            let html = '';
            if (res.files.length === 0) {
                html = '<tr><td colspan="5" class="text-center">暂无备份</td></tr>';
            } else {
                res.files.forEach(file => {
                    html += `<tr>
                        <td><input type="checkbox" class="backup-checkbox" value="${file.name}" onchange="updateBatchButton()"></td>
                        <td>${file.name}</td>
                        <td>${(file.size / 1024).toFixed(2)} KB</td>
                        <td>${file.date}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="restoreBackup('${file.name}')" class="action-icon">
                                <i class="mdi mdi-database-import"></i> 恢复
                            </a>
                            <a href="downloadBackup.php?filename=${file.name}" class="action-icon ml-2">
                                <i class="mdi mdi-download"></i> 下载
                            </a>
                            <a href="javascript:void(0)" onclick="deleteBackup('${file.name}')" class="action-icon ml-2 text-danger">
                                <i class="mdi mdi-delete"></i> 删除
                            </a>
                        </td>
                    </tr>`;
                });
            }
            $('#backupList').html(html);
            updateBatchButton(); // 更新批量删除按钮状态
        }
    }, 'json');
}
```

- [ ] **Step 4: 添加全选/取消全选功能**

在 `<script>` 标签中的 `loadBackups()` 函数后添加：

```javascript
// 全选/取消全选
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.backup-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBatchButton();
}
```

- [ ] **Step 5: 添加批量删除按钮状态更新功能**

继续添加：

```javascript
// 更新批量删除按钮状态
function updateBatchButton() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    const btn = document.getElementById('batchDeleteBtn');
    const selectAll = document.getElementById('selectAll');
    
    if (checkboxes.length > 0) {
        btn.disabled = false;
        btn.innerHTML = `<i class="mdi mdi-delete"></i> 批量删除 (已选${checkboxes.length}项)`;
    } else {
        btn.disabled = true;
        btn.innerHTML = '<i class="mdi mdi-delete"></i> 批量删除';
    }
    
    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.backup-checkbox');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checkboxes.length === allCheckboxes.length;
    }
}
```

- [ ] **Step 6: 添加单个删除功能**

继续添加：

```javascript
// 单个删除
function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件 "' + filename + '" 吗？')) return;
    
    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: [filename]}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('删除失败');
        }
    });
}
```

- [ ] **Step 7: 添加批量删除功能**

继续添加：

```javascript
// 批量删除
function batchDeleteBackups() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    if (checkboxes.length === 0) {
        toastr.warning('请先选择要删除的文件');
        return;
    }
    
    const filenames = Array.from(checkboxes).map(cb => cb.value);
    const count = filenames.length;
    
    if (!confirm('确定要删除 ' + count + ' 个备份文件吗？\n此操作不可恢复！')) return;
    
    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: filenames}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                document.getElementById('selectAll').checked = false;
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('批量删除失败');
        }
    });
}
```

- [ ] **Step 8: 提交批量删除和复选框功能**

```bash
git add admin/backupManager.php
git commit -m "feat: 添加备份文件批量删除功能（含复选框和全选）"
```

---

## Task 4: 添加上传备份文件的前端功能

**Files:**
- Modify: `admin/backupManager.php`

- [ ] **Step 1: 在页面底部添加上传模态框**

在 `backupManager.php` 的 `<?php include_once 'Footer.php'; ?>` 之前添加：

```html
<!-- 上传备份文件模态框 -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">上传备份文件</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>选择文件</label>
                        <input type="file" class="form-control" id="backupFile" name="file" accept=".sql" required>
                        <small class="form-text text-muted">仅支持 .sql 格式，最大 50MB</small>
                    </div>
                    <div id="fileInfo" class="alert alert-info" style="display:none;">
                        <strong>已选文件：</strong><span id="fileName"></span><br>
                        <strong>文件大小：</strong><span id="fileSize"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="uploadBtn" onclick="uploadBackup()" disabled>上传</button>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: 添加显示上传模态框和文件选择监听函数**

在 JavaScript 区域添加：

```javascript
// 显示上传模态框
function showUploadModal() {
    $('#uploadModal').modal('show');
    document.getElementById('uploadForm').reset();
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
}

// 文件选择变化事件
$(function() {
    $('#backupFile').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#fileName').text(file.name);
            $('#fileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
            $('#fileInfo').show();
            
            if (file.size > 50 * 1024 * 1024) {
                toastr.error('文件大小不能超过50MB');
                $('#uploadBtn').prop('disabled', true);
            } else {
                $('#uploadBtn').prop('disabled', false);
            }
        } else {
            $('#fileInfo').hide();
            $('#uploadBtn').prop('disabled', true);
        }
    });
});
```

- [ ] **Step 3: 添加上传文件函数**

```javascript
// 上传备份文件
function uploadBackup() {
    const fileInput = document.getElementById('backupFile');
    const file = fileInput.files[0];
    
    if (!file) {
        toastr.error('请选择文件');
        return;
    }
    
    const uploadBtn = $('#uploadBtn');
    uploadBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> 上传中...');
    
    const formData = new FormData();
    formData.append('file', file);
    
    toastr.info('正在上传，请稍候...');
    
    $.ajax({
        url: 'uploadBackup.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                $('#uploadModal').modal('hide');
                loadBackups();
            } else {
                toastr.error(res.message);
            }
            uploadBtn.prop('disabled', false).html('上传');
        },
        error: function(xhr) {
            toastr.error('上传失败');
            uploadBtn.prop('disabled', false).html('上传');
        }
    });
}
```

- [ ] **Step 4: 提交上传功能**

```bash
git add admin/backupManager.php
git commit -m "feat: 添加备份文件上传前端功能（含模态框和文件预览）"
```

---

## Task 5: 综合测试

**Files:**
- Test: 完整功能和安全测试

- [ ] **Step 1: 功能测试 - 单个删除**

访问 `http://localhost/admin/backupManager.php`，点击删除按钮，验证文件被删除。

- [ ] **Step 2: 功能测试 - 批量删除**

选中多个复选框，点击批量删除，验证所有文件被删除。

- [ ] **Step 3: 功能测试 - 上传文件**

点击上传备份，选择一个有效的 .sql 文件，上传成功后验证文件出现在列表中。

- [ ] **Step 4: 安全测试 - 未登录访问**

退出登录，在控制台尝试访问接口，验证返回"未授权"。

- [ ] **Step 5: 安全测试 - 路径遍历**

在控制台执行恶意删除请求，验证被拦截。

- [ ] **Step 6: 查看操作日志**

在数据库查询 warning 表，验证所有操作都有日志记录。

- [ ] **Step 7: 最终提交**

```bash
git add -A
git commit -m "✅ 完成：数据库备份增强功能（删除、上传、安全防护）"
```

---

## 自审清单

✅ 所有规格需求已覆盖
✅ 无占位符
✅ 类型和命名一致
✅ 安全验证完整

