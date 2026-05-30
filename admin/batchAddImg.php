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
    .img-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        position: relative;
        transition: all 0.3s ease;
    }
    .img-item:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    .img-item .remove-btn {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #dc3545;
        cursor: pointer;
        font-size: 20px;
        padding: 5px;
    }
    .img-item .remove-btn:hover {
        color: #a71d2a;
    }
    .img-preview {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        margin-right: 15px;
        float: left;
    }
    .img-input-area {
        margin-left: 80px;
    }
    .btn-add-img {
        width: 100%;
        border-style: dashed;
        border-width: 2px;
        padding: 12px;
    }
    .batch-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .batch-header h4 {
        margin: 0;
        color: white;
    }
    .batch-info {
        margin-top: 10px;
        font-size: 14px;
        opacity: 0.9;
    }
    .image-count {
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        display: inline-block;
        margin-left: 10px;
    }
    .quick-paste-section {
        background: #f8f9ff;
        border: 2px dashed #667eea;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 0;
        margin-top: 15px;
    }
    .quick-paste-header h5 {
        color: #667eea;
        margin-bottom: 5px;
        font-size: 16px;
    }
    .quick-paste-tip {
        color: #6c757d;
        font-size: 13px;
    }
    .bulk-textarea {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        resize: vertical;
        border: 2px solid #dee2e6;
        transition: border-color 0.3s;
    }
    .bulk-textarea:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    }
    .quick-paste-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .parse-result {
        font-size: 14px;
        font-weight: 500;
        padding: 5px 15px;
        border-radius: 4px;
        display: inline-block;
    }
    .parse-result.success {
        color: #155724;
        background: #d4edda;
    }
    .parse-result.error {
        color: #721c24;
        background: #f8d7da;
    }
    .section-divider {
        border-top: 2px solid #e9ecef;
        margin: 25px 0;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="batch-header">
                    <h4><i class="mdi mdi-upload-multiple"></i> 批量新增图片</h4>
                    <div class="batch-info">
                        可一次性添加多张图片到相册，所有图片将使用相同的日期和相册设置
                        <span class="image-count" id="imgCount">当前图片数：0 张</span>
                    </div>
                </div>

                <form class="needs-validation" action="batchAddImgPost.php" method="post" onsubmit="return check()" novalidate>

                    <!-- 快速批量粘贴区域 -->
                    <div class="quick-paste-section">
                        <div class="quick-paste-header">
                            <h5><i class="mdi mdi-lightning-bolt"></i> 快速批量粘贴</h5>
                            <span class="quick-paste-tip">粘贴多个图片URL，用空格、换行或分号分隔，自动添加到下方列表</span>
                        </div>
                        <div class="form-group mb-2">
                            <textarea id="bulkUrls" class="form-control bulk-textarea" rows="4" 
                                placeholder="在此粘贴图片URL，例如：&#10;https://example.com/1.png&#10;https://example.com/2.png&#10;https://example.com/3.png&#10;&#10;支持格式：空格分隔、换行分隔、分号分隔，或混合使用"></textarea>
                        </div>
                        <div class="quick-paste-actions">
                            <button type="button" class="btn btn-warning" onclick="parseBulkUrls()">
                                <i class="mdi mdi-auto-fix"></i> 解析并添加图片
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearBulkUrls()">
                                <i class="mdi mdi-eraser"></i> 清空输入
                            </button>
                            <span id="parseResult" class="parse-result"></span>
                        </div>
                    </div>

                    <hr class="section-divider">

                    <!-- 公共设置 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>所属相册 <span class="text-muted" style="font-size:12px;">（不选则不在前台显示）</span></label>
                                <select name="album_id" class="form-control">
                                    <option value="0">-- 未分类（仅后台可见） --</option>
                                    <?php foreach ($albums as $album): ?>
                                        <option value="<?php echo $album['id'] ?>"><?php echo $album['album_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>日期 <span class="text-danger">*</span></label>
                                <input class="form-control" id="batch-date" type="date" name="imgDatd" placeholder="日期" value="<?php echo $inv_date ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- 图片列表 -->
                    <div class="form-group mb-3">
                        <label>图片列表 <span class="text-danger">*</span></label>
                        <div id="imgList">
                            <!-- 图片项会动态添加到这里 -->
                        </div>
                        <button type="button" class="btn btn-outline-info btn-add-img" onclick="addImgItem()">
                            <i class="mdi mdi-plus-circle"></i> 添加一张图片
                        </button>
                    </div>

                    <div class="form-group mb-3 text_right">
                        <button class="btn btn-primary" type="submit">
                            <i class="mdi mdi-content-save"></i> 保存所有图片
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

<script>
    let imgIndex = 0;

    // 页面加载时添加第一张图片
    window.onload = function() {
        addImgItem();
    };

    function addImgItem() {
        imgIndex++;
        const imgList = document.getElementById('imgList');
        const imgItem = document.createElement('div');
        imgItem.className = 'img-item';
        imgItem.id = 'img-item-' + imgIndex;
        imgItem.innerHTML = `
            <i class="mdi mdi-close-circle remove-btn" onclick="removeImgItem(${imgIndex})"></i>
            <img src="" class="img-preview" id="preview-${imgIndex}" style="display:none;" onclick="previewFull('${imgIndex}')">
            <div class="img-input-area">
                <div class="form-group mb-2">
                    <label>图片URL <span class="text-danger">*</span></label>
                    <input type="text" name="imgUrl_${imgIndex}" class="form-control" placeholder="请输入图片URL地址" 
                           oninput="updatePreview(${imgIndex}, this.value)" required>
                </div>
                <div class="form-group mb-2">
                    <label>图片描述 <span class="text-danger">*</span> <span class="text-muted" style="font-size:12px;">尽量控制在25个字符以内</span></label>
                    <input type="text" name="imgText_${imgIndex}" class="form-control" placeholder="请输入图片描述" required>
                </div>
            </div>
        `;
        imgList.appendChild(imgItem);
        updateImgCount();
    }

    function removeImgItem(index) {
        const imgItem = document.getElementById('img-item-' + index);
        if (imgItem) {
            imgItem.remove();
            updateImgCount();
        }
    }

    function updatePreview(index, url) {
        const preview = document.getElementById('preview-' + index);
        if (url && url.length > 0) {
            preview.src = url;
            preview.style.display = 'block';
            preview.onerror = function() {
                this.style.display = 'none';
            };
        } else {
            preview.style.display = 'none';
        }
    }

    function previewFull(index) {
        const url = document.getElementsByName('imgUrl_' + index)[0].value;
        if (url) {
            window.open(url, '_blank');
        }
    }

    function updateImgCount() {
        const count = document.querySelectorAll('.img-item').length;
        document.getElementById('imgCount').textContent = '当前图片数：' + count + ' 张';
        
        // 如果没有图片了，自动添加一张
        if (count === 0) {
            addImgItem();
        }
    }

    // ========== 批量粘贴解析功能 ==========
    function parseBulkUrls() {
        const textarea = document.getElementById('bulkUrls');
        const text = textarea.value.trim();
        
        if (!text) {
            showResult('请输入图片URL', 'error');
            return;
        }

        // 支持空格、换行、分号、逗号分隔
        const urls = text.split(/[\s;\n\r,]+/)
            .map(url => url.trim())
            .filter(url => url.length > 0 && url.startsWith('http'));
        
        if (urls.length === 0) {
            showResult('未找到有效的图片URL（必须以 http 开头）', 'error');
            return;
        }

        // 逐个添加图片项
        urls.forEach(url => {
            addImgItemWithUrl(url);
        });
        
        showResult('成功添加 ' + urls.length + ' 张图片！请填写图片描述', 'success');
        
        // 滚动到图片列表
        document.getElementById('imgList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function addImgItemWithUrl(url) {
        imgIndex++;
        const imgList = document.getElementById('imgList');
        const imgItem = document.createElement('div');
        imgItem.className = 'img-item';
        imgItem.id = 'img-item-' + imgIndex;
        const currentIdx = imgIndex;
        imgItem.innerHTML = `
            <i class="mdi mdi-close-circle remove-btn" onclick="removeImgItem(${currentIdx})"></i>
            <img src="${url}" class="img-preview" id="preview-${currentIdx}" onclick="previewFull('${currentIdx}')" onerror="this.style.display='none'">
            <div class="img-input-area">
                <div class="form-group mb-2">
                    <label>图片URL <span class="text-danger">*</span></label>
                    <input type="text" name="imgUrl_${currentIdx}" class="form-control" value="${url}" 
                           oninput="updatePreview(${currentIdx}, this.value)" required>
                </div>
                <div class="form-group mb-2">
                    <label>图片描述 <span class="text-danger">*</span> <span class="text-muted" style="font-size:12px;">尽量控制在25个字符以内</span></label>
                    <input type="text" name="imgText_${currentIdx}" class="form-control" placeholder="请输入图片描述" required>
                </div>
            </div>
        `;
        imgList.appendChild(imgItem);
        updateImgCount();
    }

    function clearBulkUrls() {
        document.getElementById('bulkUrls').value = '';
        showResult('', '');
    }

    function showResult(message, type) {
        const resultSpan = document.getElementById('parseResult');
        resultSpan.textContent = message;
        resultSpan.className = 'parse-result' + (type ? ' ' + type : '');
    }

    function check() {
        // 检查日期
        const date = document.getElementsByName('imgDatd')[0].value;
        if (!date) {
            alert("请选择日期");
            return false;
        }

        // 检查图片列表
        const imgItems = document.querySelectorAll('.img-item');
        if (imgItems.length === 0) {
            alert("请至少添加一张图片");
            return false;
        }

        // 检查每张图片的URL和描述
        for (let i = 0; i < imgItems.length; i++) {
            const item = imgItems[i];
            const urlInput = item.querySelector('input[name^="imgUrl"]');
            const textInput = item.querySelector('input[name^="imgText"]');
            
            if (!urlInput.value.trim()) {
                alert("第 " + (i + 1) + " 张图片的URL不能为空");
                urlInput.focus();
                return false;
            }
            
            if (!textInput.value.trim()) {
                alert("第 " + (i + 1) + " 张图片的描述不能为空");
                textInput.focus();
                return false;
            }
        }

        // 二次确认
        const confirmMsg = '您确认要添加 ' + imgItems.length + ' 张图片吗？';
        if (!confirm(confirmMsg)) {
            return false;
        }

        return true;
    }
</script>

<?php
include_once 'Footer.php';
?>
</body>
</html>