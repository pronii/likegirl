# 批量上传接口文档

## 文件：`admin/batchUploadLocalPost.php`

该接口已扩展支持**图片和视频混合批量上传**。

---

## 功能特性

✅ 支持单文件上传（向后兼容原有功能）  
✅ 支持批量多文件上传（新功能）  
✅ 支持图片和视频混合上传  
✅ 自动识别文件类型并分别处理  
✅ 视频支持缩略图和时长元数据  
✅ 完整的错误处理和文件清理机制

---

## API 使用方式

### 1. 单文件上传（现有功能，保持兼容）

**请求参数：**
```
POST /admin/batchUploadLocalPost.php
Content-Type: multipart/form-data

imageFile: [File]       // 图片文件
album_id: [int]         // 相册ID
imgDatd: [string]       // 日期 (YYYY-MM-DD)
imgText: [string]       // 描述（可选）
```

**返回格式：**
```json
{
  "success": true,
  "message": "上传成功",
  "imgUrl": "/uploads/images/20260616123456_abc123.jpg",
  "thumbUrl": "/uploads/thumbs/20260616123456_abc123.jpg",
  "id": 123,
  "filename": "20260616123456_abc123.jpg"
}
```

---

### 2. 批量上传（新功能）

**请求参数：**
```
POST /admin/batchUploadLocalPost.php
Content-Type: multipart/form-data

files[0]: [File]              // 第1个文件（图片或视频）
files[1]: [File]              // 第2个文件（图片或视频）
files[n]: [File]              // 第n个文件
album_id: [int]               // 相册ID
imgDatd: [string]             // 日期 (YYYY-MM-DD)

// 可选参数（每个文件）
imgText_0: [string]           // 第1个文件的描述
imgText_1: [string]           // 第2个文件的描述
thumbnail_0: [base64]         // 第1个视频的缩略图（base64）
thumbnail_1: [base64]         // 第2个视频的缩略图（base64）
duration_0: [int]             // 第1个视频的时长（秒）
duration_1: [int]             // 第2个视频的时长（秒）
```

**返回格式：**
```json
{
  "code": 200,
  "message": "批量上传完成",
  "data": {
    "uploaded": 3,
    "failed": 1,
    "files": [
      {
        "id": 101,
        "name": "photo1.jpg",
        "type": "image",
        "url": "/uploads/images/20260616123456_0_abc123.jpg",
        "thumbnail_url": "/uploads/thumbs/20260616123456_0_abc123.jpg",
        "file_size": "2.35 MB"
      },
      {
        "id": 102,
        "name": "video1.mp4",
        "type": "video",
        "url": "/uploads/videos/20260616123456_1_def456.mp4",
        "thumbnail_url": "/uploads/video_thumbs/20260616123456_1_def456.jpg",
        "duration": 120,
        "file_size": "45.67 MB"
      },
      {
        "id": 103,
        "name": "photo2.png",
        "type": "image",
        "url": "/uploads/images/20260616123456_2_ghi789.png",
        "thumbnail_url": "/uploads/thumbs/20260616123456_2_ghi789.png",
        "file_size": "1.82 MB"
      }
    ],
    "errors": [
      "文件 invalid.avi: 不支持的视频格式"
    ]
  }
}
```

---

## 文件类型支持

### 图片格式
- JPEG/JPG
- PNG
- GIF
- WebP
- 最大大小：10 MB
- 自动压缩原图（1920x1080）
- 自动生成缩略图（300x300）

### 视频格式
- MP4
- AVI
- MOV
- WebM
- MKV
- 最大大小：100 MB
- 支持前端提取的缩略图（base64）
- 支持时长元数据

---

## 文件命名规则

**格式：** `YmdHis_索引_随机字符串.扩展名`

**示例：**
- `20260616143025_0_a3f8c912.jpg` （批量上传第1个文件）
- `20260616143025_1_b7d2e453.mp4` （批量上传第2个文件）
- `20260616143025_2_c9f1a678.png` （批量上传第3个文件）

**说明：**
- 时间戳确保唯一性
- 索引防止同一秒内的文件名冲突
- 随机字符串增加安全性

---

## 数据库字段映射

### 图片记录
```sql
INSERT INTO loveImg (
    media_type,      -- 'image'
    imgUrl,          -- '/uploads/images/xxx.jpg'
    thumbnail_url,   -- '/uploads/thumbs/xxx.jpg'
    file_size,       -- 文件大小（字节）
    imgText,         -- 描述
    imgDatd,         -- 日期
    album_id         -- 相册ID
)
```

### 视频记录
```sql
INSERT INTO loveImg (
    media_type,      -- 'video'
    imgUrl,          -- '/uploads/videos/xxx.mp4'
    thumbnail_url,   -- '/uploads/video_thumbs/xxx.jpg' 或 NULL
    video_duration,  -- 时长（秒）或 NULL
    file_size,       -- 文件大小（字节）
    imgText,         -- 描述
    imgDatd,         -- 日期
    album_id         -- 相册ID
)
```

---

## 错误处理

### 失败场景
1. **格式验证失败** → 跳过该文件，继续处理下一个
2. **大小超限** → 跳过该文件，继续处理下一个
3. **文件保存失败** → 跳过该文件，继续处理下一个
4. **数据库插入失败** → 删除已保存的文件，跳过该文件

### 部分成功
接口会处理所有文件，返回成功和失败的详细信息：
- `uploaded`: 成功上传的文件数量
- `failed`: 失败的文件数量
- `files`: 成功文件的详细信息数组
- `errors`: 失败原因数组

---

## JavaScript 调用示例

```javascript
// 批量上传多个文件（图片+视频混合）
async function batchUploadFiles(files, albumId, date) {
    const formData = new FormData();
    
    // 添加文件
    files.forEach((fileData, index) => {
        formData.append(`files[${index}]`, fileData.file);
        
        // 添加描述
        if (fileData.description) {
            formData.append(`imgText_${index}`, fileData.description);
        }
        
        // 如果是视频，添加缩略图和时长
        if (fileData.thumbnail) {
            formData.append(`thumbnail_${index}`, fileData.thumbnail);
        }
        if (fileData.duration) {
            formData.append(`duration_${index}`, fileData.duration);
        }
    });
    
    // 添加公共参数
    formData.append('album_id', albumId);
    formData.append('imgDatd', date);
    
    // 发送请求
    const response = await fetch('admin/batchUploadLocalPost.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    console.log(`成功: ${result.data.uploaded}, 失败: ${result.data.failed}`);
    console.log('上传的文件:', result.data.files);
    console.log('错误信息:', result.data.errors);
    
    return result;
}

// 使用示例
const files = [
    { 
        file: imageFile1, 
        description: '美丽的风景' 
    },
    { 
        file: videoFile1, 
        thumbnail: 'data:image/jpeg;base64,/9j/4AAQ...',
        duration: 125,
        description: '旅行视频'
    },
    { 
        file: imageFile2, 
        description: '可爱的猫咪' 
    }
];

batchUploadFiles(files, 5, '2026-06-16')
    .then(result => {
        if (result.data.failed === 0) {
            alert('全部上传成功！');
        } else {
            alert(`上传完成：成功 ${result.data.uploaded} 个，失败 ${result.data.failed} 个`);
        }
    });
```

---

## 目录结构

```
uploads/
├── images/           # 图片存储（压缩后）
├── thumbs/           # 图片缩略图
├── videos/           # 视频存储
└── video_thumbs/     # 视频缩略图
```

所有目录会在首次上传时自动创建（权限：0755）。

---

## 注意事项

1. **权限检查**：接口会验证用户登录状态（`$_SESSION['loginadmin']`）
2. **事务处理**：每个文件独立处理，失败不影响其他文件
3. **文件清理**：数据库插入失败时自动删除已保存的文件
4. **向后兼容**：保留原有单文件上传功能，不影响现有前端代码
5. **索引命名**：批量上传时使用 `files[0]`, `files[1]` 等索引格式

---

## 测试建议

1. 测试纯图片批量上传
2. 测试纯视频批量上传
3. 测试图片+视频混合上传
4. 测试单个文件上传（验证兼容性）
5. 测试文件格式验证
6. 测试文件大小限制
7. 测试部分失败场景

---

**修改完成时间：** 2026-06-16  
**修改的文件：** `admin/batchUploadLocalPost.php`  
**依赖函数：** `admin/Function.php` (isValidVideoFile, formatFileSize, formatDuration)
