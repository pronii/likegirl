<?php
session_start();
include_once 'Nav.php';
$inv_date = date("Y-m-d");

// 获取所有相册
$albumSql = "SELECT * FROM love_album ORDER BY sort_order ASC, id ASC";
$albumRes = mysqli_query($connect, $albumSql);
$albums = [];
while ($row = mysqli_fetch_array($albumRes)) {
    $albums[] = $row;
}
?>

<link href="assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>

<style>
    .batch-upload-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .batch-upload-header h4 {
        margin: 0 0 10px 0;
        color: white;
        font-size: 22px;
    }
    .batch-upload-header .subtitle {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
    }
    .file-selector-area {
        background: #f8f9ff;
        border: 3px dashed #667eea;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        margin-bottom: 25px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .file-selector-area:hover {
        background: #fff;
        border-color: #764ba2;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }
    .file-selector-area.has-files {
        border-color: #28a745;
        background: #f1f9f4;
    }
    .file-selector-icon {
        font-size: 48px;
        color: #667eea;
        margin-bottom: 15px;
    }
    .file-selector-area.has-files .file-selector-icon {
        color: #28a745;
    }
    #fileInput {
        display: none;
    }
    .file-count-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.3);
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        margin-top: 10px;
    }
    .file-item {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
    }
    .file-item:hover {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    .file-item.uploading {
        background: #e7f3ff;
        border-color: #2196F3;
    }
    .file-item.success {
        background: #f1f9f4;
        border-color: #28a745;
    }
    .file-item.error {
        background: #fff5f5;
        border-color: #dc3545;
    }
    .file-preview {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #dee2e6;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .file-info {
        flex: 1;
        min-width: 0;
    }
    .file-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-size {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .file-desc-input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 14px;
    }
    .file-desc-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    }
    .file-status {
        width: 100px;
        text-align: center;
        flex-shrink: 0;
        margin-left: 15px;
    }
    .status-icon {
        font-size: 32px;
        margin-bottom: 5px;
    }
    .status-text {
        font-size: 12px;
        font-weight: 600;
    }
    .status-waiting .status-icon { color: #6c757d; }
    .status-uploading .status-icon { color: #2196F3; }
    .status-success .status-icon { color: #28a745; }
    .status-error .status-icon { color: #dc3545; }

    .btn-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #dc3545;
        cursor: pointer;
        font-size: 24px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }
    .btn-remove:hover {
        background: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }
    .progress-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
        display: none;
    }
    .progress-section.active {
        display: block;
    }
    .progress {
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
    }
    .progress-bar {
        font-size: 14px;
        font-weight: 600;
        line-height: 30px;
        transition: width 0.3s ease;
    }
    .progress-text {
        margin-bottom: 10px;
        font-size: 15px;
        color: #333;
    }
    .result-summary {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
        display: none;
    }
    .result-summary.active {
        display: block;
    }
    .result-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }
    .stat-item {
        flex: 1;
        text-align: center;
        padding: 15px;
        border-radius: 8px;
    }
    .stat-item.success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
    }
    .stat-item.error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 14px;
        color: #666;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .uploading .status-icon {
        animation: spin 1s linear infinite;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <!-- 页面标题 -->
                <div class="batch-upload-header">
                    <h4><i class="mdi mdi-cloud-upload"></i> 批量本地文件上传</h4>
                    <p class="subtitle">一次性选择多个图片或视频文件，自动处理并上传到服务器</p>
                    <span class="file-count-badge" id="fileCountBadge">已选择 0 个文件</span>
                </div>

                <!-- 文件选择区域 -->
                <div class="file-selector-area" id="fileSelectorArea" onclick="document.getElementById('fileInput').click()">
                    <div class="file-selector-icon">
                        <i class="mdi mdi-folder-multiple-image"></i>
                    </div>
                    <h5>点击选择图片或视频文件</h5>
                    <p class="text-muted mb-2">图片支持 JPG、PNG、GIF、WEBP 格式，最大 10 MB</p>
                    <p class="text-muted mb-0">视频支持 MP4、MOV、AVI 格式，最大 100 MB</p>
                    <input type="file" id="fileInput" multiple accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo">
                </div>

                <!-- 公共设置 -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>所属相册 <span class="text-muted" style="font-size:12px;">（不选则不在前台显示）</span></label>
                        <select name="album_id" id="albumSelect" class="form-control">
                            <option value="0">-- 未分类（仅后台可见） --</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['id'] ?>"><?php echo htmlspecialchars($album['album_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>日期 <span class="text-danger">*</span></label>
                        <input class="form-control" id="dateInput" type="date" value="<?php echo $inv_date ?>" required>
                    </div>
                </div>

                <!-- 文件列表 -->
                <div id="fileListContainer" style="display:none;">
                    <h5 class="mb-3"><i class="mdi mdi-file-image"></i> 文件列表</h5>
                    <div id="fileList"></div>
                </div>

                <!-- 上传进度 -->
                <div class="progress-section" id="progressSection">
                    <div class="progress-text" id="progressText">准备上传...</div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             id="progressBar" role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>

                <!-- 结果摘要 -->
                <div class="result-summary" id="resultSummary">
                    <h5 class="mb-3"><i class="mdi mdi-check-circle"></i> 上传完成</h5>
                    <div class="result-stats">
                        <div class="stat-item success">
                            <div class="stat-number" id="successCount">0</div>
                            <div class="stat-label">成功</div>
                        </div>
                        <div class="stat-item error">
                            <div class="stat-number" id="errorCount">0</div>
                            <div class="stat-label">失败</div>
                        </div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="text-right mt-3">
                    <button class="btn btn-primary btn-lg" id="uploadBtn" onclick="startUpload()" disabled>
                        <i class="mdi mdi-rocket-launch"></i> 开始上传
                    </button>
                    <a href="loveImgSet.php" class="btn btn-secondary btn-lg">
                        <i class="mdi mdi-arrow-left"></i> 返回列表
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../Style/js/videoThumbnail.js"></script>
<script>
let selectedFiles = [];
let uploadedCount = 0;
let isUploading = false;

// 文件选择处理
document.getElementById('fileInput').addEventListener('change', async function(e) {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;

    // 显示详细的处理提示
    const container = document.getElementById('fileList');
    container.innerHTML = `
        <div class="text-center py-4" id="processingStatus">
            <div class="mb-3">
                <i class="mdi mdi-video-image mdi-spin" style="font-size: 48px; color: #667eea;"></i>
            </div>
            <h5 class="mb-3">正在处理文件</h5>
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     id="processProgressBar"
                     role="progressbar"
                     style="width: 0%; font-size: 16px; line-height: 30px;">
                    0%
                </div>
            </div>
            <div class="text-muted">
                <span id="processCurrentFile">准备中...</span>
                <br>
                <small>已处理: <strong id="processedCount">0</strong> / <strong id="totalCount">0</strong></small>
            </div>
        </div>
    `;
    document.getElementById('fileListContainer').style.display = 'block';

    // 设置总数
    document.getElementById('totalCount').textContent = files.length;

    // 添加文件到列表
    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // 更新当前处理的文件名
        const currentFileEl = document.getElementById('processCurrentFile');
        if (currentFileEl) {
            currentFileEl.innerHTML = `正在处理: <strong>${file.name}</strong>`;
        }

        const fileType = file.type.startsWith('video/') ? 'video' : 'image';

        // 验证文件类型
        if (fileType === 'image') {
            if (!file.type.match('image/(jpeg|png|gif|webp)')) {
                alert(`文件 ${file.name} 不是支持的图片格式`);
                continue;
            }
            // 验证图片大小
            if (file.size > 10 * 1024 * 1024) {
                alert(`图片 ${file.name} 超过 10 MB 限制`);
                continue;
            }
        } else if (fileType === 'video') {
            if (!file.type.match('video/(mp4|quicktime|x-msvideo)')) {
                alert(`文件 ${file.name} 不是支持的视频格式`);
                continue;
            }
            // 验证视频大小
            if (file.size > 100 * 1024 * 1024) {
                alert(`视频 ${file.name} 超过 100 MB 限制`);
                continue;
            }
        } else {
            alert(`文件 ${file.name} 类型不支持`);
            continue;
        }

        const fileItem = {
            file: file,
            id: Date.now() + Math.random(),
            type: fileType,
            status: 'waiting',
            message: '',
            thumbnail: null,
            duration: null
        };

        // 如果是视频，提取缩略图和时长
        if (fileType === 'video') {
            // 更新状态：正在提取
            if (currentFileEl) {
                currentFileEl.innerHTML = `正在提取视频缩略图: <strong>${file.name}</strong>`;
            }

            await new Promise((resolve) => {
                VideoThumbnail.extract(file, function(data) {
                    if (data.error) {
                        fileItem.status = 'error';
                        fileItem.message = data.error;
                    } else {
                        fileItem.thumbnail = data.thumbnail;
                        fileItem.duration = data.duration;
                    }
                    resolve();
                });
            });
        }

        selectedFiles.push(fileItem);

        // 更新进度
        const processed = i + 1;
        const percent = Math.round((processed / files.length) * 100);
        const progressBar = document.getElementById('processProgressBar');
        const processedCountEl = document.getElementById('processedCount');

        if (progressBar) {
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
        }
        if (processedCountEl) {
            processedCountEl.textContent = processed;
        }
    }

    // 处理完成，显示短暂的完成提示
    const processingStatus = document.getElementById('processingStatus');
    if (processingStatus) {
        processingStatus.innerHTML = `
            <div class="text-center py-3">
                <i class="mdi mdi-check-circle" style="font-size: 48px; color: #28a745;"></i>
                <h5 class="mt-2 mb-0">文件处理完成！</h5>
                <small class="text-muted">共处理 ${files.length} 个文件</small>
            </div>
        `;

        // 1秒后显示文件列表
        setTimeout(() => {
            renderFileList();
            updateUI();
        }, 1000);
    } else {
        renderFileList();
        updateUI();
    }
});

// 渲染文件列表
function renderFileList() {
    const container = document.getElementById('fileList');
    container.innerHTML = '';

    selectedFiles.forEach((item, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = `file-item ${item.status}`;
        fileItem.id = `file-${item.id}`;

        // 生成预览图
        if (item.type === 'image') {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = fileItem.querySelector('.file-preview');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(item.file);
        } else if (item.type === 'video' && item.thumbnail) {
            // 使用提取的视频缩略图
            setTimeout(() => {
                const img = fileItem.querySelector('.file-preview');
                if (img) img.src = item.thumbnail;
            }, 0);
        }

        // 状态图标
        let statusIcon = '';
        let statusText = '';
        let statusClass = 'status-waiting';

        switch(item.status) {
            case 'waiting':
                statusIcon = '<i class="mdi mdi-clock-outline"></i>';
                statusText = '等待中';
                statusClass = 'status-waiting';
                break;
            case 'uploading':
                statusIcon = '<i class="mdi mdi-loading"></i>';
                statusText = '上传中';
                statusClass = 'status-uploading';
                break;
            case 'success':
                statusIcon = '<i class="mdi mdi-check-circle"></i>';
                statusText = '成功';
                statusClass = 'status-success';
                break;
            case 'error':
                statusIcon = '<i class="mdi mdi-close-circle"></i>';
                statusText = item.message || '失败';
                statusClass = 'status-error';
                break;
        }

        // 文件类型图标和信息
        const typeIcon = item.type === 'video' ? 'mdi-video' : 'mdi-image';
        const typeText = item.type === 'video' ? '视频' : '图片';
        const durationText = item.type === 'video' && item.duration ?
            ` · ${VideoThumbnail.formatDuration(item.duration)}` : '';

        fileItem.innerHTML = `
            ${item.status === 'waiting' ? `<i class="mdi mdi-close-circle btn-remove" onclick="removeFile(${index})"></i>` : ''}
            <img src="" class="file-preview" alt="预览">
            <div class="file-info">
                <div class="file-name">
                    <i class="mdi ${typeIcon} mr-1"></i>${item.file.name}
                </div>
                <div class="file-size">${typeText} · ${formatFileSize(item.file.size)}${durationText}</div>
                <input type="text" class="file-desc-input" placeholder="${item.type === 'video' ? '视频' : '图片'}描述（可选）"
                       id="desc-${item.id}" ${item.status !== 'waiting' ? 'disabled' : ''}>
            </div>
            <div class="file-status ${statusClass}">
                <div class="status-icon">${statusIcon}</div>
                <div class="status-text">${statusText}</div>
            </div>
        `;

        container.appendChild(fileItem);
    });

    document.getElementById('fileListContainer').style.display = selectedFiles.length > 0 ? 'block' : 'none';
}

// 移除文件
function removeFile(index) {
    if (isUploading) {
        alert('上传进行中，无法移除文件');
        return;
    }
    selectedFiles.splice(index, 1);
    renderFileList();
    updateUI();
}

// 更新UI状态
function updateUI() {
    const count = selectedFiles.length;
    document.getElementById('fileCountBadge').textContent = `已选择 ${count} 个文件`;
    document.getElementById('uploadBtn').disabled = count === 0 || isUploading;

    const selectorArea = document.getElementById('fileSelectorArea');
    if (count > 0) {
        selectorArea.classList.add('has-files');
    } else {
        selectorArea.classList.remove('has-files');
    }
}

// 格式化文件大小
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1024 / 1024).toFixed(2) + ' MB';
}

// 开始上传
async function startUpload() {
    if (selectedFiles.length === 0) {
        alert('请先选择文件');
        return;
    }

    const date = document.getElementById('dateInput').value;
    if (!date) {
        alert('请选择日期');
        document.getElementById('dateInput').focus();
        return;
    }

    if (!confirm(`确认上传 ${selectedFiles.length} 个文件吗？`)) {
        return;
    }

    isUploading = true;
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('progressSection').classList.add('active');
    document.getElementById('resultSummary').classList.remove('active');

    const album_id = document.getElementById('albumSelect').value;
    let successCount = 0;
    let errorCount = 0;

    for (let i = 0; i < selectedFiles.length; i++) {
        const item = selectedFiles[i];
        const progress = Math.round(((i) / selectedFiles.length) * 100);

        // 更新整体进度
        document.getElementById('progressText').textContent = `正在上传第 ${i + 1}/${selectedFiles.length} 个文件...`;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressBar').textContent = progress + '%';

        // 更新文件状态为上传中
        item.status = 'uploading';
        renderFileList();

        // 获取描述
        const descInput = document.getElementById(`desc-${item.id}`);
        const imgText = descInput ? descInput.value : '';

        // 上传文件
        try {
            const result = await uploadFile(item.file, album_id, date, imgText, item);
            if (result.success) {
                item.status = 'success';
                item.message = '';
                successCount++;
            } else {
                item.status = 'error';
                item.message = result.message;
                errorCount++;
            }
        } catch (error) {
            item.status = 'error';
            item.message = '网络错误';
            errorCount++;
        }

        renderFileList();
    }

    // 完成
    document.getElementById('progressBar').style.width = '100%';
    document.getElementById('progressBar').textContent = '100%';
    document.getElementById('progressText').textContent = '上传完成！';

    // 显示结果
    document.getElementById('successCount').textContent = successCount;
    document.getElementById('errorCount').textContent = errorCount;
    document.getElementById('resultSummary').classList.add('active');

    isUploading = false;

    // 显示提示
    if (errorCount === 0) {
        alert(`成功上传 ${successCount} 个文件！`);
        setTimeout(() => {
            window.location.href = 'loveImgSet.php';
        }, 2000);
    } else {
        alert(`上传完成：成功 ${successCount} 个，失败 ${errorCount} 个`);
    }
}

// 上传单个文件
function uploadFile(file, album_id, imgDatd, imgText, item) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();

        if (item.type === 'video') {
            // 视频文件使用批量上传接口
            formData.append('files[]', file);
            formData.append('album_id', album_id);
            formData.append('imgDatd', imgDatd);

            // 添加视频缩略图和时长
            if (item.thumbnail) {
                formData.append('thumbnail_0', item.thumbnail);
            }
            if (item.duration) {
                formData.append('duration_0', item.duration);
            }

            // 视频描述
            if (imgText) {
                formData.append('imgText_0', imgText);
            }
        } else {
            // 图片文件使用原来的接口
            formData.append('imageFile', file);
            formData.append('album_id', album_id);
            formData.append('imgDatd', imgDatd);
            formData.append('imgText', imgText);
        }

        $.ajax({
            url: 'batchUploadLocalPost.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 300000, // 5分钟超时（300秒 = 300000毫秒）
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error('上传失败:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText
                });

                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch(e) {
                    // 提供更详细的错误信息
                    if (status === 'timeout') {
                        reject(new Error('上传超时，请尝试较小的文件'));
                    } else if (xhr.status === 0) {
                        reject(new Error('网络连接失败或请求被取消'));
                    } else if (xhr.status === 500) {
                        reject(new Error('服务器错误 (500): ' + (xhr.responseText || '未知错误')));
                    } else {
                        reject(new Error('网络错误: ' + xhr.status));
                    }
                }
            }
        });
    });
}
</script>

<?php
include_once 'Footer.php';
?>
</body>
</html>
