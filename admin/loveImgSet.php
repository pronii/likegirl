<?php
session_start();
include_once 'Nav.php';
include_once 'Function.php';

// 确保使用 UTF-8 编码
mysqli_set_charset($connect, "utf8mb4");

// 关联查询获取相册名称
$loveImg = "SELECT li.*, la.album_name FROM loveImg li
            LEFT JOIN love_album la ON li.album_id = la.id
            ORDER BY li.id desc";
$resImg = mysqli_query($connect, $loveImg);
$albumRes = mysqli_query($connect, "SELECT id, album_name FROM love_album ORDER BY sort_order ASC");
?>

<link href="assets/css/selection-manager.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="../Style/css/video.css">

<style>
    .img-thumbnail {
        width: 120px;
        height: 90px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .img-thumbnail:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .album-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .album-none {
        background: #e9ecef;
        color: #6c757d;
    }
    .row-selected {
        background-color: #e3f2fd !important;
    }
    .row-selected td {
        background-color: #e3f2fd !important;
    }
    #albumFilter, #typeFilter {
        transition: border-color 0.3s ease;
    }
    #albumFilter:focus, #typeFilter:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .photo-checkbox, #selectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .drag-selecting {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    tbody tr {
        cursor: pointer;
    }
    .video-preview-thumb {
        position: relative;
        width: 120px;
        height: 90px;
        display: inline-block;
        cursor: pointer;
    }
    .video-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .video-preview-thumb .mdi-play-circle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 32px;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        pointer-events: none;
    }
    .video-preview-thumb .duration-badge {
        position: absolute;
        bottom: 4px;
        right: 4px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 10px;
        }
        .header-title {
            font-size: 16px !important;
        }
        .header-title .btn {
            font-size: 11px;
            padding: 4px 8px;
        }
        .header-title .btn i {
            margin-right: 2px !important;
        }
        .img-thumbnail {
            width: 80px;
            height: 60px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #basic-datatable {
            font-size: 12px;
            min-width: 500px;
        }
        #basic-datatable th, #basic-datatable td {
            padding: 6px 4px;
        }
        #basic-datatable th:nth-child(2),
        #basic-datatable td:nth-child(2) {
            display: none;
        }
        #basic-datatable td:nth-child(5) {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .album-badge {
            font-size: 10px;
            padding: 2px 4px;
        }
        #basic-datatable td:last-child {
            min-width: 90px;
        }
        #basic-datatable td:last-child .btn {
            font-size: 11px;
            padding: 4px 8px;
            margin: 2px 0;
            display: block;
            width: 100%;
        }
        #basic-datatable td:last-child .btn i {
            margin-right: 2px !important;
        }
        .floating-batch-bar {
            bottom: 60px;
        }
        .batch-bar-content {
            flex-direction: column;
            gap: 8px;
            padding: 8px;
        }
        .batch-info {
            font-size: 12px;
        }
        .batch-actions {
            width: 100%;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .batch-actions .btn {
            font-size: 10px;
            padding: 5px 8px;
            margin: 2px;
        }
        #albumFilter {
            width: 100% !important;
            min-width: auto !important;
            margin-top: 8px;
            font-size: 13px;
        }
        .mb-3 label {
            font-size: 13px;
        }
    }
</style>

<div id="floatingCounter" class="floating-counter" style="display: none;">
    <i class="mdi mdi-checkbox-marked-circle-outline"></i>
    <span id="floatingCount">0</span> 张已选
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">恋爱相册
                    <a href="/admin/batchAddImg.php">
                        <button type="button" class="btn btn-info btn-sm right_10">
                            <i class="mdi mdi-upload-multiple"></i>批量新增
                        </button>
                    </a>
                    <a href="/admin/loveImgAdd.php">
                        <button type="button" class="btn btn-success btn-sm right_10">
                            <i class="mdi mdi-circle-edit-outline"></i>单张新增
                        </button>
                    </a>
                </h4>

                <div class="mb-3">
                    <label class="mr-2"><i class="mdi mdi-filter-outline"></i> 按相册筛选照片</label>
                    <select id="albumFilter" class="form-control form-control-sm" style="display:inline-block;width:auto;min-width:200px;">
                        <option value="">全部相册</option>
                        <?php
                        while ($album = mysqli_fetch_assoc($albumRes)) {
                            echo "<option value='" . $album['id'] . "'>" . $album['album_name'] . "</option>";
                        }
                        ?>
                        <option value="null">未分类</option>
                    </select>
                    <span class="ml-2 text-muted" id="filterInfo"></span>
                </div>

                <div class="mb-3">
                    <label class="mr-2"><i class="mdi mdi-filter-outline"></i> 按类型筛选</label>
                    <select id="typeFilter" class="form-control form-control-sm"
                            style="display:inline-block;width:auto;min-width:150px;">
                        <option value="">全部类型</option>
                        <option value="image">仅图片</option>
                        <option value="video">仅视频</option>
                    </select>
                </div>

                <div id="batchActionPanel" class="floating-batch-bar" style="display: none;">
                    <div class="batch-bar-content">
                        <div class="batch-info">
                            <i class="mdi mdi-checkbox-multiple-marked"></i>
                            <span>已选 <strong id="selectedCount">0</strong> 张</span>
                            <button type="button" class="btn btn-outline-light btn-sm ml-2" data-action="preview">
                                <i class="mdi mdi-eye-outline"></i> 预览
                            </button>
                        </div>
                        <div class="batch-actions">
                            <button type="button" class="btn btn-primary btn-sm" data-action="transfer">
                                <i class="mdi mdi-folder-move"></i> 转移相册
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-action="delete">
                                <i class="mdi mdi-delete-multiple"></i> 批量删除
                            </button>
                            <button type="button" class="btn btn-light btn-sm batch-close" data-action="cancel">
                                <i class="mdi mdi-close"></i> 取消
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                <table id="basic-datatable" class="table dt-responsive nowrap" width="100%">
                    <thead>
                    <tr>
                        <th width="40px"><input type="checkbox" id="selectAll"></th>
                        <th>序号</th>
                        <th>预览</th>
                        <th>类型</th>
                        <th>描述</th>
                        <th>所属相册</th>
                        <th>日期</th>
                        <th style="width:150px;">操作</th>
                    </tr>
                    </thead>
                    <tbody id="photoTbody">
                    <?php
                    $SerialNumber = 0;
                    while ($list = mysqli_fetch_array($resImg)) {
                        $SerialNumber++;
                        $albumName = $list['album_name'] ? $list['album_name'] : '未分类';
                        $albumClass = $list['album_name'] ? 'album-badge' : 'album-badge album-none';
                        $isVideo = ($list['media_type'] ?? 'image') === 'video';
                        ?>
                        <tr id="row-<?php echo $list['id']; ?>"
                            data-photo-id="<?php echo $list['id']; ?>"
                            data-album-id="<?php echo (int)($list['album_id'] ?? 0); ?>"
                            data-album-name="<?php echo htmlspecialchars($albumName, ENT_QUOTES, 'UTF-8'); ?>"
                            data-type="<?php echo $list['media_type'] ?? 'image'; ?>">
                            <td>
                                <input type="checkbox" class="photo-checkbox" value="<?php echo $list['id']; ?>" data-checkbox="photo">
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
                                        <?php if (!empty($list['video_duration'])): ?>
                                        <span class="duration-badge">
                                            <?php echo formatDuration($list['video_duration']); ?>
                                        </span>
                                        <?php endif; ?>
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
                                <span class="<?php echo $albumClass ?>">
                                    <i class="mdi mdi-folder"></i> <?php echo $albumName ?>
                                </span>
                            </td>
                            <td><?php echo $list['imgDatd'] ?></td>
                            <td>
                                <a href="modImg.php?id=<?php echo $list['id'] ?>">
                                    <button type="button" class="btn btn-secondary btn-rounded">
                                        <i class="mdi mdi-clipboard-text-play-outline mr-1"></i>修改
                                    </button>
                                </a>
                                <button type="button" class="btn btn-danger btn-rounded" onclick="del(<?php echo $list['id']; ?>, '<?php echo htmlspecialchars($list['imgText'], ENT_QUOTES, 'UTF-8'); ?>')">
                                    <i class="mdi mdi-delete-empty mr-1"></i>删除
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                </div>

                <div class="mt-3">
                    <button type="button" id="selectAllPages" class="btn btn-outline-primary btn-sm" style="display:none;">
                        <i class="mdi mdi-checkbox-multiple-marked-outline"></i> 全选所有页
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function(char) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
    });
}

function del(id, imgText) {
    Swal.fire({
        title: '确认删除',
        html: `确认删除描述为 <strong>${escapeHtml(imgText || '')}</strong> 的照片吗？`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '删除',
        cancelButtonText: '取消',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: 'delImg.php',
            type: 'GET',
            data: { id: id, imgText: imgText },
            success: function() {
                $('#row-' + id).remove();
                updateSerialNumbers();
                Swal.fire('已删除', '照片已删除', 'success');
            },
            error: function(xhr) {
                Swal.fire('错误', xhr.responseText || '删除失败，请重试', 'error');
            }
        });
    });
}

let isDragging = false;
let dragStartValue = false;

$('#selectAll').on('click', function() {
    const isChecked = $(this).prop('checked');
    $('.photo-checkbox:visible').each(function() {
        $(this).prop('checked', isChecked);
        toggleRowSelection($(this));
    });
    updateBatchPanel();
    updateSelectAllPagesButton();
});

$(document).on('change', '.photo-checkbox', function() {
    toggleRowSelection($(this));
    updateBatchPanel();
    updateSelectAllPagesButton();
});

function toggleRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.prop('checked')) {
        row.addClass('row-selected');
    } else {
        row.removeClass('row-selected');
        $('#selectAll').prop('checked', false).prop('indeterminate', false);
    }
}

function updateBatchPanel() {
    const selected = $('.photo-checkbox:checked').length;
    $('#selectedCount').text(selected);
    $('#floatingCount').text(selected);

    if (selected > 0) {
        $('#batchActionPanel').addClass('show').show();
        $('#floatingCounter').addClass('show').show();
    } else {
        $('#batchActionPanel').removeClass('show').hide();
        $('#floatingCounter').removeClass('show').hide();
    }
}

function updateSelectAllPagesButton() {
    const visibleCheckboxes = $('.photo-checkbox:visible').length;
    const checkedVisible = $('.photo-checkbox:visible:checked').length;

    if (checkedVisible === visibleCheckboxes && visibleCheckboxes > 0) {
        $('#selectAllPages').fadeIn();
    } else {
        $('#selectAllPages').fadeOut();
    }
}

$('#selectAllPages').on('click', function() {
    $('.photo-checkbox').prop('checked', true).each(function() {
        $(this).closest('tr').addClass('row-selected');
    });
    $('#selectAll').prop('checked', true);
    updateBatchPanel();
    $(this).fadeOut();

    Swal.fire({
        icon: 'success',
        title: '已全选',
        text: `已选中 ${$('.photo-checkbox').length} 张照片`,
        timer: 1600,
        showConfirmButton: false
    });
});

function getSelectedIds() {
    const ids = [];
    $('.photo-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function clearSelection() {
    $('.photo-checkbox').prop('checked', false);
    $('.row-selected').removeClass('row-selected');
    $('#selectAll').prop('checked', false).prop('indeterminate', false);
    updateSelectAllPagesButton();
    updateBatchPanel();
}

$(document).on('click', '[data-action="delete"]', batchDelete);
$(document).on('click', '[data-action="transfer"]', batchTransfer);
$(document).on('click', '[data-action="cancel"]', clearSelection);

function batchDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        return Swal.fire('提示', '请先选择照片', 'info');
    }

    Swal.fire({
        title: '确认批量删除',
        html: `确定要删除 <strong>${ids.length}</strong> 张照片吗？此操作不可撤销。`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '删除',
        cancelButtonText: '取消',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: 'deletePhotos.php',
            type: 'POST',
            data: { ids: ids },
            dataType: 'json',
            success: function(res) {
                if (res && res.code === 200) {
                    ids.forEach(function(id) {
                        $('#row-' + id).remove();
                    });
                    clearSelection();
                    Swal.fire({
                        icon: 'success',
                        title: '删除成功',
                        text: res.message || `已删除 ${res.deleted_count || ids.length} 张照片`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('删除失败', (res && res.message) || '服务器返回异常', 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = '请求失败，请稍后重试';
                try {
                    const res = JSON.parse(xhr.responseText);
                    errorMsg = res.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                Swal.fire('错误', errorMsg, 'error');
            }
        });
    });
}

function batchTransfer() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        return Swal.fire('提示', '请先选择照片', 'info');
    }

    let albumOptions = '<option value="">-- 选择目标相册 --</option>';
    <?php
    $albumOptionsForJs = [];
    mysqli_data_seek($albumRes, 0);
    while ($album = mysqli_fetch_assoc($albumRes)) {
        $albumOptionsForJs[] = [
            'id' => $album['id'],
            'name' => $album['album_name']
        ];
    }
    ?>
    const albumOptionsData = <?php echo json_encode($albumOptionsForJs, JSON_UNESCAPED_UNICODE); ?>;
    albumOptionsData.forEach(function(album) {
        albumOptions += `<option value="${album.id}">${escapeHtml(album.name)}</option>`;
    });

    Swal.fire({
        title: '选择目标相册',
        html: `<div style="text-align:left;">
            <p>已选择 <strong>${ids.length}</strong> 张照片</p>
            <label for="swal-album-select" class="mt-2 mb-1">目标相册：</label>
            <select id="swal-album-select" class="form-control">${albumOptions}</select>
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '转移',
        cancelButtonText: '取消',
        preConfirm: () => {
            const albumSelect = document.getElementById('swal-album-select');
            if (!albumSelect.value) {
                Swal.showValidationMessage('请选择目标相册');
                return false;
            }
            return {
                albumId: albumSelect.value,
                albumName: albumSelect.options[albumSelect.selectedIndex].text
            };
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        const albumId = result.value.albumId;
        const albumName = result.value.albumName;

        $.ajax({
            url: 'transferPhotos.php',
            type: 'POST',
            data: { ids: ids, album_id: albumId },
            dataType: 'json',
            success: function(res) {
                if (res && res.code === 200) {
                    const finalAlbumName = res.album_name || albumName;
                    ids.forEach(function(id) {
                        const row = $('#row-' + id);
                        const albumCell = row.find('td:eq(5)');
                        albumCell.html(`<span class="album-badge"><i class="mdi mdi-folder"></i> ${escapeHtml(finalAlbumName)}</span>`);

                        // 更新data属性
                        row.attr('data-album-id', albumId);
                        row.attr('data-album-name', finalAlbumName);
                        row.data('album-id', parseInt(albumId));
                        row.data('album-name', finalAlbumName);
                    });
                    clearSelection();
                    Swal.fire({
                        icon: 'success',
                        title: '转移成功',
                        text: res.message || '照片已转移',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('转移失败', (res && res.message) || '服务器返回异常', 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = '请求失败，请稍后重试';
                try {
                    const res = JSON.parse(xhr.responseText);
                    errorMsg = res.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                Swal.fire('错误', errorMsg, 'error');
            }
        });
    });
}

$(document).on('click', '[data-action="preview"]', function() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        return Swal.fire('提示', '请先选择照片', 'info');
    }

    let previewHtml = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;max-height:400px;overflow-y:auto;">';
    ids.forEach(function(id) {
        const row = $(`#row-${id}`);
        const imgSrc = row.find('.img-thumbnail').attr('src');
        const imgText = row.find('td:eq(4)').text();
        previewHtml += `<div style="text-align:center;">
            <img src="${imgSrc}" style="width:100%;height:80px;object-fit:cover;border-radius:4px;cursor:pointer;" onclick="window.open('${imgSrc}', '_blank')">
            <small style="display:block;margin-top:4px;font-size:11px;color:#666;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(imgText || '无描述')}</small>
        </div>`;
    });
    previewHtml += '</div>';

    Swal.fire({
        title: `预览 ${ids.length} 张照片`,
        html: previewHtml,
        width: '800px',
        confirmButtonText: '关闭'
    });
});


$(document).on('keydown', function(e) {
    if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        $('#selectAll').prop('checked', false).trigger('click');
    }
    if (e.key === 'Escape') {
        clearSelection();
    }
});

$(document).on('mousedown', '.photo-checkbox', function(e) {
    isDragging = true;
    dragStartValue = !$(this).prop('checked');
    $(this).prop('checked', dragStartValue);
    toggleRowSelection($(this));
    updateBatchPanel();
    $('body').addClass('drag-selecting');
    e.preventDefault();
});

$(document).on('mouseenter', '.photo-checkbox', function() {
    if (isDragging) {
        $(this).prop('checked', dragStartValue);
        toggleRowSelection($(this));
    }
});

$(document).on('mouseup', function() {
    if (!isDragging) {
        return;
    }
    isDragging = false;
    $('body').removeClass('drag-selecting');
    updateBatchPanel();
    updateSelectAllPagesButton();
});

$(document).on('click', 'tbody tr', function(e) {
    if ($(e.target).closest('td').index() === 7 || $(e.target).is('button,input,img,a') || $(e.target).closest('button,a').length) {
        return;
    }
    const checkbox = $(this).find('.photo-checkbox');
    checkbox.prop('checked', !checkbox.prop('checked'));
    toggleRowSelection(checkbox);
    updateBatchPanel();
    updateSelectAllPagesButton();
});

// 原生相册筛选功能
$('#albumFilter').on('change', function() {
    const albumId = $(this).val();
    const rows = $('#photoTbody tr');

    if (albumId === '') {
        rows.show();
        $('#filterInfo').text('');
    } else {
        rows.each(function() {
            const rowAlbumId = $(this).data('album-id').toString();
            if ((albumId === 'null' && rowAlbumId === '0') || rowAlbumId === albumId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        const albumName = $(this).find('option:selected').text();
        $('#filterInfo').text(`(${albumName})`);
    }

    updateSerialNumbers();
});

// 类型筛选
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
    if (typeof MediaPlayer !== 'undefined' && MediaPlayer.open) {
        MediaPlayer.open({
            type: 'video',
            url: url,
            description: description
        });
    } else {
        // 降级方案：在新标签页打开视频
        window.open(url, '_blank');
    }
}

// 更新可见行序号
function updateSerialNumbers() {
    let num = 0;
    $('#photoTbody tr:visible').each(function() {
        num++;
        $(this).find('.SerialNumber').text(num);
    });
}
</script>

<script src="assets/js/selection-manager.js"></script>
<script src="../Style/js/videoPlayer.js"></script>

<?php
include_once 'Footer.php';
?>
</body>
</html>
