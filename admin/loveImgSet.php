<?php
session_start();
include_once 'Nav.php';
// 关联查询获取相册名称
$loveImg = "SELECT li.*, la.album_name FROM loveImg li
            LEFT JOIN love_album la ON li.album_id = la.id
            ORDER BY li.id desc";
$resImg = mysqli_query($connect, $loveImg);
?>

<link href="assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/selection-manager.css" rel="stylesheet" type="text/css"/>

<style>
    .img-thumbnail {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .img-thumbnail:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
</style>

<!-- Floating selection counter -->
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

                <!-- Floating batch action bar -->
                <div id="batchActionPanel" class="floating-batch-bar">
                    <div class="batch-bar-content">
                        <div class="batch-info">
                            <i class="mdi mdi-checkbox-multiple-marked"></i>
                            <span>已选 <strong id="selectedCount">0</strong> 张</span>
                        </div>
                        <div class="batch-actions">
                            <select id="targetAlbum" class="form-control form-control-sm batch-select">
                                <option value="">-- 选择目标相册 --</option>
                                <?php
                                $albumRes = mysqli_query($connect, "SELECT id, album_name FROM love_album ORDER BY sort_order ASC");
                                while($album = mysqli_fetch_assoc($albumRes)) {
                                    echo "<option value='" . $album['id'] . "'>" . $album['album_name'] . "</option>";
                                }
                                ?>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm" data-action="transfer">
                                <i class="mdi mdi-folder-move"></i> 转移相册
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-action="delete">
                                <i class="mdi mdi-delete-multiple"></i> 批量删除
                            </button>
                            <button type="button" class="btn btn-light btn-sm batch-close" data-action="cancel">
                                <i class="mdi mdi-close"></i>
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
                                <div class="SerialNumber">
                                    <?php echo $SerialNumber ?>
                                </div>
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
                                <button type="button" class="btn btn-danger btn-rounded" onclick="del(<?php echo $list['id']; ?>,'<?php echo $list['imgText']; ?>')">
                                    <i class="mdi mdi-delete-empty mr-1"></i>删除
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <!-- Select All Pages button -->
                <div class="mt-3">
                    <button type="button" id="selectAllPages" class="btn btn-outline-primary btn-sm" style="display:none;">
                        <i class="mdi mdi-checkbox-multiple-marked-outline"></i> 全选所有页（跨页选择）
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 先加载所有依赖库 -->
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
// 删除函数 —— 全局可访问，彻底解决 del is not defined
function del(id, imgText) {
    Swal.fire({
        title: '确认删除',
        html: `您确认要删除描述为 <strong>${imgText}</strong> 的相册图片吗？`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '是的，删除',
        cancelButtonText: '取消'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delImg.php',
                type: 'GET',
                data: { id: id, imgText: imgText },
                success: function(response) {
                    // Use DataTable API to remove row instead of location.reload()
                    const table = $('#basic-datatable').DataTable();
                    const row = table.row('#row-' + id);
                    row.remove().draw(false);

                    Swal.fire('已删除', '照片已成功删除', 'success');
                },
                error: function() {
                    Swal.fire('错误', '删除失败，请重试', 'error');
                }
            });
        }
    });
}

let dataTableInstance;

$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#basic-datatable')) {
        $('#basic-datatable').DataTable().destroy();
    }

    dataTableInstance = $('#basic-datatable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Chinese.json"
        },
        "responsive": true,
        "columnDefs": [{ "orderable": false, "targets": 0 }],
        "drawCallback": function() {
            // Re-apply visual selection after redraw
            $('.photo-checkbox:checked').each(function() {
                $(this).closest('tr').addClass('row-selected');
            });
        }
    });

    // Show "Select All Pages" button when current page is fully selected
    dataTableInstance.on('draw', function() {
        updateSelectAllPagesButton();
    });
});

// 全选当前页
$('#selectAll').on('click', function() {
    const isChecked = $(this).prop('checked');
    $('.photo-checkbox:visible').each(function() {
        $(this).prop('checked', isChecked);
        toggleRowSelection($(this));
    });
    updateBatchPanel();
    updateSelectAllPagesButton();
});

// Individual checkbox change handler with visual feedback
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
        $('#selectAll').prop('checked', false);
    }
}

function updateBatchPanel() {
    const selected = $('.photo-checkbox:checked').length;
    $('#selectedCount').text(selected);
    $('#floatingCount').text(selected);

    if (selected > 0) {
        $('#batchActionPanel').addClass('show');
        $('#floatingCounter').addClass('show');
    } else {
        $('#batchActionPanel').removeClass('show');
        $('#floatingCounter').removeClass('show');
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

// Select all across all pages
$('#selectAllPages').on('click', function() {
    $('.photo-checkbox').prop('checked', true);
    $('.photo-checkbox').each(function() {
        $(this).closest('tr').addClass('row-selected');
    });
    $('#selectAll').prop('checked', true);
    updateBatchPanel();
    $(this).fadeOut();

    Swal.fire({
        icon: 'success',
        title: '已全选',
        text: `已选中全部 ${$('.photo-checkbox').length} 张照片（跨所有页）`,
        timer: 2000,
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

// Batch action handlers using data attributes
$(document).on('click', '[data-action="delete"]', function() {
    batchDelete();
});

$(document).on('click', '[data-action="transfer"]', function() {
    batchTransfer();
});

$(document).on('click', '[data-action="cancel"]', function() {
    $('.photo-checkbox').prop('checked', false);
    $('.photo-checkbox').each(function() {
        $(this).closest('tr').removeClass('row-selected');
    });
    $('#selectAll').prop('checked', false);
    updateBatchPanel();
});

// 批量删除（使用DataTable API更新）
function batchDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) return Swal.fire('提示', '请先选择照片', 'info');

    Swal.fire({
        title: '确认批量删除',
        html: `确定要批量删除 <strong>${ids.length}</strong> 张照片吗？此操作不可撤销！`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '是的，删除',
        cancelButtonText: '取消'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.post('deletePhotos.php', { ids: ids }, function(res) {
            if (res.code === 200) {
                // Use DataTable API to remove rows instead of location.reload()
                const table = $('#basic-datatable').DataTable();
                ids.forEach(function(id) {
                    const row = table.row('#row-' + id);
                    row.remove();
                });
                table.draw(false);

                // Clear selections
                $('.photo-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateBatchPanel();

                Swal.fire('成功', res.message, 'success');
            } else {
                Swal.fire('删除失败', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('错误', '网络请求失败，请检查接口路径', 'error');
        });
    });
}

// 批量转移（使用DataTable API更新）
function batchTransfer() {
    const ids = getSelectedIds();
    const albumId = $('#targetAlbum').val();

    if (ids.length === 0) return Swal.fire('提示', '请先选择照片', 'info');
    if (!albumId) return Swal.fire('提示', '请选择目标相册', 'info');

    const albumName = $('#targetAlbum option:selected').text();

    Swal.fire({
        title: '确认转移',
        html: `确定将选中的 <strong>${ids.length}</strong> 张照片转移到 <strong>${albumName}</strong> 吗？`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '是的，转移',
        cancelButtonText: '取消'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.post('transferPhotos.php', { ids: ids, album_id: albumId }, function(res) {
            if (res.code === 200) {
                // Update DataTable cells instead of full reload
                const table = $('#basic-datatable').DataTable();
                ids.forEach(function(id) {
                    const row = table.row('#row-' + id);
                    const albumCell = $(row.node()).find('td:eq(4)');
                    albumCell.html(`<span class="album-badge"><i class="mdi mdi-folder"></i> ${albumName}</span>`);
                });

                // Clear selections
                $('.photo-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                $('#targetAlbum').val('');
                updateBatchPanel();

                Swal.fire('成功', res.message, 'success');
            } else {
                Swal.fire('转移失败', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('错误', '网络请求失败，请检查接口路径', 'error');
        });
    });
}
</script>

<script src="assets/js/selection-manager.js"></script>

<?php
include_once 'Footer.php';
?>
</body>
</html>
