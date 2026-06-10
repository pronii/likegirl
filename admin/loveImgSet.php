<?php
session_start();
include_once 'Nav.php';

// 确保使用 UTF-8 编码
mysqli_set_charset($connect, "utf8mb4");

// 关联查询获取相册名称
$loveImg = "SELECT li.*, la.album_name FROM loveImg li
            LEFT JOIN love_album la ON li.album_id = la.id
            ORDER BY li.id desc";
$resImg = mysqli_query($connect, $loveImg);
$albumRes = mysqli_query($connect, "SELECT id, album_name FROM love_album ORDER BY sort_order ASC");
?>

<link href="assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/selection-manager.css" rel="stylesheet" type="text/css"/>

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
    #albumFilter {
        transition: border-color 0.3s ease;
    }
    #albumFilter:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    /* 优化复选框交互体验 */
    .photo-checkbox,
    #selectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    /* 拖拽选择时禁止文字选中 */
    .drag-selecting {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    tbody tr {
        cursor: pointer;
    }
</style>

<div id="floatingCounter" class="floating-counter">
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

                <div id="batchActionPanel" class="floating-batch-bar">
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

                <table id="basic-datatable" class="table dt-responsive nowrap" width="100%">
                    <thead>
                    <tr>
                        <th width="40px"><input type="checkbox" id="selectAll"></th>
                        <th>序号</th>
                        <th>图片预览</th>
                        <th>图片描述</th>
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
                        ?>
                        <tr id="row-<?php echo $list['id']; ?>" data-photo-id="<?php echo $list['id']; ?>">
                            <td>
                                <input type="checkbox" class="photo-checkbox" value="<?php echo $list['id']; ?>" data-checkbox="photo">
                            </td>
                            <td>
                                <div class="SerialNumber"><?php echo $SerialNumber ?></div>
                            </td>
                            <td>
                                <img src="<?php echo $list['imgUrl'] ?>"
                                     class="img-thumbnail"
                                     alt="预览"
                                     onclick="window.open('<?php echo $list['imgUrl'] ?>', '_blank')">
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

                <div class="mt-3">
                    <button type="button" id="selectAllPages" class="btn btn-outline-primary btn-sm" style="display:none;">
                        <i class="mdi mdi-checkbox-multiple-marked-outline"></i> 全选所有页
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/vendor/jquery.dataTables.min.js"></script>
<script src="assets/js/vendor/dataTables.bootstrap4.js"></script>
<script src="assets/js/vendor/dataTables.responsive.min.js"></script>
<script src="assets/js/vendor/responsive.bootstrap4.min.js"></script>
<script src="assets/js/vendor/dataTables.buttons.min.js"></script>
<script src="assets/js/vendor/buttons.bootstrap4.min.js"></script>
<script src="assets/js/vendor/buttons.html5.min.js"></script>
<script src="assets/js/vendor/buttons.flash.min.js"></script>
<script src="assets/js/vendor/buttons.print.min.js"></script>
<script src="assets/js/vendor/dataTables.keyTable.min.js"></script>
<script src="assets/js/vendor/dataTables.select.min.js"></script>
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
                const table = $('#basic-datatable').DataTable();
                table.row('#row-' + id).remove().draw(false);
                Swal.fire('已删除', '照片已删除', 'success');
            },
            error: function(xhr) {
                Swal.fire('错误', xhr.responseText || '删除失败，请重试', 'error');
            }
        });
    });
}

let dataTableInstance;
let isDragging = false;
let dragStartValue = false;

$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#basic-datatable')) {
        $('#basic-datatable').DataTable().destroy();
    }

    dataTableInstance = $('#basic-datatable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Chinese.json' },
        responsive: true,
        columnDefs: [{ orderable: false, targets: 0 }],
        drawCallback: function() {
            $('.photo-checkbox:checked').each(function() {
                $(this).closest('tr').addClass('row-selected');
            });
            updateBatchPanel();
        }
    });

    dataTableInstance.on('draw', function() {
        updateSelectAllPagesButton();
    });
});

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
    $('#batchActionPanel').toggleClass('show', selected > 0);
    $('#floatingCounter').toggleClass('show', selected > 0);
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
                        const albumCell = $('#row-' + id).find('td:eq(4)');
                        albumCell.html(`<span class="album-badge"><i class="mdi mdi-folder"></i> ${escapeHtml(finalAlbumName)}</span>`);
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
        const imgText = row.find('td:eq(3)').text();
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

$('#albumFilter').on('change', function() {
    const albumId = $(this).val();

    if (albumId === '') {
        dataTableInstance.column(4).search('').draw();
        $('#filterInfo').text('');
    } else if (albumId === 'null') {
        dataTableInstance.column(4).search('未分类', true, false).draw();
        $('#filterInfo').text('(仅看未分类)');
    } else {
        const albumName = $(this).find('option:selected').text();
        dataTableInstance.column(4).search(albumName, true, false).draw();
        $('#filterInfo').text(`(${albumName})`);
    }
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
    if ($(e.target).closest('td').index() === 6 || $(e.target).is('button,input,img,a') || $(e.target).closest('button,a').length) {
        return;
    }
    const checkbox = $(this).find('.photo-checkbox');
    checkbox.prop('checked', !checkbox.prop('checked'));
    toggleRowSelection(checkbox);
    updateBatchPanel();
    updateSelectAllPagesButton();
});
</script>

<script src="assets/js/selection-manager.js"></script>

<?php
include_once 'Footer.php';
?>
</body>
</html>
