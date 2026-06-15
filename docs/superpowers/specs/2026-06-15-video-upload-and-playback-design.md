# 视频上传和播放功能设计文档

**日期**: 2026-06-15  
**版本**: 1.0  
**状态**: 设计完成，待实施

---

## 📝 需求概述

在现有的恋爱相册系统中添加视频上传和播放功能，实现图片和视频的统一管理。

### 功能需求
- 支持常见视频格式上传（MP4, AVI, MOV, WebM, MKV）
- 单个视频文件大小限制：≤ 100MB
- 自动提取视频第一帧作为缩略图
- 弹窗内播放视频
- 支持视频与图片混合展示

### 非功能需求
- 向后兼容现有图片功能
- 最小化代码重复
- 良好的用户体验

---

## 🏗️ 技术方案

### 方案选择：统一媒体表

采用**统一媒体表**方案，将图片和视频存储在同一张表中，通过 `media_type` 字段区分类型。

**优势：**
- 代码复用性高，图片功能可直接用于视频
- 统一管理简单，相册可同时包含图片和视频
- 前端展示逻辑统一
- 扩展性强，未来可支持更多媒体类型

---

## 🗄️ 数据库设计

### 表结构修改

修改 `loveImg` 表，添加以下字段：

```sql
ALTER TABLE `loveImg` 
ADD COLUMN `media_type` ENUM('image', 'video') DEFAULT 'image' 
    COMMENT '媒体类型' AFTER `id`,
ADD COLUMN `thumbnail_url` VARCHAR(500) DEFAULT NULL 
    COMMENT '视频缩略图URL' AFTER `imgUrl`,
ADD COLUMN `video_duration` INT DEFAULT NULL 
    COMMENT '视频时长(秒)' AFTER `thumbnail_url`,
ADD COLUMN `file_size` BIGINT DEFAULT NULL 
    COMMENT '文件大小(字节)' AFTER `video_duration`;
```

### 字段说明

| 字段名 | 类型 | 说明 | 图片 | 视频 |
|--------|------|------|------|------|
| `media_type` | ENUM | 媒体类型 | 'image' | 'video' |
| `imgUrl` | VARCHAR(500) | 文件URL | 图片URL | 视频URL |
| `thumbnail_url` | VARCHAR(500) | 缩略图URL | NULL | 缩略图URL |
| `video_duration` | INT | 视频时长(秒) | NULL | 时长值 |
| `file_size` | BIGINT | 文件大小(字节) | 可选 | 文件大小 |

### 数据迁移

```sql
-- 现有图片数据自动标记为image类型
UPDATE loveImg 
SET media_type = 'image' 
WHERE media_type IS NULL OR media_type = '';
```

---

## 📁 文件存储结构

```
uploads/
├── images/          # 图片存储（现有）
├── thumbs/          # 图片缩略图（现有）
├── videos/          # 新增：视频文件
└── video_thumbs/    # 新增：视频缩略图
```

### 文件命名规则

**视频文件：** `uploads/videos/YYYYMMDDHHmmss_随机8位.扩展名`  
**视频缩略图：** `uploads/video_thumbs/YYYYMMDDHHmmss_随机8位.jpg`

**示例：**
- 视频：`uploads/videos/20260615143022_a3f9d8e1.mp4`
- 缩略图：`uploads/video_thumbs/20260615143022_a3f9d8e1.jpg`

---

## 🔧 后端实现

### 视频上传流程

#### 1. 文件验证
```php
// 允许的视频格式
$allowedExtensions = ['mp4', 'avi', 'mov', 'webm', 'mkv'];
$allowedMimeTypes = ['video/mp4', 'video/x-msvideo', 'video/quicktime', 
                     'video/webm', 'video/x-matroska'];
$maxFileSize = 100 * 1024 * 1024; // 100MB

// 验证逻辑
if ($_FILES['file']['size'] > $maxFileSize) {
    // 文件过大
}
if (!in_array($extension, $allowedExtensions)) {
    // 格式不支持
}
```

#### 2. 文件上传处理
```php
// 生成唯一文件名
$timestamp = date('YmdHis');
$randomStr = substr(md5(uniqid()), 0, 8);
$filename = $timestamp . '_' . $randomStr . '.' . $extension;

// 保存文件
$uploadPath = 'uploads/videos/' . $filename;
move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath);

// 返回视频URL
$videoUrl = '/uploads/videos/' . $filename;
```

#### 3. 缩略图上传处理
```php
// 接收前端传来的base64缩略图
$thumbnailBase64 = $_POST['thumbnail'];
$thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $thumbnailBase64));

// 保存缩略图
$thumbnailFilename = $timestamp . '_' . $randomStr . '.jpg';
$thumbnailPath = 'uploads/video_thumbs/' . $thumbnailFilename;
file_put_contents($thumbnailPath, $thumbnailData);

// 缩略图URL
$thumbnailUrl = '/uploads/video_thumbs/' . $thumbnailFilename;
```

#### 4. 数据库写入
```php
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
    '$thumbnailUrl',
    $duration,
    $fileSize,
    '$description',
    '$date',
    $albumId
)";
```

### 需要修改的后端文件

| 文件 | 改动内容 |
|------|----------|
| `admin/ImgAddPost.php` | 单张上传接口，添加视频处理逻辑 |
| `admin/batchAddImgPost.php` | 批量上传接口，支持图片视频混合 |
| `admin/loveImgSet.php` | 管理页面，显示媒体类型和视频信息 |
| `admin/modImg.php` | 编辑页面，支持视频字段编辑 |
| `admin/delImg.php` | 删除功能，同时删除视频和缩略图 |
| `getPhotos.php` | API接口，返回媒体类型和视频字段 |

---

## 🎨 前端实现

### 相册展示页面（loveImg.php）

#### 混合媒体卡片

**HTML结构：**
```html
<!-- 图片卡片 -->
<div class="media-card image-card" data-type="image" data-url="...">
    <img src="图片URL" alt="描述">
</div>

<!-- 视频卡片 -->
<div class="media-card video-card" data-type="video" 
     data-url="视频URL" data-thumb="缩略图URL" data-duration="155">
    <img src="缩略图URL" alt="描述">
    <div class="video-indicator">
        <i class="mdi mdi-play-circle"></i>
        <span class="duration">02:35</span>
    </div>
</div>
```

**CSS样式：**
```css
.video-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.video-indicator i {
    font-size: 48px;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.duration {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}
```

### 视频播放器

#### 弹窗播放器设计

复用现有的图片灯箱（lightbox）框架，根据媒体类型渲染不同的内容。

**HTML结构：**
```html
<div class="media-lightbox">
    <div class="lightbox-content">
        <!-- 如果是图片 -->
        <img src="图片URL" class="lightbox-image">
        
        <!-- 如果是视频 -->
        <video class="lightbox-video" controls>
            <source src="视频URL" type="video/mp4">
            您的浏览器不支持视频播放
        </video>
    </div>
    
    <div class="lightbox-controls">
        <button class="prev-btn">上一个</button>
        <button class="close-btn">关闭</button>
        <button class="next-btn">下一个</button>
    </div>
</div>
```

**JavaScript逻辑：**
```javascript
function openMediaLightbox(mediaData) {
    if (mediaData.type === 'image') {
        // 显示图片
        showImage(mediaData.url);
    } else if (mediaData.type === 'video') {
        // 显示视频播放器
        showVideo(mediaData.url);
    }
}
```

**播放器功能：**
- ▶️ 播放/暂停
- 🔊 音量控制
- ⏩ 进度条拖动
- ⏱️ 时间显示（当前/总时长）
- 🖼️ 全屏播放
- ⬅️➡️ 上一个/下一个（支持图片视频混合切换）
- ⌨️ 快捷键支持（空格播放/暂停，ESC关闭）

---

### 缩略图生成（前端）

#### 使用Canvas提取视频第一帧

```javascript
function extractVideoThumbnail(videoFile, callback) {
    const video = document.createElement('video');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    video.preload = 'metadata';
    video.src = URL.createObjectURL(videoFile);
    
    video.addEventListener('loadeddata', function() {
        // 设置canvas尺寸
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // 绘制第一帧
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // 转换为base64
        const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
        
        // 获取视频时长
        const duration = Math.round(video.duration);
        
        callback({
            thumbnail: thumbnail,
            duration: duration,
            width: video.videoWidth,
            height: video.videoHeight
        });
        
        // 清理
        URL.revokeObjectURL(video.src);
    });
}
```

**使用示例：**
```javascript
// 用户选择视频文件时
fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file.type.startsWith('video/')) {
        extractVideoThumbnail(file, function(data) {
            // 显示缩略图预览
            previewImg.src = data.thumbnail;
            
            // 显示视频信息
            console.log('时长:', data.duration, '秒');
            console.log('分辨率:', data.width, 'x', data.height);
            
            // 上传时一起提交
            uploadData.thumbnail = data.thumbnail;
            uploadData.duration = data.duration;
        });
    }
});
```

---

## 📤 上传界面设计

### 单张上传页面（loveImgAdd.php）

#### 文件选择器
```html
<input type="file" 
       name="mediaFile" 
       id="mediaFile"
       accept="image/*,video/mp4,video/avi,video/mov,video/webm,video/mkv">
```

#### 预览区域
```html
<div id="previewArea">
    <!-- 图片预览 -->
    <div id="imagePreview" style="display:none;">
        <img id="previewImg" src="" alt="预览">
    </div>
    
    <!-- 视频预览 -->
    <div id="videoPreview" style="display:none;">
        <video id="previewVideo" controls>
            <source src="" type="video/mp4">
        </video>
        <div class="video-info">
            <p>时长: <span id="videoDuration">--</span></p>
            <p>大小: <span id="videoSize">--</span></p>
        </div>
    </div>
</div>
```

#### 上传流程

```javascript
// 1. 用户选择文件
fileInput.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    const fileType = file.type.startsWith('video/') ? 'video' : 'image';
    
    if (fileType === 'video') {
        // 验证视频大小
        if (file.size > 100 * 1024 * 1024) {
            alert('视频文件不能超过100MB');
            return;
        }
        
        // 提取缩略图和元数据
        const videoData = await extractVideoThumbnail(file);
        
        // 显示预览
        showVideoPreview(file, videoData);
        
        // 保存数据用于上传
        uploadData = {
            file: file,
            type: 'video',
            thumbnail: videoData.thumbnail,
            duration: videoData.duration,
            size: file.size
        };
    } else {
        // 图片处理（现有逻辑）
        showImagePreview(file);
    }
});

// 2. 提交上传
async function submitUpload() {
    const formData = new FormData();
    formData.append('file', uploadData.file);
    formData.append('type', uploadData.type);
    
    if (uploadData.type === 'video') {
        formData.append('thumbnail', uploadData.thumbnail);
        formData.append('duration', uploadData.duration);
        formData.append('file_size', uploadData.size);
    }
    
    formData.append('description', $('#description').val());
    formData.append('date', $('#date').val());
    formData.append('album_id', $('#album_id').val());
    
    // AJAX上传
    const response = await fetch('ImgAddPost.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    // 处理结果...
}
```

---

### 批量上传页面（batchAddImg.php）

#### 混合批量上传

**文件选择：**
```html
<input type="file" 
       name="files[]" 
       id="batchFiles"
       accept="image/*,video/*"
       multiple>
```

**上传队列显示：**
```html
<div class="upload-queue">
    <div class="queue-item" data-type="image">
        <i class="mdi mdi-image"></i>
        <span class="filename">photo1.jpg</span>
        <span class="size">2.3 MB</span>
        <div class="progress-bar">
            <div class="progress" style="width: 60%"></div>
        </div>
    </div>
    
    <div class="queue-item" data-type="video">
        <i class="mdi mdi-video"></i>
        <span class="filename">video1.mp4</span>
        <span class="size">45.8 MB</span>
        <span class="duration">02:35</span>
        <div class="progress-bar">
            <div class="progress" style="width: 30%"></div>
        </div>
    </div>
</div>
```

**批量处理逻辑：**
```javascript
async function batchUpload(files) {
    const total = files.length;
    let completed = 0;
    
    for (const file of files) {
        const fileType = file.type.startsWith('video/') ? 'video' : 'image';
        
        if (fileType === 'video') {
            // 提取缩略图
            const videoData = await extractVideoThumbnail(file);
            
            // 上传视频和缩略图
            await uploadSingleVideo(file, videoData);
        } else {
            // 上传图片
            await uploadSingleImage(file);
        }
        
        completed++;
        updateProgress(completed, total);
    }
}
```

---

## 🎯 后台管理页面设计

### 恋爱相册管理页（loveImgSet.php）

#### 列表显示改进

**表头添加类型列：**
```html
<thead>
    <tr>
        <th>复选框</th>
        <th>序号</th>
        <th>预览</th>
        <th>类型</th>  <!-- 新增 -->
        <th>描述</th>
        <th>所属相册</th>
        <th>日期</th>
        <th>操作</th>
    </tr>
</thead>
```

**数据行渲染：**
```php
<tr data-type="<?php echo $row['media_type']; ?>">
    <td><input type="checkbox" value="<?php echo $row['id']; ?>"></td>
    <td><?php echo $num; ?></td>
    
    <!-- 预览列 -->
    <td>
        <?php if ($row['media_type'] === 'image'): ?>
            <img src="<?php echo $row['imgUrl']; ?>" 
                 class="img-thumbnail" 
                 onclick="previewImage('<?php echo $row['imgUrl']; ?>')">
        <?php else: ?>
            <div class="video-preview-thumb" 
                 onclick="previewVideo('<?php echo $row['imgUrl']; ?>')">
                <img src="<?php echo $row['thumbnail_url']; ?>" 
                     class="img-thumbnail">
                <i class="mdi mdi-play-circle"></i>
                <span class="duration-badge">
                    <?php echo formatDuration($row['video_duration']); ?>
                </span>
            </div>
        <?php endif; ?>
    </td>
    
    <!-- 类型列 -->
    <td>
        <?php if ($row['media_type'] === 'image'): ?>
            <span class="badge badge-info">
                <i class="mdi mdi-image"></i> 图片
            </span>
        <?php else: ?>
            <span class="badge badge-primary">
                <i class="mdi mdi-video"></i> 视频
            </span>
        <?php endif; ?>
    </td>
    
    <td><?php echo $row['imgText']; ?></td>
    <td><?php echo $row['album_name']; ?></td>
    <td><?php echo $row['imgDatd']; ?></td>
    <td>
        <a href="modImg.php?id=<?php echo $row['id']; ?>">
            <button class="btn btn-secondary btn-sm">修改</button>
        </a>
        <button class="btn btn-danger btn-sm" 
                onclick="deleteMedia(<?php echo $row['id']; ?>)">删除</button>
    </td>
</tr>
```

#### 筛选功能扩展

**添加类型筛选器：**
```html
<div class="filter-bar">
    <select id="albumFilter" class="form-control">
        <option value="">全部相册</option>
        <!-- 相册选项 -->
    </select>
    
    <select id="typeFilter" class="form-control">  <!-- 新增 -->
        <option value="">全部类型</option>
        <option value="image">仅图片</option>
        <option value="video">仅视频</option>
    </select>
</div>
```

**前端筛选逻辑：**
```javascript
$('#typeFilter').on('change', function() {
    const selectedType = $(this).val();
    const rows = $('tbody tr');
    
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
```

---

## 🔄 删除功能扩展

### 视频删除逻辑

```php
// admin/delImg.php

$id = intval($_GET['id']);

// 查询媒体信息
$query = "SELECT * FROM loveImg WHERE id = $id";
$result = mysqli_query($connect, $query);
$media = mysqli_fetch_assoc($result);

if ($media['media_type'] === 'video') {
    // 删除视频文件
    $videoPath = $_SERVER['DOCUMENT_ROOT'] . $media['imgUrl'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
    
    // 删除缩略图
    $thumbPath = $_SERVER['DOCUMENT_ROOT'] . $media['thumbnail_url'];
    if (file_exists($thumbPath)) {
        unlink($thumbPath);
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
mysqli_query($connect, $deleteSql);
```

---

## 📊 工具函数

### PHP辅助函数

```php
// 格式化视频时长
function formatDuration($seconds) {
    if ($seconds === null) return '--';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

// 格式化文件大小
function formatFileSize($bytes) {
    if ($bytes === null) return '--';
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
```

---

## 🧪 测试计划

### 功能测试清单

#### 上传测试
- [ ] 单张视频上传（各种格式：MP4, AVI, MOV, WebM, MKV）
- [ ] 批量混合上传（图片+视频）
- [ ] 大文件上传（接近100MB）
- [ ] 超大文件拒绝（>100MB）
- [ ] 缩略图自动生成
- [ ] 视频时长正确提取

#### 展示测试
- [ ] 相册页图片视频混合显示
- [ ] 视频卡片显示播放图标和时长
- [ ] 点击视频打开弹窗播放器
- [ ] 视频播放器功能完整（播放、暂停、进度、音量、全屏）
- [ ] 上一个/下一个功能（图片视频混合切换）

#### 管理测试
- [ ] 后台列表显示类型标识
- [ ] 类型筛选功能
- [ ] 视频预览点击播放
- [ ] 视频编辑功能
- [ ] 视频删除（文件+缩略图+数据库）
- [ ] 批量操作支持视频

#### 兼容性测试
- [ ] 现有图片功能不受影响
- [ ] 数据库迁移成功
- [ ] 相册分类功能正常
- [ ] 批量转移支持视频

---

## 📝 实施计划

### 阶段1：数据库和文件结构（1天）
1. 执行数据库ALTER语句
2. 创建 `uploads/videos/` 和 `uploads/video_thumbs/` 目录
3. 数据迁移（标记现有数据为image类型）

### 阶段2：后端上传接口（2天）
1. 修改 `ImgAddPost.php` 支持视频
2. 修改 `batchAddImgPost.php` 支持混合上传
3. 添加视频文件验证逻辑
4. 实现缩略图保存接口

### 阶段3：前端上传界面（2天）
1. 修改 `loveImgAdd.php` 文件选择器
2. 实现视频缩略图提取JS
3. 添加视频预览功能
4. 修改 `batchAddImg.php` 批量上传

### 阶段4：前端展示和播放（2天）
1. 修改 `loveImg.php` 混合媒体卡片
2. 实现视频播放器弹窗
3. 添加播放控制功能
4. 实现上一个/下一个切换

### 阶段5：后台管理（1天）
1. 修改 `loveImgSet.php` 列表显示
2. 添加类型筛选功能
3. 修改 `modImg.php` 编辑页面
4. 修改 `delImg.php` 删除逻辑

### 阶段6：测试和优化（1天）
1. 功能测试
2. 性能优化
3. 兼容性测试
4. Bug修复

**预计总工期：9天**

---

## 🚀 技术栈

- **后端**: PHP 7.4+, MySQL 5.7+
- **前端**: jQuery 3.x, HTML5 Video API, Canvas API
- **UI框架**: Bootstrap 4.x, MDI图标
- **上传**: FormData + AJAX
- **视频处理**: 纯前端JS（无需FFmpeg）

---

## 📋 注意事项

### 性能优化
1. **视频懒加载**：仅在可视区域加载视频缩略图
2. **分页加载**：每页加载20-30个媒体项
3. **缩略图压缩**：JPEG质量0.8，尺寸适当缩小

### 安全考虑
1. **文件类型验证**：后端验证MIME类型和扩展名
2. **文件大小限制**：PHP配置 `upload_max_filesize=100M`, `post_max_size=100M`
3. **路径安全**：防止目录遍历攻击
4. **XSS防护**：视频描述等输入需过滤

### 浏览器兼容性
- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

**不支持IE浏览器**（HTML5 Video限制）

---

## 🎯 未来扩展

### 可选功能（后续迭代）
1. **视频压缩**：服务端自动压缩大视频
2. **多码率支持**：自动生成不同清晰度
3. **字幕支持**：WebVTT字幕文件上传
4. **直播功能**：HLS流媒体播放
5. **视频剪辑**：前端简单裁剪功能
6. **水印功能**：自动添加视频水印

---

## ✅ 总结

本设计文档详细描述了在恋爱相册系统中添加视频上传和播放功能的完整方案。采用统一媒体表设计，最大化代码复用，实现图片和视频的无缝混合管理。通过前端Canvas提取缩略图，避免服务器端依赖FFmpeg，降低部署复杂度。整体方案技术成熟、实施可行、扩展性强。