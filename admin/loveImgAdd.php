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

<style>
.media-preview-container {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.media-preview-container img,
.media-preview-container video {
    max-width: 100%;
    max-height: 400px;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.video-info {
    margin-top: 15px;
    padding: 12px;
    background: #fff;
    border-radius: 6px;
    border-left: 4px solid #667eea;
}
.video-info p {
    margin-bottom: 8px;
}
.video-info p:last-child {
    margin-bottom: 0;
}
.thumbnail-preview {
    margin-top: 15px;
    padding: 12px;
    background: #fff;
    border-radius: 6px;
}
.thumbnail-preview img {
    max-width: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.file-type-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
}
.badge-image {
    background: #d4edda;
    color: #155724;
}
.badge-video {
    background: #d1ecf1;
    color: #0c5460;
}
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">新增图片/视频</h4>
                <form class="needs-validation" id="uploadForm" novalidate>
                    <div class="form-group mb-3">
                        <label>所属相册 <span class="text-muted" style="font-size:12px;">（不选则不在前台显示）</span></label>
                        <select name="album_id" id="album_id" class="form-control">
                            <option value="0">-- 未分类（仅后台可见） --</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['id'] ?>"><?php echo htmlspecialchars($album['album_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>日期 <span class="text-danger">*</span></label>
                        <input class="form-control col-sm-4" id="imgDatd" type="date" name="imgDatd" placeholder="日期" value="<?php echo $inv_date ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>描述 <span class="margin_left badge badge-success-lighten">尽量控制在25个字符以内</span></label>
                        <input name="imgText" id="imgText" type="text" class="form-control" placeholder="请输入描述（可选）" value="">
                    </div>

                    <div class="form-group mb-3">
                        <label>选择文件 <span class="text-danger">*</span> <span class="text-muted" style="font-size:12px;">（图片最大10MB，视频最大100MB）</span></label>
                        <input type="file"
                               name="mediaFile"
                               id="mediaFile"
                               class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/avi,video/x-msvideo,video/quicktime,video/webm,video/x-matroska"
                               required>
                        <small class="form-text text-muted">支持图片格式：JPG、PNG、GIF、WEBP | 视频格式：MP4、AVI、MOV、WebM、MKV</small>
                    </div>

                    <!-- 图片预览区域 -->
                    <div id="imagePreview" class="media-preview-container" style="display:none;">
                        <span class="file-type-badge badge-image">📷 图片文件</span>
                        <div>
                            <img id="previewImg" src="" alt="预览">
                        </div>
                    </div>

                    <!-- 视频预览区域 -->
                    <div id="videoPreview" class="media-preview-container" style="display:none;">
                        <span class="file-type-badge badge-video">🎬 视频文件</span>
                        <div>
                            <video id="previewVideo" controls style="max-width: 100%; max-height: 400px;">
                                <source src="" type="video/mp4">
                                您的浏览器不支持视频播放
                            </video>
                        </div>
                        <div class="video-info">
                            <p><strong>📊 视频信息：</strong></p>
                            <p>⏱️ 时长: <span id="videoDuration" class="text-primary">--</span></p>
                            <p>📦 文件大小: <span id="videoSize" class="text-primary">--</span></p>
                            <p>📐 分辨率: <span id="videoResolution" class="text-primary">--</span></p>
                        </div>
                        <div class="thumbnail-preview" id="thumbnailPreview">
                            <p><strong>🖼️ 自动提取的缩略图：</strong></p>
                            <img id="extractedThumbnail" src="" alt="缩略图">
                        </div>
                    </div>

                    <div class="form-group mb-3 text_right">
                        <button class="btn btn-primary" type="submit" id="submitBtn">
                            <i class="mdi mdi-upload"></i> 上传
                        </button>
                        <a href="loveImgSet.php" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../Style/js/videoThumbnail.js"></script>
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
        if (!file) {
            resetPreview();
            return;
        }

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
            Swal.fire({
                icon: 'error',
                title: '文件过大',
                text: '视频文件不能超过100MB，请选择较小的文件',
                confirmButtonText: '好的'
            });
            document.getElementById('mediaFile').value = '';
            resetPreview();
            return;
        }

        // 显示视频预览容器
        const videoPreview = document.getElementById('videoPreview');
        videoPreview.style.display = 'block';
        document.getElementById('videoDuration').textContent = '提取中...';
        document.getElementById('videoSize').textContent = VideoThumbnail.formatFileSize(file.size);
        document.getElementById('videoResolution').textContent = '提取中...';

        // 显示视频预览
        const video = document.getElementById('previewVideo');
        const videoUrl = URL.createObjectURL(file);
        video.src = videoUrl;

        // 提取缩略图和视频信息
        VideoThumbnail.extract(file, function(data) {
            if (data.error) {
                Swal.fire({
                    icon: 'error',
                    title: '视频处理失败',
                    text: data.error,
                    confirmButtonText: '好的'
                });
                document.getElementById('mediaFile').value = '';
                videoPreview.style.display = 'none';
                URL.revokeObjectURL(videoUrl);
                resetPreview();
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
        // 验证大小
        if (file.size > 10 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: '文件过大',
                text: '图片文件不能超过10MB，请选择较小的文件',
                confirmButtonText: '好的'
            });
            document.getElementById('mediaFile').value = '';
            resetPreview();
            return;
        }

        // 显示图片预览
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

    function resetPreview() {
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('videoPreview').style.display = 'none';
        uploadData = {
            file: null,
            type: null,
            thumbnail: null,
            duration: null,
            size: null
        };
    }

    // 表单提交处理
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // 验证文件
        if (!uploadData.file) {
            Swal.fire({
                icon: 'warning',
                title: '请选择文件',
                text: '请先选择要上传的图片或视频文件',
                confirmButtonText: '好的'
            });
            return;
        }

        // 获取表单数据
        const albumId = document.getElementById('album_id').value;
        const imgDatd = document.getElementById('imgDatd').value;
        const imgText = document.getElementById('imgText').value.trim();

        // 验证日期
        if (!imgDatd) {
            Swal.fire({
                icon: 'warning',
                title: '请选择日期',
                confirmButtonText: '好的'
            });
            document.getElementById('imgDatd').focus();
            return;
        }

        // 构建FormData
        const formData = new FormData();
        formData.append('file', uploadData.file);
        formData.append('album_id', albumId);
        formData.append('imgDatd', imgDatd);
        formData.append('imgText', imgText || '');

        // 如果是视频，添加额外信息
        if (uploadData.type === 'video') {
            formData.append('thumbnail', uploadData.thumbnail);
            formData.append('duration', uploadData.duration);
        }

        // 禁用提交按钮
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> 上传中...';

        // AJAX上传
        fetch('ImgAddPost.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.code === 200) {
                Swal.fire({
                    icon: 'success',
                    title: '上传成功',
                    text: result.message || '文件已成功上传',
                    confirmButtonText: '好的'
                }).then(() => {
                    window.location.href = 'loveImgSet.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '上传失败',
                    text: result.message || '未知错误',
                    confirmButtonText: '好的'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('上传错误:', error);
            Swal.fire({
                icon: 'error',
                title: '上传出错',
                text: '网络错误或服务器无响应',
                confirmButtonText: '好的'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
})();
</script>

<?php
include_once 'Footer.php';
?>
</body>
</html>