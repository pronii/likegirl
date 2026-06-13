<?php
/**
 * 相册封面选择器组件
 * 在 albumAdd.php 和 albumMod.php 中复用
 *
 * 使用前请确保表单中存在隐藏字段或文本框：name="album_cover"
 * 引入方式：include_once 'albumCoverPicker.php';
 *
 * 可选变量：
 *   $currentCover - 当前封面URL（修改相册时传入，新增时为空）
 */
if (!isset($currentCover)) {
    $currentCover = '';
}
?>

<style>
    .cover-picker-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    .cover-tab {
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        background: none;
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    .cover-tab:hover {
        color: #667eea;
    }
    .cover-tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
    .cover-tab-content {
        display: none;
    }
    .cover-tab-content.active {
        display: block;
    }
    /* 上传区域 */
    .cover-upload-area {
        background: #f8f9ff;
        border: 2px dashed #667eea;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cover-upload-area:hover {
        background: #fff;
        border-color: #764ba2;
    }
    .cover-upload-area .upload-icon {
        font-size: 36px;
        color: #667eea;
        margin-bottom: 10px;
    }
    #coverFileInput {
        display: none;
    }
    /* 当前封面预览 */
    .cover-current-preview {
        margin-top: 15px;
        text-align: center;
    }
    .cover-current-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .cover-preview-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        color: #6c757d;
    }
    .cover-clear-btn {
        margin-top: 10px;
    }
    /* 图库网格 */
    .cover-library-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 12px;
        max-height: 360px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .cover-library-item {
        position: relative;
        cursor: pointer;
        border-radius: 6px;
        overflow: hidden;
        border: 3px solid transparent;
        transition: all 0.2s;
        aspect-ratio: 1;
    }
    .cover-library-item:hover {
        border-color: #667eea;
        transform: scale(1.03);
    }
    .cover-library-item.selected {
        border-color: #28a745;
    }
    .cover-library-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .cover-library-item .check-mark {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #28a745;
        color: #fff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .cover-library-item.selected .check-mark {
        display: flex;
    }
    .cover-library-loading {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }
    .cover-library-empty {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }
    .cover-upload-status {
        margin-top: 10px;
        font-size: 14px;
    }
    .cover-upload-status.uploading { color: #2196F3; }
    .cover-upload-status.success { color: #28a745; }
    .cover-upload-status.error { color: #dc3545; }
</style>

<div class="form-group mb-3">
    <label>相册封面</label>

    <!-- 隐藏字段：实际提交的封面值 -->
    <input type="hidden" name="album_cover" id="albumCoverInput" value="<?php echo htmlspecialchars($currentCover, ENT_QUOTES); ?>">

    <!-- 选项卡 -->
    <div class="cover-picker-tabs">
        <button type="button" class="cover-tab active" data-tab="url" onclick="switchCoverTab('url')">
            <i class="mdi mdi-link-variant"></i> 输入URL
        </button>
        <button type="button" class="cover-tab" data-tab="upload" onclick="switchCoverTab('upload')">
            <i class="mdi mdi-cloud-upload"></i> 上传图片
        </button>
        <button type="button" class="cover-tab" data-tab="library" onclick="switchCoverTab('library')">
            <i class="mdi mdi-image-multiple"></i> 从图库选择
        </button>
    </div>

    <!-- Tab 1: URL输入 -->
    <div class="cover-tab-content active" id="coverTab-url">
        <input type="text" class="form-control" id="coverUrlInput"
               placeholder="请输入封面图片URL地址（可选）"
               value="<?php echo htmlspecialchars($currentCover, ENT_QUOTES); ?>"
               oninput="setCoverFromUrl(this.value)">
        <small class="form-text text-muted">留空将显示默认封面</small>
    </div>

    <!-- Tab 2: 上传图片 -->
    <div class="cover-tab-content" id="coverTab-upload">
        <div class="cover-upload-area" onclick="document.getElementById('coverFileInput').click()">
            <div class="upload-icon"><i class="mdi mdi-cloud-upload"></i></div>
            <div>点击选择图片上传</div>
            <small class="text-muted">支持 JPG、PNG、GIF、WEBP，最大 10 MB</small>
            <input type="file" id="coverFileInput" accept="image/jpeg,image/png,image/gif,image/webp">
        </div>
        <div class="cover-upload-status" id="coverUploadStatus"></div>
    </div>

    <!-- Tab 3: 从图库选择 -->
    <div class="cover-tab-content" id="coverTab-library">
        <div id="coverLibraryGrid" class="cover-library-grid">
            <div class="cover-library-loading">
                <i class="mdi mdi-loading mdi-spin"></i> 加载中...
            </div>
        </div>
        <div class="text-center mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="coverLoadMoreBtn"
                    onclick="loadMoreLibrary()" style="display:none;">
                加载更多
            </button>
        </div>
    </div>

    <!-- 当前封面预览（所有方式共用） -->
    <div class="cover-current-preview" id="coverPreviewBox" style="<?php echo $currentCover ? '' : 'display:none;'; ?>">
        <span class="cover-preview-label">当前封面预览：</span>
        <img id="coverPreviewImg" src="<?php echo htmlspecialchars($currentCover, ENT_QUOTES); ?>"
             onerror="this.style.opacity=0.3;" alt="封面预览">
        <div>
            <button type="button" class="btn btn-outline-danger btn-sm cover-clear-btn" onclick="clearCover()">
                <i class="mdi mdi-close"></i> 清除封面
            </button>
        </div>
    </div>
</div>

<script>
// ========== 封面选择器逻辑 ==========
let coverLibraryPage = 1;
let coverLibraryLoading = false;
let coverLibraryLoaded = false;

// 切换选项卡
function switchCoverTab(tab) {
    document.querySelectorAll('.cover-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.tab === tab);
    });
    document.querySelectorAll('.cover-tab-content').forEach(c => {
        c.classList.remove('active');
    });
    document.getElementById('coverTab-' + tab).classList.add('active');

    // 首次切换到图库时加载数据
    if (tab === 'library' && !coverLibraryLoaded) {
        loadImageLibrary();
    }
}

// 设置封面值并更新预览
function setCover(url) {
    document.getElementById('albumCoverInput').value = url;
    const previewBox = document.getElementById('coverPreviewBox');
    const previewImg = document.getElementById('coverPreviewImg');

    if (url && url.trim()) {
        previewImg.src = url;
        previewImg.style.opacity = 1;
        previewBox.style.display = 'block';
    } else {
        previewBox.style.display = 'none';
    }
}

// 从URL输入框设置封面
function setCoverFromUrl(url) {
    setCover(url);
    // 同步取消图库选中状态
    document.querySelectorAll('.cover-library-item').forEach(item => {
        item.classList.remove('selected');
    });
}

// 清除封面
function clearCover() {
    setCover('');
    document.getElementById('coverUrlInput').value = '';
    document.getElementById('coverUploadStatus').textContent = '';
    document.querySelectorAll('.cover-library-item').forEach(item => {
        item.classList.remove('selected');
    });
}

// ========== 上传图片 ==========
document.getElementById('coverFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // 验证类型
    if (!file.type.match('image/(jpeg|png|gif|webp)')) {
        showCoverUploadStatus('不支持的文件格式', 'error');
        return;
    }
    // 验证大小
    if (file.size > 10 * 1024 * 1024) {
        showCoverUploadStatus('文件超过 10 MB 限制', 'error');
        return;
    }

    // 上传
    const formData = new FormData();
    formData.append('coverFile', file);

    showCoverUploadStatus('正在上传...', 'uploading');

    $.ajax({
        url: 'albumCoverUploadPost.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showCoverUploadStatus('上传成功！', 'success');
                setCover(response.coverUrl);
                // 同步到URL输入框
                document.getElementById('coverUrlInput').value = response.coverUrl;
            } else {
                showCoverUploadStatus('上传失败：' + response.message, 'error');
            }
        },
        error: function() {
            showCoverUploadStatus('上传失败，请重试', 'error');
        }
    });

    // 清空input，允许重复选择同一文件
    e.target.value = '';
});

function showCoverUploadStatus(message, type) {
    const status = document.getElementById('coverUploadStatus');
    status.textContent = message;
    status.className = 'cover-upload-status ' + type;
}

// ========== 从图库选择 ==========
function loadImageLibrary() {
    coverLibraryPage = 1;
    coverLibraryLoaded = true;
    const grid = document.getElementById('coverLibraryGrid');
    grid.innerHTML = '<div class="cover-library-loading"><i class="mdi mdi-loading mdi-spin"></i> 加载中...</div>';
    fetchLibrary(true);
}

function loadMoreLibrary() {
    coverLibraryPage++;
    fetchLibrary(false);
}

function fetchLibrary(isFirst) {
    if (coverLibraryLoading) return;
    coverLibraryLoading = true;

    $.ajax({
        url: 'getImageLibrary.php',
        type: 'GET',
        data: { page: coverLibraryPage, limit: 24, album_id: -1 },
        dataType: 'json',
        success: function(response) {
            coverLibraryLoading = false;
            const grid = document.getElementById('coverLibraryGrid');

            if (isFirst) {
                grid.innerHTML = '';
            }

            if (response.success && response.data.length > 0) {
                response.data.forEach(img => {
                    const item = document.createElement('div');
                    item.className = 'cover-library-item';
                    const currentCover = document.getElementById('albumCoverInput').value;
                    if (currentCover === img.imgUrl) {
                        item.classList.add('selected');
                    }
                    item.innerHTML = `
                        <img src="${img.thumbUrl}" alt="${img.imgText || ''}" loading="lazy">
                        <div class="check-mark"><i class="mdi mdi-check"></i></div>
                    `;
                    item.onclick = function() {
                        document.querySelectorAll('.cover-library-item').forEach(i => i.classList.remove('selected'));
                        item.classList.add('selected');
                        setCover(img.imgUrl);
                        document.getElementById('coverUrlInput').value = img.imgUrl;
                    };
                    grid.appendChild(item);
                });

                // 加载更多按钮
                document.getElementById('coverLoadMoreBtn').style.display = response.hasMore ? 'inline-block' : 'none';
            } else if (isFirst) {
                grid.innerHTML = '<div class="cover-library-empty"><i class="mdi mdi-image-off"></i><br>图库中暂无图片<br><small>请先到"恋爱相册"上传图片</small></div>';
                document.getElementById('coverLoadMoreBtn').style.display = 'none';
            }
        },
        error: function() {
            coverLibraryLoading = false;
            if (isFirst) {
                document.getElementById('coverLibraryGrid').innerHTML =
                    '<div class="cover-library-empty">加载失败，请重试</div>';
            }
        }
    });
}
</script>
