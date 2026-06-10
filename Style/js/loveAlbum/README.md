# LoveAlbum 模块化重构文档

## 📁 目录结构

```
Style/js/
├── loveAlbum.js              # 主入口文件（加载器）
├── loveAlbum.js.old          # 原始文件备份
└── loveAlbum/
    ├── state.js              # 状态管理模块
    ├── album.js              # 相册操作模块
    ├── selection.js          # 选择功能模块
    ├── drag.js               # 拖动框选模块
    └── main.js               # 主控制器
```

## 🔧 模块说明

### 1. state.js - 状态管理
**职责**: 管理所有全局状态
- 相册状态（当前相册ID、页码等）
- 选择模式状态
- 拖动状态
- 提供状态重置方法

**主要接口**:
```javascript
LoveAlbumState.currentAlbumId
LoveAlbumState.selectedPhotos
LoveAlbumState.reset()
```

### 2. album.js - 相册操作
**职责**: 处理相册和照片的加载、显示
- 加载相册列表
- 打开相册
- 加载照片
- 创建照片元素

**主要接口**:
```javascript
LoveAlbumCore.loadAlbums()
LoveAlbumCore.open(albumId, albumName)
LoveAlbumCore.loadPhotos()
```

### 3. selection.js - 选择功能
**职责**: 管理照片选择相关功能
- 进入/退出选择模式
- 单选、多选、范围选择
- 全选、反选、清空
- 下载、收藏选中照片

**主要接口**:
```javascript
LoveAlbumSelection.enter()
LoveAlbumSelection.exit()
LoveAlbumSelection.selectAll()
LoveAlbumSelection.downloadSelected()
```

### 4. drag.js - 拖动框选
**职责**: 实现拖动框选功能
- 创建选择框
- 处理拖动事件
- 检测照片是否在框内

**主要接口**:
```javascript
LoveAlbumDrag.start(event)
LoveAlbumDrag.move(event)
LoveAlbumDrag.end(event)
```

### 5. main.js - 主控制器
**职责**: 初始化和事件绑定
- 初始化模块
- 绑定所有事件监听器
- 提供全局初始化函数

**主要接口**:
```javascript
LoveAlbum.init()
initLoveAlbum() // 全局函数
```

## 🔄 向后兼容

所有原有的全局函数都已保留，确保现有代码无需修改：

```javascript
// 原有函数仍然可用
loadAlbums()
openAlbum(id, name)
toggleSelectionMode()
selectAllPhotos()
// ... 等等
```

## 📊 优化效果

### 代码结构
- **原始**: 1个文件 751行
- **模块化**: 5个文件，每个100-200行
- **可读性**: ⭐⭐⭐⭐⭐
- **可维护性**: ⭐⭐⭐⭐⭐

### 性能
- 模块按需加载
- 状态管理更清晰
- 内存占用无明显变化

### 开发体验
- ✅ 职责分离清晰
- ✅ 易于调试定位
- ✅ 便于单元测试
- ✅ 支持团队协作

## 🚀 使用方式

### 引入方式（无需修改）
原有的引入方式保持不变：
```html
<script src="Style/js/loveAlbum.js"></script>
```

### 调用方式（无需修改）
```javascript
// 初始化
initLoveAlbum();

// 或使用模块化API
LoveAlbum.init();
```

## 🔧 未来扩展

如需添加新功能，只需创建新模块：

```javascript
// Style/js/loveAlbum/newFeature.js
const LoveAlbumNewFeature = {
    // 新功能实现
};
```

然后在 `loveAlbum.js` 中添加模块路径即可。

## 📝 注意事项

1. 模块之间通过 `LoveAlbumState` 共享状态
2. 所有模块使用命名空间，避免全局污染
3. 保留全局函数别名确保向后兼容
4. 建议逐步迁移到模块化API

## 🔙 回滚方式

如需回滚到原版本：
```bash
mv Style/js/loveAlbum.js Style/js/loveAlbum.js.new
mv Style/js/loveAlbum.js.old Style/js/loveAlbum.js
rm -rf Style/js/loveAlbum/
```
