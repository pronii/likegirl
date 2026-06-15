# 视频上传和播放功能实施计划

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 在恋爱相册系统中添加视频上传和播放功能，实现图片和视频的统一管理

**Architecture:** 采用统一媒体表方案，扩展现有 `loveImg` 表支持视频类型。前端使用Canvas提取视频第一帧作为缩略图，后端保存视频文件和缩略图到独立目录。复用现有相册管理代码，最小化重复。

**Tech Stack:** PHP 7.4+, MySQL 5.7+, jQuery 3.x, HTML5 Video API, Canvas API, Bootstrap 4.x

---

## 文件结构规划

### 新增文件
- `uploads/videos/` - 视频文件存储目录
- `uploads/video_thumbs/` - 视频缩略图存储目录
- `admin/Function.php` - 添加工具函数 `formatDuration()`, `formatFileSize()`
- `Style/js/videoThumbnail.js` - 视频缩略图提取模块
- `Style/js/videoPlayer.js` - 视频播放器模块
- `Style/css/video.css` - 视频相关样式

### 修改文件
- 数据库表 `loveImg` - 添加媒体类型和视频字段
- `admin/ImgAddPost.php` - 扩展支持视频上传
- `admin/batchAddImgPost.php` - 扩展支持视频批量上传
- `admin/loveImgAdd.php` - 前端单张上传界面
- `admin/batchAddImg.php` - 前端批量上传界面
- `admin/loveImgSet.php` - 管理页面显示视频
- `admin/modImg.php` - 编辑页面支持视频
- `admin/delImg.php` - 删除视频和缩略图
- `getPhotos.php` - API返回视频字段
- `loveImg.php` - 前端展示页面
- `Style/js/loveAlbum/album.js` - 相册模块支持视频卡片
- `Style/css/loveImg.css` - 添加视频卡片样式

---

## Task 1: 数据库结构准备

**Files:**
- Modify: 数据库表 `loveImg`
- Create: `uploads/videos/`
- Create: `uploads/video_thumbs/`

- [ ] **Step 1: 备份数据库**

```bash
cd /d D:\phpStudy\PHPTutorial\WWW
mkdir -p backups
mysqldump -uroot -proot loveImg > backups/backup_before_video_$(date +%Y%m%d_%H%M%S).sql
```

Expected: 创建备份文件 `backups/backup_before_video_YYYYMMDD_HHMMSS.sql`

- [ ] **Step 2: 执行数据库表结构修改**

创建SQL脚本文件 `admin/db_migration_video.sql`:

```sql
-- 添加视频相关字段
ALTER TABLE `loveImg` 
ADD COLUMN `media_type` ENUM('image', 'video') DEFAULT 'image' 
    COMMENT '媒体类型' AFTER `id`,
ADD COLUMN `thumbnail_url` VARCHAR(500) DEFAULT NULL 
    COMMENT '视频缩略图URL' AFTER `imgUrl`,
ADD COLUMN `video_duration` INT DEFAULT NULL 
    COMMENT '视频时长(秒)' AFTER `thumbnail_url`,
ADD COLUMN `file_size` BIGINT DEFAULT NULL 
    COMMENT '文件大小(字节)' AFTER `video_duration`;

-- 将现有数据标记为image类型
UPDATE `loveImg` 
SET `media_type` = 'image' 
WHERE `media_type` IS NULL OR `media_type` = '';

-- 添加索引优化查询
ALTER TABLE `loveImg` ADD INDEX `idx_media_type` (`media_type`);
```

- [ ] **Step 3: 执行SQL迁移脚本**

```bash
mysql -uroot -proot loveImg < admin/db_migration_video.sql
```

Expected: 表结构修改成功，输出无错误信息

- [ ] **Step 4: 验证表结构**

```bash
mysql -uroot -proot -e "USE loveImg; DESCRIBE loveImg;"
```

Expected: 显示新增字段 `media_type`, `thumbnail_url`, `video_duration`, `file_size`

- [ ] **Step 5: 创建视频存储目录**

```bash
mkdir -p uploads/videos
mkdir -p uploads/video_thumbs
chmod 755 uploads/videos
chmod 755 uploads/video_thumbs
```

Expected: 目录创建成功

- [ ] **Step 6: 验证目录权限**

```bash
ls -la uploads/ | grep -E "videos|video_thumbs"
```

Expected: 显示两个目录，权限为 `drwxr-xr-x`

- [ ] **Step 7: 提交数据库迁移脚本**

```bash
git add admin/db_migration_video.sql
git add uploads/videos/.gitkeep
git add uploads/video_thumbs/.gitkeep
git commit -m "chore: 数据库迁移-添加视频支持字段和存储目录"
```

---

## Task 2: PHP工具函数

**Files:**
- Modify: `admin/Function.php`

- [ ] **Step 1: 读取现有Function.php文件**

```bash
cat admin/Function.php
```

Expected: 显示现有的PHP函数定义

- [ ] **Step 2: 在Function.php末尾添加视频工具函数**

在文件末尾（`?>` 之前）添加：

```php
/**
 * 格式化视频时长
 * @param int|null $seconds 秒数
 * @return string 格式化后的时长字符串 (HH:MM:SS 或 MM:SS)
 */
function formatDuration($seconds) {
    if ($seconds === null || $seconds === '') {
        return '--';
    }
    
    $seconds = intval($seconds);
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

/**
 * 格式化文件大小
 * @param int|null $bytes 字节数
 * @return string 格式化后的文件大小 (B/KB/MB/GB)
 */
function formatFileSize($bytes) {
    if ($bytes === null || $bytes === '') {
        return '--';
    }
    
    $bytes = floatval($bytes);
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * 验证视频文件类型
 * @param string $filename 文件名
 * @param string $mimeType MIME类型
 * @return bool 是否为允许的视频类型
 */
function isValidVideoFile($filename, $mimeType) {
    $allowedExtensions = ['mp4', 'avi', 'mov', 'webm', 'mkv'];
    $allowedMimeTypes = [
        'video/mp4',
        'video/x-msvideo',
        'video/quicktime',
        'video/webm',
        'video/x-matroska'
    ];
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    return in_array($extension, $allowedExtensions) && 
           in_array($mimeType, $allowedMimeTypes);
}
```

- [ ] **Step 3: 测试formatDuration函数**

创建测试文件 `admin/test_functions.php`:

```php
<?php
include_once 'Function.php';

// 测试 formatDuration
echo "Testing formatDuration:\n";
echo formatDuration(65) . " (expected: 01:05)\n";
echo formatDuration(3665) . " (expected: 01:01:05)\n";
echo formatDuration(null) . " (expected: --)\n";

// 测试 formatFileSize
echo "\nTesting formatFileSize:\n";
echo formatFileSize(1024) . " (expected: 1 KB)\n";
echo formatFileSize(1048576) . " (expected: 1 MB)\n";
echo formatFileSize(104857600) . " (expected: 100 MB)\n";

// 测试 isValidVideoFile
echo "\nTesting isValidVideoFile:\n";
echo (isValidVideoFile('test.mp4', 'video/mp4') ? 'true' : 'false') . " (expected: true)\n";
echo (isValidVideoFile('test.avi', 'video/x-msvideo') ? 'true' : 'false') . " (expected: true)\n";
echo (isValidVideoFile('test.exe', 'application/x-msdownload') ? 'true' : 'false') . " (expected: false)\n";
```

- [ ] **Step 4: 运行测试**

```bash
php admin/test_functions.php
```

Expected: 所有测试通过，输出符合预期

- [ ] **Step 5: 删除测试文件并提交**

```bash
rm admin/test_functions.php
git add admin/Function.php
git commit -m "feat: 添加视频工具函数(格式化时长、文件大小、类型验证)"
```

---

## Task 3: 前端视频缩略图提取模块

**Files:**
- Create: `Style/js/videoThumbnail.js`

- [ ] **Step 1: 创建videoThumbnail.js文件**

```javascript
/**
 * 视频缩略图提取模块
 * 使用Canvas API从视频文件中提取第一帧作为缩略图
 */
(function(window) {
    'use strict';

    /**
     * 从视频文件提取缩略图和元数据
     * @param {File} videoFile - 视频文件对象
     * @param {Function} callback - 回调函数 callback({thumbnail, duration, width, height, error})
     */
    function extractVideoThumbnail(videoFile, callback) {
        // 验证文件类型
        if (!videoFile.type.startsWith('video/')) {
            callback({ error: '不是有效的视频文件' });
            return;
        }

        // 验证文件大小 (100MB)
        const maxSize = 100 * 1024 * 1024;
        if (videoFile.size > maxSize) {
            callback({ error: '视频文件大小不能超过100MB' });
            return;
        }

        const video = document.createElement('video');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        video.preload = 'metadata';
        video.muted = true; // 静音避免自动播放限制
        
        // 创建对象URL
        const objectUrl = URL.createObjectURL(videoFile);
        video.src = objectUrl;
        
        // 错误处理
        video.addEventListener('error', function() {
            URL.revokeObjectURL(objectUrl);
            callback({ error: '无法加载视频文件，可能格式不支持' });
        });
        
        // 加载元数据完成
        video.addEventListener('loadeddata', function() {
            try {
                // 设置canvas尺寸
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // 绘制第一帧
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // 转换为base64 (JPEG格式，质量0.8)
                const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
                
                // 获取视频时长（秒，四舍五入）
                const duration = Math.round(video.duration);
                
                // 回调返回数据
                callback({
                    thumbnail: thumbnail,
                    duration: duration,
                    width: video.videoWidth,
                    height: video.videoHeight,
                    size: videoFile.size,
                    error: null
                });
                
            } catch (e) {
                callback({ error: '提取缩略图失败: ' + e.message });
            } finally {
                // 清理资源
                URL.revokeObjectURL(objectUrl);
            }
        });
    }

    /**
     * 格式化视频时长为 MM:SS 或 HH:MM:SS
     * @param {number} seconds - 秒数
     * @return {string} 格式化后的时长
     */
    function formatDuration(seconds) {
        if (!seconds || seconds === 0) return '00:00';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return pad(hours) + ':' + pad(minutes) + ':' + pad(secs);
        } else {
            return pad(minutes) + ':' + pad(secs);
        }
    }

    /**
     * 格式化文件大小
     * @param {number} bytes - 字节数
     * @return {string} 格式化后的文件大小
     */
    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let size = bytes;
        
        while (size >= 1024 && i < 3) {
            size /= 1024;
            i++;
        }
        
        return size.toFixed(2) + ' ' + units[i];
    }

    // 辅助函数：补零
    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }

    // 导出到全局
    window.VideoThumbnail = {
        extract: extractVideoThumbnail,
        formatDuration: formatDuration,
        formatFileSize: formatFileSize
    };

})(window);
```

- [ ] **Step 2: 创建测试HTML页面**

创建 `admin/test_video_thumbnail.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>视频缩略图提取测试</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        #result { margin-top: 20px; }
        #thumbnail { max-width: 400px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h2>视频缩略图提取测试</h2>
    <input type="file" id="videoInput" accept="video/*">
    <div id="result"></div>
    
    <script src="../Style/js/videoThumbnail.js"></script>
    <script>
        document.getElementById('videoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            document.getElementById('result').innerHTML = '<p>处理中...</p>';
            
            VideoThumbnail.extract(file, function(data) {
                if (data.error) {
                    document.getElementById('result').innerHTML = 
                        '<p style="color:red;">错误: ' + data.error + '</p>';
                    return;
                }
                
                document.getElementById('result').innerHTML = 
                    '<h3>提取成功</h3>' +
                    '<p>时长: ' + VideoThumbnail.formatDuration(data.duration) + '</p>' +
                    '<p>分辨率: ' + data.width + ' x ' + data.height + '</p>' +
                    '<p>文件大小: ' + VideoThumbnail.formatFileSize(data.size) + '</p>' +
                    '<h4>缩略图:</h4>' +
                    '<img id="thumbnail" src="' + data.thumbnail + '">';
            });
        });
    </script>
</body>
</html>
```

- [ ] **Step 3: 手动测试视频缩略图提取**

在浏览器中打开 `http://localhost/admin/test_video_thumbnail.html`，上传一个视频文件

Expected: 显示视频时长、分辨率、文件大小和缩略图预览

- [ ] **Step 4: 删除测试文件并提交**

```bash
rm admin/test_video_thumbnail.html
git add Style/js/videoThumbnail.js
git commit -m "feat: 添加视频缩略图提取模块(Canvas API)"
```

---

## Task 4: 后端单张上传接口扩展

**Files:**
- Modify: `admin/ImgAddPost.php`

- [ ] **Step 1: 读取现有ImgAddPost.php**

```bash
head -50 admin/ImgAddPost.php
```

Expected: 查看现有的上传处理逻辑

- [ ] **Step 2: 备份原文件**

```bash
cp admin/ImgAddPost.php admin/ImgAddPost.php.backup
```

- [ ] **Step 3: 在文件开头引入工具函数**

确保文件开头包含：

```php
<?php
session_start();
include_once 'connect.php';
include_once 'Function.php';

header('Content-Type: application/json; charset=utf-8');
```

- [ ] **Step 4: 添加视频上传处理逻辑**

在现有图片上传逻辑后添加（保留原有图片上传代码不变）：

```php
// 检测文件类型
$fileType = $_FILES['file']['type'];
$fileName = $_FILES['file']['name'];
$fileSize = $_FILES['file']['size'];

// 判断是图片还是视频
if (strpos($fileType, 'video/') === 0) {
    // === 视频上传处理 ===
    
    // 验证视频文件
    if (!isValidVideoFile($fileName, $fileType)) {
        echo json_encode([
            'code' => 400,
            'message' => '不支持的视频格式，仅支持 MP4、AVI、MOV、WebM、MKV'
        ]);
        exit;
    }
    
    // 验证文件大小 (100MB)
    $maxSize = 100 * 1024 * 1024;
    if ($fileSize > $maxSize) {
        echo json_encode([
            'code' => 400,
            'message' => '视频文件不能超过100MB'
        ]);
        exit;
    }
    
    // 生成唯一文件名
    $timestamp = date('YmdHis');
    $randomStr = substr(md5(uniqid()), 0, 8);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = $timestamp . '_' . $randomStr . '.' . $extension;
    
    // 保存视频文件
    $uploadDir = '../uploads/videos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $videoPath = $uploadDir . $newFileName;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $videoPath)) {
        echo json_encode([
            'code' => 500,
            'message' => '视频文件保存失败'
        ]);
        exit;
    }
    
    $videoUrl = '/uploads/videos/' . $newFileName;
    
    // 处理缩略图
    $thumbnailUrl = null;
    if (isset($_POST['thumbnail']) && !empty($_POST['thumbnail'])) {
        $thumbnailBase64 = $_POST['thumbnail'];
        $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $thumbnailBase64));
        
        if ($thumbnailData !== false) {
            $thumbDir = '../uploads/video_thumbs/';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            
            $thumbFileName = $timestamp . '_' . $randomStr . '.jpg';
            $thumbPath = $thumbDir . $thumbFileName;
            
            if (file_put_contents($thumbPath, $thumbnailData)) {
                $thumbnailUrl = '/uploads/video_thumbs/' . $thumbFileName;
            }
        }
    }
    
    // 获取视频时长和其他信息
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : null;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($connect, $_POST['description']) : '';
    $date = isset($_POST['date']) ? mysqli_real_escape_string($connect, $_POST['date']) : date('Y-m-d');
    $albumId = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;
    
    // 插入数据库
    $sql = "INSERT INTO loveImg (
        media_type,
        imgUrl,
        thumbnail_url,
        video_duration,
        file_size,
        imgText,
        imgDatd,
        album_id
    ) VALUES (
        'video',
        '$videoUrl',
        " . ($thumbnailUrl ? "'$thumbnailUrl'" : "NULL") . ",
        " . ($duration ? $duration : "NULL") . ",
        $fileSize,
        '$description',
        '$date',
        $albumId
    )";
    
    if (mysqli_query($connect, $sql)) {
        echo json_encode([
            'code' => 200,
            'message' => '视频上传成功',
            'data' => [
                'id' => mysqli_insert_id($connect),
                'url' => $videoUrl,
                'thumbnail' => $thumbnailUrl,
                'duration' => $duration
            ]
        ]);
    } else {
        // 上传失败，删除已保存的文件
        if (file_exists($videoPath)) unlink($videoPath);
        if ($thumbnailUrl && file_exists('../' . ltrim($thumbnailUrl, '/'))) {
            unlink('../' . ltrim($thumbnailUrl, '/'));
        }
        
        echo json_encode([
            'code' => 500,
            'message' => '数据库保存失败: ' . mysqli_error($connect)
        ]);
    }
    
} else {
    // === 保持原有的图片上传逻辑 ===
    // (不修改现有代码)
}
```

- [ ] **Step 5: 测试视频上传接口**

创建测试脚本 `admin/test_video_upload.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>测试视频上传</title>
    <script src="../Style/js/videoThumbnail.js"></script>
</head>
<body>
    <h2>测试视频上传接口</h2>
    <input type="file" id="videoFile" accept="video/*">
    <input type="text" id="description" placeholder="描述" value="测试视频">
    <button id="uploadBtn">上传</button>
    <div id="result"></div>
    
    <script>
    document.getElementById('uploadBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('videoFile');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('请选择视频文件');
            return;
        }
        
        document.getElementById('result').innerHTML = '提取缩略图中...';
        
        VideoThumbnail.extract(file, function(data) {
            if (data.error) {
                document.getElementById('result').innerHTML = '错误: ' + data.error;
                return;
            }
            
            document.getElementById('result').innerHTML = '上传中...';
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('thumbnail', data.thumbnail);
            formData.append('duration', data.duration);
            formData.append('description', document.getElementById('description').value);
            formData.append('date', new Date().toISOString().split('T')[0]);
            formData.append('album_id', 0);
            
            fetch('ImgAddPost.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                document.getElementById('result').innerHTML = 
                    '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '请求失败: ' + error;
            });
        });
    });
    </script>
</body>
</html>
```

- [ ] **Step 6: 手动测试上传**

在浏览器访问 `http://localhost/admin/test_video_upload.html`，上传一个小视频

Expected: 返回 `{"code": 200, "message": "视频上传成功", ...}`

- [ ] **Step 7: 验证数据库记录**

```bash
mysql -uroot -proot -e "USE loveImg; SELECT id, media_type, imgUrl, thumbnail_url, video_duration FROM loveImg ORDER BY id DESC LIMIT 1;"
```

Expected: 显示新插入的视频记录，media_type 为 'video'

- [ ] **Step 8: 删除测试文件并提交**

```bash
rm admin/test_video_upload.html
rm admin/ImgAddPost.php.backup
git add admin/ImgAddPost.php
git commit -m "feat: 单张上传接口支持视频文件"
```

---

## Task 5: 后端批量上传接口扩展

**Files:**
- Modify: `admin/batchAddImgPost.php`

- [ ] **Step 1: 读取现有batchAddImgPost.php**

```bash
head -30 admin/batchAddImgPost.php
```

Expected: 查看现有批量上传逻辑

- [ ] **Step 2: 修改批量上传接口支持视频**

在文件开头确保引入：

```php
<?php
session_start();
include_once 'connect.php';
include_once 'Function.php';

header('Content-Type: application/json; charset=utf-8');
```

在批量处理循环中，为每个文件判断类型：

```php
$uploadedFiles = [];
$errors = [];

foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
    $fileName = $_FILES['files']['name'][$index];
    $fileType = $_FILES['files']['type'][$index];
    $fileSize = $_FILES['files']['size'][$index];
    $fileError = $_FILES['files']['error'][$index];
    
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors[] = $fileName . ': 上传失败(错误代码' . $fileError . ')';
        continue;
    }
    
    // 判断文件类型
    $isVideo = strpos($fileType, 'video/') === 0;
    
    if ($isVideo) {
        // 视频处理
        if (!isValidVideoFile($fileName, $fileType)) {
            $errors[] = $fileName . ': 不支持的视频格式';
            continue;
        }
        
        if ($fileSize > 100 * 1024 * 1024) {
            $errors[] = $fileName . ': 视频文件超过100MB';
            continue;
        }
        
        // 生成唯一文件名
        $timestamp = date('YmdHis') . '_' . $index;
        $randomStr = substr(md5(uniqid()), 0, 8);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = $timestamp . '_' . $randomStr . '.' . $extension;
        
        // 保存视频
        $uploadDir = '../uploads/videos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $videoPath = $uploadDir . $newFileName;
        if (!move_uploaded_file($tmpName, $videoPath)) {
            $errors[] = $fileName . ': 保存失败';
            continue;
        }
        
        $videoUrl = '/uploads/videos/' . $newFileName;
        
        // 处理缩略图（从POST数据中获取）
        $thumbnailUrl = null;
        $thumbnailKey = 'thumbnail_' . $index;
        if (isset($_POST[$thumbnailKey]) && !empty($_POST[$thumbnailKey])) {
            $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST[$thumbnailKey]));
            
            if ($thumbnailData !== false) {
                $thumbDir = '../uploads/video_thumbs/';
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
                
                $thumbFileName = $timestamp . '_' . $randomStr . '.jpg';
                $thumbPath = $thumbDir . $thumbFileName;
                
                if (file_put_contents($thumbPath, $thumbnailData)) {
                    $thumbnailUrl = '/uploads/video_thumbs/' . $thumbFileName;
                }
            }
        }
        
        // 获取时长
        $durationKey = 'duration_' . $index;
        $duration = isset($_POST[$durationKey]) ? intval($_POST[$durationKey]) : null;
        
        // 获取其他信息
        $albumId = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;
        $date = isset($_POST['date']) ? mysqli_real_escape_string($connect, $_POST['date']) : date('Y-m-d');
        
        // 插入数据库
        $sql = "INSERT INTO loveImg (
            media_type, imgUrl, thumbnail_url, video_duration, file_size, imgText, imgDatd, album_id
        ) VALUES (
            'video', '$videoUrl', " . ($thumbnailUrl ? "'$thumbnailUrl'" : "NULL") . ", 
            " . ($duration ? $duration : "NULL") . ", $fileSize, '', '$date', $albumId
        )";
        
        if (mysqli_query($connect, $sql)) {
            $uploadedFiles[] = [
                'name' => $fileName,
                'type' => 'video',
                'url' => $videoUrl,
                'id' => mysqli_insert_id($connect)
            ];
        } else {
            $errors[] = $fileName . ': 数据库保存失败';
            if (file_exists($videoPath)) unlink($videoPath);
            if ($thumbnailUrl) unlink('../' . ltrim($thumbnailUrl, '/'));
        }
        
    } else {
        // === 图片处理（保持原有逻辑）===
        // (现有代码不变)
    }
}

// 返回结果
echo json_encode([
    'code' => 200,
    'message' => '批量上传完成',
    'data' => [
        'uploaded' => count($uploadedFiles),
        'failed' => count($errors),
        'files' => $uploadedFiles,
        'errors' => $errors
    ]
]);
```

- [ ] **Step 3: 提交批量上传接口**

```bash
git add admin/batchAddImgPost.php
git commit -m "feat: 批量上传接口支持图片视频混合上传"
```

---

## Task 6: 前端单张上传界面扩展

**Files:**
- Modify: `admin/loveImgAdd.php`

- [ ] **Step 1: 读取现有loveImgAdd.php文件**

```bash
grep -n "input.*type.*file" admin/loveImgAdd.php
```

Expected: 找到文件选择器的位置

- [ ] **Step 2: 修改文件选择器支持视频**

找到现有的文件输入框，修改 accept 属性：

```html
<input type="file" 
       name="mediaFile" 
       id="mediaFile"
       class="form-control"
       accept="image/*,video/mp4,video/avi,video/mov,video/webm,video/mkv">
```

- [ ] **Step 3: 添加视频预览区域**

在现有图片预览区域后添加视频预览：

```html
<!-- 图片预览（现有） -->
<div id="imagePreview" style="display:none;">
    <img id="previewImg" src="" alt="预览" style="max-width: 100%; max-height: 400px;">
</div>

<!-- 视频预览（新增） -->
<div id="videoPreview" style="display:none; margin-top: 15px;">
    <video id="previewVideo" controls style="max-width: 100%; max-height: 400px;">
        <source src="" type="video/mp4">
        您的浏览器不支持视频播放
    </video>
    <div class="video-info" style="margin-top: 10px;">
        <p><strong>视频信息：</strong></p>
        <p>时长: <span id="videoDuration" class="text-primary">--</span></p>
        <p>文件大小: <span id="videoSize" class="text-primary">--</span></p>
        <p>分辨率: <span id="videoResolution" class="text-primary">--</span></p>
    </div>
    <div id="thumbnailPreview" style="margin-top: 10px;">
        <p><strong>自动提取的缩略图：</strong></p>
        <img id="extractedThumbnail" src="" style="max-width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
</div>
```

- [ ] **Step 4: 在页面底部引入videoThumbnail.js**

在 `</body>` 标签前添加：

```html
<script src="../Style/js/videoThumbnail.js"></script>
```

- [ ] **Step 5: 添加文件选择处理脚本**

在引入videoThumbnail.js后添加：

```html
<script>
(function() {
    'use strict';
    
    let uploadData = {
        file: null,
        type: null,
        thumbnail: null,
        duration: null,
        size: null
    };
    
    // 文件选择处理
    document.getElementById('mediaFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const fileType = file.type.startsWith('video/') ? 'video' : 'image';
        
        // 隐藏所有预览
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('videoPreview').style.display = 'none';
        
        if (fileType === 'video') {
            handleVideoFile(file);
        } else {
            handleImageFile(file);
        }
    });
    
    function handleVideoFile(file) {
        // 验证大小
        if (file.size > 100 * 1024 * 1024) {
            alert('视频文件不能超过100MB，请选择较小的文件');
            document.getElementById('mediaFile').value = '';
            return;
        }
        
        // 显示加载提示
        const videoPreview = document.getElementById('videoPreview');
        videoPreview.style.display = 'block';
        document.getElementById('videoDuration').textContent = '提取中...';
        document.getElementById('videoSize').textContent = VideoThumbnail.formatFileSize(file.size);
        document.getElementById('videoResolution').textContent = '提取中...';
        
        // 显示视频预览
        const video = document.getElementById('previewVideo');
        const videoUrl = URL.createObjectURL(file);
        video.src = videoUrl;
        
        // 提取缩略图
        VideoThumbnail.extract(file, function(data) {
            if (data.error) {
                alert('视频处理失败: ' + data.error);
                document.getElementById('mediaFile').value = '';
                videoPreview.style.display = 'none';
                URL.revokeObjectURL(videoUrl);
                return;
            }
            
            // 更新视频信息
            document.getElementById('videoDuration').textContent = VideoThumbnail.formatDuration(data.duration);
            document.getElementById('videoResolution').textContent = data.width + ' × ' + data.height;
            
            // 显示缩略图
            document.getElementById('extractedThumbnail').src = data.thumbnail;
            
            // 保存上传数据
            uploadData = {
                file: file,
                type: 'video',
                thumbnail: data.thumbnail,
                duration: data.duration,
                size: file.size
            };
        });
    }
    
    function handleImageFile(file) {
        // 图片处理（保持现有逻辑）
        const imagePreview = document.getElementById('imagePreview');
        imagePreview.style.display = 'block';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // 保存上传数据
        uploadData = {
            file: file,
            type: 'image',
            thumbnail: null,
            duration: null,
            size: file.size
        };
    }
    
    // 修改原有的提交函数
    const originalSubmit = window.submitUpload || function() {};
    
    window.submitUpload = function() {
        if (!uploadData.file) {
            alert('请先选择文件');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', uploadData.file);
        formData.append('type', uploadData.type);
        
        if (uploadData.type === 'video') {
            formData.append('thumbnail', uploadData.thumbnail);
            formData.append('duration', uploadData.duration);
            formData.append('file_size', uploadData.size);
        }
        
        // 获取表单其他字段
        formData.append('description', document.getElementById('description').value || '');
        formData.append('date', document.getElementById('date').value || new Date().toISOString().split('T')[0]);
        formData.append('album_id', document.getElementById('album_id').value || 0);
        
        // 显示上传中
        const submitBtn = document.querySelector('button[type="submit"]') || document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '上传中...';
        }
        
        // AJAX上传
        fetch('ImgAddPost.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.code === 200) {
                alert('上传成功！');
                window.location.href = 'loveImgSet.php';
            } else {
                alert('上传失败: ' + result.message);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '提交';
                }
            }
        })
        .catch(error => {
            alert('上传出错: ' + error);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '提交';
            }
        });
    };
    
})();
</script>
```

- [ ] **Step 6: 测试单张上传界面**

在浏览器访问 `http://localhost/admin/loveImgAdd.php`

Expected: 
1. 选择视频文件时显示视频预览
2. 自动提取并显示缩略图
3. 显示视频时长、大小、分辨率
4. 提交成功后跳转到管理页面

- [ ] **Step 7: 提交单张上传界面**

```bash
git add admin/loveImgAdd.php
git commit -m "feat: 单张上传界面支持视频文件选择和预览"
```

---

## Task 7: 前端批量上传界面扩展

**Files:**
- Modify: `admin/batchAddImg.php`

- [ ] **Step 1: 修改文件选择器**

找到批量上传的文件输入框，修改为：

```html
<input type="file" 
       name="files[]" 
       id="batchFiles"
       class="form-control"
       accept="image/*,video/*"
       multiple>
```

- [ ] **Step 2: 添加上传队列显示区域**

在文件选择器后添加：

```html
<div id="uploadQueue" style="margin-top: 20px; display: none;">
    <h5>上传队列 (<span id="queueCount">0</span> 个文件)</h5>
    <div id="queueItems" class="list-group">
        <!-- 队列项动态生成 -->
    </div>
    <div style="margin-top: 15px;">
        <button type="button" id="startUploadBtn" class="btn btn-primary">
            <i class="mdi mdi-upload"></i> 开始上传
        </button>
        <button type="button" id="clearQueueBtn" class="btn btn-secondary">
            <i class="mdi mdi-close-circle"></i> 清空队列
        </button>
    </div>
</div>
```

- [ ] **Step 3: 在页面底部引入videoThumbnail.js**

```html
<script src="../Style/js/videoThumbnail.js"></script>
```

- [ ] **Step 4: 添加批量上传处理脚本**

```html
<script>
(function() {
    'use strict';
    
    let uploadQueue = [];
    
    // 文件选择处理
    document.getElementById('batchFiles').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;
        
        uploadQueue = [];
        document.getElementById('queueItems').innerHTML = '<p class="text-muted">处理文件中...</p>';
        document.getElementById('uploadQueue').style.display = 'block';
        
        processFiles(files);
    });
    
    async function processFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileType = file.type.startsWith('video/') ? 'video' : 'image';
            
            const queueItem = {
                index: i,
                file: file,
                type: fileType,
                name: file.name,
                size: file.size,
                thumbnail: null,
                duration: null,
                status: 'pending'
            };
            
            if (fileType === 'video') {
                // 验证视频大小
                if (file.size > 100 * 1024 * 1024) {
                    queueItem.status = 'error';
                    queueItem.error = '文件超过100MB';
                    uploadQueue.push(queueItem);
                    continue;
                }
                
                // 提取缩略图
                await new Promise((resolve) => {
                    VideoThumbnail.extract(file, function(data) {
                        if (data.error) {
                            queueItem.status = 'error';
                            queueItem.error = data.error;
                        } else {
                            queueItem.thumbnail = data.thumbnail;
                            queueItem.duration = data.duration;
                        }
                        resolve();
                    });
                });
            }
            
            uploadQueue.push(queueItem);
        }
        
        renderQueue();
    }
    
    function renderQueue() {
        const container = document.getElementById('queueItems');
        container.innerHTML = '';
        
        uploadQueue.forEach(item => {
            const div = document.createElement('div');
            div.className = 'list-group-item';
            div.id = 'queue-item-' + item.index;
            
            const iconClass = item.type === 'video' ? 'mdi-video' : 'mdi-image';
            const typeText = item.type === 'video' ? '视频' : '图片';
            const sizeText = VideoThumbnail.formatFileSize(item.size);
            const durationText = item.type === 'video' && item.duration ? 
                ' · ' + VideoThumbnail.formatDuration(item.duration) : '';
            
            let statusHtml = '';
            if (item.status === 'error') {
                statusHtml = '<span class="badge badge-danger">' + item.error + '</span>';
            } else if (item.status === 'uploading') {
                statusHtml = '<span class="badge badge-info">上传中...</span>';
            } else if (item.status === 'success') {
                statusHtml = '<span class="badge badge-success">已上传</span>';
            } else {
                statusHtml = '<span class="badge badge-secondary">待上传</span>';
            }
            
            div.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="mdi ${iconClass} mr-2" style="font-size: 24px;"></i>
                    <div class="flex-grow-1">
                        <strong>${item.name}</strong>
                        <div class="text-muted small">${typeText} · ${sizeText}${durationText}</div>
                    </div>
                    ${statusHtml}
                </div>
            `;
            
            container.appendChild(div);
        });
        
        document.getElementById('queueCount').textContent = uploadQueue.length;
    }
    
    // 开始上传
    document.getElementById('startUploadBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = '上传中...';
        
        const albumId = document.getElementById('album_id') ? document.getElementById('album_id').value : 0;
        const date = new Date().toISOString().split('T')[0];
        
        let successCount = 0;
        let failCount = 0;
        
        for (const item of uploadQueue) {
            if (item.status === 'error') {
                failCount++;
                continue;
            }
            
            item.status = 'uploading';
            renderQueue();
            
            const formData = new FormData();
            formData.append('file', item.file);
            formData.append('type', item.type);
            formData.append('album_id', albumId);
            formData.append('date', date);
            
            if (item.type === 'video') {
                formData.append('thumbnail', item.thumbnail);
                formData.append('duration', item.duration);
                formData.append('file_size', item.size);
            }
            
            try {
                const response = await fetch('ImgAddPost.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.code === 200) {
                    item.status = 'success';
                    successCount++;
                } else {
                    item.status = 'error';
                    item.error = result.message;
                    failCount++;
                }
            } catch (error) {
                item.status = 'error';
                item.error = '上传失败';
                failCount++;
            }
            
            renderQueue();
        }
        
        btn.disabled = false;
        btn.textContent = '开始上传';
        
        alert(`上传完成！成功: ${successCount}，失败: ${failCount}`);
        
        if (successCount > 0) {
            setTimeout(() => {
                window.location.href = 'loveImgSet.php';
            }, 1500);
        }
    });
    
    // 清空队列
    document.getElementById('clearQueueBtn').addEventListener('click', function() {
        if (confirm('确定清空上传队列吗？')) {
            uploadQueue = [];
            document.getElementById('batchFiles').value = '';
            document.getElementById('uploadQueue').style.display = 'none';
        }
    });
    
})();
</script>
```

- [ ] **Step 5: 测试批量上传**

在浏览器访问 `http://localhost/admin/batchAddImg.php`

Expected:
1. 可以同时选择图片和视频
2. 显示上传队列，视频自动提取缩略图
3. 点击开始上传后逐个上传
4. 显示上传进度和结果

- [ ] **Step 6: 提交批量上传界面**

```bash
git add admin/batchAddImg.php
git commit -m "feat: 批量上传界面支持图片视频混合上传"
```

---

## Task 8: 视频CSS样式

**Files:**
- Create: `Style/css/video.css`

- [ ] **Step 1: 创建video.css文件**

```css
/* ===== 视频卡片样式 ===== */
.video-card {
    position: relative;
}

.video-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 2;
}

.video-indicator i {
    font-size: 48px;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    display: block;
}

.video-card .duration {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    z-index: 3;
    pointer-events: none;
}

.video-card:hover .video-indicator i {
    color: rgba(255, 255, 255, 1);
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* ===== 视频播放器弹窗 ===== */
.lightbox-video {
    max-width: 90vw;
    max-height: 80vh;
    background: #000;
}

.lightbox-video:focus {
    outline: none;
}

/* ===== 后台管理视频预览 ===== */
.video-preview-thumb {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.video-preview-thumb .img-thumbnail {
    width: 120px;
    height: 90px;
    object-fit: cover;
}

.video-preview-thumb i.mdi-play-circle {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 32px;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    pointer-events: none;
}

.video-preview-thumb .duration-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 10px;
}

.video-preview-thumb:hover {
    opacity: 0.8;
}

/* ===== 视频信息展示 ===== */
.video-info {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.video-info p {
    margin: 5px 0;
}

/* ===== 媒体类型徽章 ===== */
.badge.badge-video {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.badge.badge-image {
    background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%);
}

/* ===== 响应式 ===== */
@media (max-width: 768px) {
    .video-indicator i {
        font-size: 36px;
    }
    
    .video-card .duration {
        font-size: 10px;
        padding: 1px 4px;
    }
    
    .video-preview-thumb .img-thumbnail {
        width: 80px;
        height: 60px;
    }
    
    .video-preview-thumb i.mdi-play-circle {
        font-size: 24px;
    }
}
```

- [ ] **Step 2: 提交CSS文件**

```bash
git add Style/css/video.css
git commit -m "style: 添加视频卡片和播放器样式"
```

---

## Task 9: 视频播放器模块

**Files:**
- Create: `Style/js/videoPlayer.js`

- [ ] **Step 1: 创建videoPlayer.js文件**

```javascript
/**
 * 视频播放器模块
 * 复用现有lightbox框架，扩展支持视频播放
 */
(function(window) {
    'use strict';

    /**
     * 打开媒体灯箱（图片或视频）
     * @param {Object} mediaData - 媒体数据
     * @param {string} mediaData.type - 'image' 或 'video'
     * @param {string} mediaData.url - 媒体URL
     * @param {string} mediaData.description - 描述
     * @param {Array} mediaList - 媒体列表（用于上一个/下一个）
     * @param {number} currentIndex - 当前索引
     */
    function openMediaLightbox(mediaData, mediaList, currentIndex) {
        const lightbox = createLightbox();
        
        renderMedia(lightbox, mediaData);
        setupControls(lightbox, mediaList, currentIndex);
        setupKeyboard(lightbox, mediaList, currentIndex);
        
        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';
        
        // 淡入效果
        setTimeout(() => {
            lightbox.style.opacity = '1';
        }, 10);
    }

    function createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.className = 'media-lightbox';
        lightbox.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        return lightbox;
    }

    function renderMedia(lightbox, mediaData) {
        const contentDiv = document.createElement('div');
        contentDiv.className = 'lightbox-content';
        contentDiv.style.cssText = `
            max-width: 90vw;
            max-height: 85vh;
            position: relative;
        `;
        
        if (mediaData.type === 'video') {
            // 视频播放器
            const video = document.createElement('video');
            video.className = 'lightbox-video';
            video.controls = true;
            video.autoplay = true;
            video.style.cssText = `
                max-width: 90vw;
                max-height: 80vh;
                display: block;
            `;
            
            const source = document.createElement('source');
            source.src = mediaData.url;
            source.type = getVideoMimeType(mediaData.url);
            
            video.appendChild(source);
            
            // 添加错误处理
            video.addEventListener('error', function() {
                contentDiv.innerHTML = '<p style="color: white; padding: 20px;">视频加载失败</p>';
            });
            
            contentDiv.appendChild(video);
            
        } else {
            // 图片
            const img = document.createElement('img');
            img.className = 'lightbox-image';
            img.src = mediaData.url;
            img.alt = mediaData.description || '';
            img.style.cssText = `
                max-width: 90vw;
                max-height: 85vh;
                display: block;
            `;
            
            contentDiv.appendChild(img);
        }
        
        // 添加描述
        if (mediaData.description) {
            const desc = document.createElement('div');
            desc.style.cssText = `
                color: white;
                text-align: center;
                margin-top: 10px;
                font-size: 14px;
            `;
            desc.textContent = mediaData.description;
            contentDiv.appendChild(desc);
        }
        
        lightbox.appendChild(contentDiv);
    }

    function setupControls(lightbox, mediaList, currentIndex) {
        const controls = document.createElement('div');
        controls.className = 'lightbox-controls';
        controls.style.cssText = `
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        `;
        
        const buttonStyle = `
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        `;
        
        // 上一个按钮
        if (mediaList && currentIndex > 0) {
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '← 上一个';
            prevBtn.style.cssText = buttonStyle;
            prevBtn.onclick = function() {
                closeLightbox(lightbox);
                setTimeout(() => {
                    openMediaLightbox(mediaList[currentIndex - 1], mediaList, currentIndex - 1);
                }, 300);
            };
            prevBtn.onmouseover = function() {
                this.style.background = 'rgba(255, 255, 255, 0.3)';
            };
            prevBtn.onmouseout = function() {
                this.style.background = 'rgba(255, 255, 255, 0.2)';
            };
            controls.appendChild(prevBtn);
        }
        
        // 关闭按钮
        const closeBtn = document.createElement('button');
        closeBtn.textContent = '✕ 关闭';
        closeBtn.style.cssText = buttonStyle;
        closeBtn.onclick = function() {
            closeLightbox(lightbox);
        };
        closeBtn.onmouseover = function() {
            this.style.background = 'rgba(255, 255, 255, 0.3)';
        };
        closeBtn.onmouseout = function() {
            this.style.background = 'rgba(255, 255, 255, 0.2)';
        };
        controls.appendChild(closeBtn);
        
        // 下一个按钮
        if (mediaList && currentIndex < mediaList.length - 1) {
            const nextBtn = document.createElement('button');
            nextBtn.textContent = '下一个 →';
            nextBtn.style.cssText = buttonStyle;
            nextBtn.onclick = function() {
                closeLightbox(lightbox);
                setTimeout(() => {
                    openMediaLightbox(mediaList[currentIndex + 1], mediaList, currentIndex + 1);
                }, 300);
            };
            nextBtn.onmouseover = function() {
                this.style.background = 'rgba(255, 255, 255, 0.3)';
            };
            nextBtn.onmouseout = function() {
                this.style.background = 'rgba(255, 255, 255, 0.2)';
            };
            controls.appendChild(nextBtn);
        }
        
        lightbox.appendChild(controls);
    }

    function setupKeyboard(lightbox, mediaList, currentIndex) {
        const keyHandler = function(e) {
            if (e.key === 'Escape') {
                closeLightbox(lightbox);
            } else if (e.key === 'ArrowLeft' && mediaList && currentIndex > 0) {
                closeLightbox(lightbox);
                setTimeout(() => {
                    openMediaLightbox(mediaList[currentIndex - 1], mediaList, currentIndex - 1);
                }, 300);
            } else if (e.key === 'ArrowRight' && mediaList && currentIndex < mediaList.length - 1) {
                closeLightbox(lightbox);
                setTimeout(() => {
                    openMediaLightbox(mediaList[currentIndex + 1], mediaList, currentIndex + 1);
                }, 300);
            } else if (e.key === ' ') {
                // 空格键播放/暂停视频
                const video = lightbox.querySelector('video');
                if (video) {
                    e.preventDefault();
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                }
            }
        };
        
        document.addEventListener('keydown', keyHandler);
        lightbox._keyHandler = keyHandler;
    }

    function closeLightbox(lightbox) {
        // 移除键盘监听
        if (lightbox._keyHandler) {
            document.removeEventListener('keydown', lightbox._keyHandler);
        }
        
        // 停止视频播放
        const video = lightbox.querySelector('video');
        if (video) {
            video.pause();
            video.src = '';
        }
        
        // 淡出效果
        lightbox.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(lightbox);
            document.body.style.overflow = '';
        }, 300);
    }

    function getVideoMimeType(url) {
        const ext = url.split('.').pop().toLowerCase();
        const mimeTypes = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'avi': 'video/x-msvideo',
            'mov': 'video/quicktime',
            'mkv': 'video/x-matroska'
        };
        return mimeTypes[ext] || 'video/mp4';
    }

    // 导出到全局
    window.MediaPlayer = {
        open: openMediaLightbox
    };

})(window);
```

- [ ] **Step 2: 提交视频播放器模块**

```bash
git add Style/js/videoPlayer.js
git commit -m "feat: 添加视频播放器模块(支持弹窗播放和快捷键)"
```

---

## Task 10: 前端展示页面扩展

**Files:**
- Modify: `loveImg.php`
- Modify: `Style/js/loveAlbum/album.js`
- Modify: `Style/css/loveImg.css`

- [ ] **Step 1: 在loveImg.php引入新资源**

在 `<head>` 部分添加：

```html
<link rel="stylesheet" href="Style/css/video.css?v=<?php echo $version ?>">
```

在 `</body>` 前添加：

```html
<script src="Style/js/videoThumbnail.js"></script>
<script src="Style/js/videoPlayer.js"></script>
```

- [ ] **Step 2: 修改getPhotos.php返回视频字段**

找到 `getPhotos.php` 文件，修改SQL查询：

```php
$sql = "SELECT 
    li.*,
    la.album_name,
    la.album_desc
FROM loveImg li
LEFT JOIN love_album la ON li.album_id = la.id
WHERE 1=1";

// 添加相册筛选等现有逻辑...

$result = mysqli_query($connect, $sql);
$photos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $photos[] = [
        'id' => $row['id'],
        'type' => $row['media_type'], // 新增
        'url' => $row['imgUrl'],
        'thumbnail' => $row['thumbnail_url'], // 新增
        'duration' => $row['video_duration'], // 新增
        'description' => $row['imgText'],
        'date' => $row['imgDatd'],
        'album_id' => $row['album_id'],
        'album_name' => $row['album_name']
    ];
}

echo json_encode([
    'code' => 200,
    'data' => $photos
]);
```

- [ ] **Step 3: 修改album.js渲染逻辑**

找到 `Style/js/loveAlbum/album.js` 中渲染照片的函数，修改为：

```javascript
function renderPhotos(photos) {
    const $gallery = $('#photoGallery');
    $gallery.empty();
    
    if (!photos || photos.length === 0) {
        $gallery.html('<div class="col-12 text-center" style="padding: 40px;">暂无内容</div>');
        return;
    }
    
    photos.forEach((item, index) => {
        const col = $('<div>').addClass('col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4');
        
        // 根据类型渲染不同的卡片
        if (item.type === 'video') {
            // 视频卡片
            const card = $('<div>')
                .addClass('media-card video-card')
                .attr('data-type', 'video')
                .attr('data-url', item.url)
                .attr('data-thumb', item.thumbnail || item.url)
                .attr('data-duration', item.duration || 0)
                .attr('data-description', item.description || '')
                .attr('data-index', index);
            
            const img = $('<img>')
                .attr('src', item.thumbnail || item.url)
                .attr('alt', item.description || '视频')
                .css({
                    'width': '100%',
                    'height': '200px',
                    'object-fit': 'cover',
                    'border-radius': '8px',
                    'cursor': 'pointer'
                });
            
            const indicator = $('<div>')
                .addClass('video-indicator')
                .html('<i class="mdi mdi-play-circle"></i>');
            
            const duration = $('<span>')
                .addClass('duration')
                .text(formatDuration(item.duration));
            
            card.append(img, indicator, duration);
            
            // 点击播放视频
            card.on('click', function() {
                const index = parseInt($(this).attr('data-index'));
                playMedia(index);
            });
            
            col.append(card);
            
        } else {
            // 图片卡片（保持原有逻辑）
            const card = $('<div>')
                .addClass('media-card image-card')
                .attr('data-type', 'image')
                .attr('data-url', item.url)
                .attr('data-description', item.description || '')
                .attr('data-index', index);
            
            const img = $('<img>')
                .attr('src', item.url)
                .attr('alt', item.description || '图片')
                .css({
                    'width': '100%',
                    'height': '200px',
                    'object-fit': 'cover',
                    'border-radius': '8px',
                    'cursor': 'pointer'
                });
            
            card.append(img);
            
            // 点击查看图片
            card.on('click', function() {
                const index = parseInt($(this).attr('data-index'));
                playMedia(index);
            });
            
            col.append(card);
        }
        
        $gallery.append(col);
    });
}

// 播放媒体（图片或视频）
function playMedia(index) {
    const mediaList = [];
    
    $('.media-card').each(function() {
        const type = $(this).attr('data-type');
        const url = $(this).attr('data-url');
        const description = $(this).attr('data-description');
        
        mediaList.push({
            type: type,
            url: url,
            description: description
        });
    });
    
    if (mediaList[index]) {
        MediaPlayer.open(mediaList[index], mediaList, index);
    }
}

// 格式化时长
function formatDuration(seconds) {
    if (!seconds) return '00:00';
    
    seconds = parseInt(seconds);
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    
    return (minutes < 10 ? '0' : '') + minutes + ':' + (secs < 10 ? '0' : '') + secs;
}
```

- [ ] **Step 4: 测试前端展示**

在浏览器访问 `http://localhost/loveImg.php`

Expected:
1. 视频卡片显示缩略图、播放图标和时长
2. 点击视频打开播放器弹窗
3. 播放器支持播放/暂停、音量、进度控制
4. 可以通过左右箭头切换上一个/下一个
5. ESC键关闭播放器

- [ ] **Step 5: 提交前端展示修改**

```bash
git add loveImg.php Style/js/loveAlbum/album.js Style/css/loveImg.css getPhotos.php
git commit -m "feat: 前端展示页面支持视频卡片和播放器"
```

---

## Task 11: 后台管理页面扩展

**Files:**
- Modify: `admin/loveImgSet.php`
- Modify: `admin/modImg.php`
- Modify: `admin/delImg.php`

- [ ] **Step 1: 在loveImgSet.php引入资源**

在 `<head>` 部分添加：

```html
<link rel="stylesheet" href="../Style/css/video.css">
```

在 `</body>` 前添加：

```html
<script src="../Style/js/videoPlayer.js"></script>
```

- [ ] **Step 2: 修改loveImgSet.php表头**

在表头添加类型列：

```php
<thead>
    <tr>
        <th width="40px"><input type="checkbox" id="selectAll"></th>
        <th>序号</th>
        <th>预览</th>
        <th>类型</th>  <!-- 新增 -->
        <th>描述</th>
        <th>所属相册</th>
        <th>日期</th>
        <th style="width:150px;">操作</th>
    </tr>
</thead>
```

- [ ] **Step 3: 修改loveImgSet.php数据行**

修改数据查询和渲染：

```php
<?php
// 关联查询获取相册名称和媒体信息
$loveImg = "SELECT li.*, la.album_name FROM loveImg li
            LEFT JOIN love_album la ON li.album_id = la.id
            ORDER BY li.id DESC";
$resImg = mysqli_query($connect, $loveImg);
?>

<!-- 数据行 -->
<?php
$SerialNumber = 0;
while ($list = mysqli_fetch_array($resImg)) {
    $SerialNumber++;
    $isVideo = $list['media_type'] === 'video';
?>
    <tr id="row-<?php echo $list['id']; ?>"
        data-photo-id="<?php echo $list['id']; ?>"
        data-album-id="<?php echo (int)($list['album_id'] ?? 0); ?>"
        data-type="<?php echo $list['media_type']; ?>">
        <td>
            <input type="checkbox" class="photo-checkbox" value="<?php echo $list['id']; ?>">
        </td>
        <td>
            <div class="SerialNumber"><?php echo $SerialNumber ?></div>
        </td>
        
        <!-- 预览列 -->
        <td>
            <?php if ($isVideo): ?>
                <div class="video-preview-thumb" 
                     onclick="previewVideo('<?php echo $list['imgUrl']; ?>', '<?php echo htmlspecialchars($list['imgText'], ENT_QUOTES); ?>')">
                    <img src="<?php echo $list['thumbnail_url'] ?: $list['imgUrl']; ?>" 
                         class="img-thumbnail"
                         alt="视频预览">
                    <i class="mdi mdi-play-circle"></i>
                    <span class="duration-badge">
                        <?php echo formatDuration($list['video_duration']); ?>
                    </span>
                </div>
            <?php else: ?>
                <img src="<?php echo $list['imgUrl'] ?>"
                     class="img-thumbnail"
                     alt="预览"
                     onclick="window.open('<?php echo $list['imgUrl'] ?>', '_blank')">
            <?php endif; ?>
        </td>
        
        <!-- 类型列 -->
        <td>
            <?php if ($isVideo): ?>
                <span class="badge badge-primary">
                    <i class="mdi mdi-video"></i> 视频
                </span>
            <?php else: ?>
                <span class="badge badge-info">
                    <i class="mdi mdi-image"></i> 图片
                </span>
            <?php endif; ?>
        </td>
        
        <td><?php echo $list['imgText'] ?></td>
        <td>
            <span class="<?php echo $list['album_name'] ? 'album-badge' : 'album-badge album-none'; ?>">
                <i class="mdi mdi-folder"></i> 
                <?php echo $list['album_name'] ?: '未分类'; ?>
            </span>
        </td>
        <td><?php echo $list['imgDatd'] ?></td>
        <td>
            <a href="modImg.php?id=<?php echo $list['id'] ?>">
                <button type="button" class="btn btn-secondary btn-rounded">
                    <i class="mdi mdi-clipboard-text-play-outline mr-1"></i>修改
                </button>
            </a>
            <button type="button" class="btn btn-danger btn-rounded" 
                    onclick="del(<?php echo $list['id']; ?>, '<?php echo htmlspecialchars($list['imgText'], ENT_QUOTES); ?>')">
                <i class="mdi mdi-delete-empty mr-1"></i>删除
            </button>
        </td>
    </tr>
<?php } ?>
```

- [ ] **Step 4: 添加类型筛选器**

在现有的相册筛选器后添加：

```html
<div class="mb-3">
    <label class="mr-2"><i class="mdi mdi-filter-outline"></i> 按类型筛选</label>
    <select id="typeFilter" class="form-control form-control-sm" 
            style="display:inline-block;width:auto;min-width:150px;">
        <option value="">全部类型</option>
        <option value="image">仅图片</option>
        <option value="video">仅视频</option>
    </select>
</div>

<script>
$('#typeFilter').on('change', function() {
    const selectedType = $(this).val();
    const rows = $('#photoTbody tr');
    
    if (selectedType === '') {
        rows.show();
    } else {
        rows.each(function() {
            const rowType = $(this).data('type');
            if (rowType === selectedType) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    updateSerialNumbers();
});

// 视频预览函数
function previewVideo(url, description) {
    MediaPlayer.open({
        type: 'video',
        url: url,
        description: description
    });
}
</script>
```

- [ ] **Step 5: 修改delImg.php支持视频删除**

```php
<?php
session_start();
include_once 'connect.php';

$id = intval($_GET['id']);

// 查询媒体信息
$query = "SELECT * FROM loveImg WHERE id = $id";
$result = mysqli_query($connect, $query);
$media = mysqli_fetch_assoc($result);

if (!$media) {
    echo json_encode(['code' => 404, 'message' => '记录不存在']);
    exit;
}

// 删除文件
if ($media['media_type'] === 'video') {
    // 删除视频文件
    $videoPath = $_SERVER['DOCUMENT_ROOT'] . $media['imgUrl'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
    
    // 删除缩略图
    if ($media['thumbnail_url']) {
        $thumbPath = $_SERVER['DOCUMENT_ROOT'] . $media['thumbnail_url'];
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }
} else {
    // 删除图片（现有逻辑）
    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $media['imgUrl'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// 删除数据库记录
$deleteSql = "DELETE FROM loveImg WHERE id = $id";
if (mysqli_query($connect, $deleteSql)) {
    echo json_encode(['code' => 200, 'message' => '删除成功']);
} else {
    echo json_encode(['code' => 500, 'message' => '删除失败: ' . mysqli_error($connect)]);
}
?>
```

- [ ] **Step 6: 修改modImg.php编辑页面**

在编辑页面显示视频信息：

```php
<?php
$media = mysqli_fetch_assoc($result);
$isVideo = $media['media_type'] === 'video';
?>

<!-- 预览区域 -->
<?php if ($isVideo): ?>
    <div class="form-group">
        <label>视频预览</label>
        <div>
            <video controls style="max-width: 400px;">
                <source src="<?php echo $media['imgUrl']; ?>">
            </video>
        </div>
        <div class="mt-2">
            <p>时长: <?php echo formatDuration($media['video_duration']); ?></p>
            <p>文件大小: <?php echo formatFileSize($media['file_size']); ?></p>
        </div>
    </div>
<?php else: ?>
    <div class="form-group">
        <label>图片预览</label>
        <div>
            <img src="<?php echo $media['imgUrl']; ?>" 
                 style="max-width: 400px;" alt="预览">
        </div>
    </div>
<?php endif; ?>

<!-- 其他字段保持不变 -->
```

- [ ] **Step 7: 测试后台管理功能**

访问 `http://localhost/admin/loveImgSet.php`

Expected:
1. 列表显示视频类型图标
2. 视频预览显示缩略图和时长
3. 点击视频预览打开播放器
4. 类型筛选功能正常
5. 删除视频同时删除视频文件和缩略图
6. 编辑页面显示视频信息

- [ ] **Step 8: 提交后台管理修改**

```bash
git add admin/loveImgSet.php admin/modImg.php admin/delImg.php
git commit -m "feat: 后台管理页面支持视频显示、筛选、编辑和删除"
```

---

## Task 12: 综合测试

**Files:**
- 无文件修改，纯测试

- [ ] **Step 1: 测试视频上传功能**

测试清单：
- [ ] 上传MP4格式视频
- [ ] 上传AVI格式视频
- [ ] 上传MOV格式视频
- [ ] 上传大文件(接近100MB)
- [ ] 尝试上传超过100MB的文件(应被拒绝)
- [ ] 验证缩略图自动生成
- [ ] 验证视频时长正确提取

- [ ] **Step 2: 测试批量上传功能**

测试清单：
- [ ] 同时选择3张图片和2个视频批量上传
- [ ] 验证上传队列正确显示
- [ ] 验证所有文件成功上传
- [ ] 验证数据库记录正确

- [ ] **Step 3: 测试前端展示功能**

测试清单：
- [ ] 相册页面图片视频混合显示
- [ ] 视频卡片显示播放图标和时长
- [ ] 点击视频打开播放器
- [ ] 播放器控制功能(播放/暂停/音量/进度/全屏)
- [ ] 上一个/下一个切换(图片视频混合)
- [ ] ESC键关闭播放器
- [ ] 空格键播放/暂停

- [ ] **Step 4: 测试后台管理功能**

测试清单：
- [ ] 管理页面显示视频类型
- [ ] 视频预览点击播放
- [ ] 类型筛选(全部/仅图片/仅视频)
- [ ] 视频编辑功能
- [ ] 视频删除(文件+缩略图+数据库)
- [ ] 批量操作支持视频

- [ ] **Step 5: 测试兼容性**

测试清单：
- [ ] 现有图片功能不受影响
- [ ] 相册分类功能正常
- [ ] 批量转移支持视频
- [ ] 批量删除支持视频

- [ ] **Step 6: 性能测试**

```bash
# 查看uploads目录大小
du -sh uploads/videos uploads/video_thumbs

# 查看数据库表大小
mysql -uroot -proot -e "
SELECT 
    table_name,
    round(((data_length + index_length) / 1024 / 1024), 2) as 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'loveImg' AND table_name = 'loveImg';
"
```

Expected: 视频和缩略图文件正常存储，数据库表大小合理

- [ ] **Step 7: 记录测试结果**

创建测试报告 `docs/test_report_video.md`:

```markdown
# 视频功能测试报告

**测试日期**: 2026-06-15
**测试人**: [测试人员]

## 测试结果

### 上传功能
- [x] MP4上传正常
- [x] AVI上传正常
- [x] MOV上传正常
- [x] 大文件验证正常
- [x] 缩略图生成正常
- [x] 时长提取正常

### 展示功能
- [x] 视频卡片显示正常
- [x] 播放器功能完整
- [x] 切换功能正常
- [x] 快捷键正常

### 管理功能
- [x] 类型显示正常
- [x] 筛选功能正常
- [x] 编辑功能正常
- [x] 删除功能正常

### 兼容性
- [x] 图片功能不受影响
- [x] 所有现有功能正常

## 问题记录

（记录测试中发现的问题）

## 结论

所有功能测试通过，可以上线使用。
```

- [ ] **Step 8: 提交测试报告**

```bash
git add docs/test_report_video.md
git commit -m "docs: 添加视频功能测试报告"
```

---

## Task 13: 文档和清理

**Files:**
- Create: `docs/VIDEO_FEATURE.md`
- Modify: `README.md` (if exists)

- [ ] **Step 1: 创建用户文档**

```markdown
# 视频上传和播放功能使用指南

## 功能概述

恋爱相册系统现已支持视频上传和播放功能，可以与图片混合管理和展示。

## 支持的视频格式

- MP4
- AVI
- MOV
- WebM
- MKV

## 文件大小限制

单个视频文件不超过 **100MB**

## 如何上传视频

### 单张上传

1. 进入后台管理 → 恋爱相册 → 单张新增
2. 点击文件选择器，选择视频文件
3. 系统自动提取视频第一帧作为缩略图
4. 填写描述、日期、选择相册
5. 点击提交完成上传

### 批量上传

1. 进入后台管理 → 恋爱相册 → 批量新增
2. 同时选择多个图片和视频文件
3. 系统自动处理所有文件
4. 点击开始上传完成批量上传

## 如何播放视频

### 前端展示页面

1. 访问恋爱相册页面
2. 视频卡片显示缩略图、播放图标和时长
3. 点击视频卡片打开播放器
4. 使用播放器控制按钮或快捷键

### 快捷键

- **空格键**: 播放/暂停
- **← →**: 上一个/下一个
- **ESC**: 关闭播放器

## 后台管理

### 视频列表

- 类型列显示"视频"标识
- 预览列显示缩略图和播放图标
- 点击预览可直接播放

### 筛选功能

使用类型筛选器可以：
- 查看全部内容
- 仅显示图片
- 仅显示视频

### 编辑和删除

- 编辑: 可修改描述、日期、所属相册
- 删除: 同时删除视频文件、缩略图和数据库记录

## 技术实现

- 缩略图: 前端Canvas API自动提取视频第一帧
- 播放器: HTML5 Video标签
- 存储: 视频文件和缩略图分别存储在独立目录

## 浏览器兼容性

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

**不支持IE浏览器**

## 常见问题

**Q: 上传视频后为什么看不到缩略图？**
A: 请确保浏览器支持Canvas API，刷新页面重试。

**Q: 视频无法播放？**
A: 检查视频格式是否支持，建议使用MP4格式。

**Q: 如何删除视频？**
A: 在后台管理页面找到对应视频，点击删除按钮。

## 更新日志

**v1.0 (2026-06-15)**
- 初始版本发布
- 支持视频上传和播放
- 支持图片视频混合展示
```

- [ ] **Step 2: 提交用户文档**

```bash
git add docs/VIDEO_FEATURE.md
git commit -m "docs: 添加视频功能用户使用指南"
```

- [ ] **Step 3: 清理临时文件**

```bash
# 删除.gitkeep文件(如果添加了)
rm uploads/videos/.gitkeep uploads/video_thumbs/.gitkeep 2>/dev/null || true

# 删除测试文件备份
find admin -name "*.backup" -delete
```

- [ ] **Step 4: 最终提交**

```bash
git add -A
git commit -m "chore: 清理临时文件，视频功能开发完成"
```

---

## 完成检查清单

- [ ] 数据库表结构已修改
- [ ] 视频存储目录已创建
- [ ] PHP工具函数已添加
- [ ] 视频缩略图提取模块已实现
- [ ] 后端单张上传接口已扩展
- [ ] 后端批量上传接口已扩展
- [ ] 前端单张上传界面已扩展
- [ ] 前端批量上传界面已扩展
- [ ] 视频CSS样式已添加
- [ ] 视频播放器模块已实现
- [ ] 前端展示页面已扩展
- [ ] 后台管理页面已扩展
- [ ] 综合测试已完成
- [ ] 用户文档已创建
- [ ] 所有修改已提交到git

---

## 实施后验证

**验证步骤：**

1. 访问 `http://localhost/admin/loveImgAdd.php`
2. 上传一个MP4视频文件
3. 验证缩略图自动生成
4. 访问 `http://localhost/loveImg.php`
5. 验证视频卡片显示
6. 点击视频验证播放器功能
7. 访问 `http://localhost/admin/loveImgSet.php`
8. 验证视频在管理页面正确显示
9. 测试类型筛选功能
10. 测试视频删除功能

**预期结果：** 所有功能正常工作，图片功能不受影响

---

## 故障排查

### 问题1: 视频上传失败

**可能原因:**
- PHP配置限制

**解决方案:**
```bash
# 检查PHP配置
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"

# 如果限制太小，修改php.ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

### 问题2: 缩略图生成失败

**可能原因:**
- 浏览器不支持Canvas API
- 视频格式不支持

**解决方案:**
- 使用现代浏览器(Chrome/Firefox)
- 转换视频为MP4格式

### 问题3: 视频无法播放

**可能原因:**
- 视频编码不支持
- MIME类型错误

**解决方案:**
- 使用H.264编码的MP4视频
- 检查服务器MIME类型配置

---

**实施计划结束**