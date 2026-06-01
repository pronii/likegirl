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
    .batch-action-card {
        background: #f8f9fa;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }
</style>

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
                <!-- 批量操作面板 -->
                <div id="batchActionPanel" class="batch-action-card">
                    <div class="d-flex align-items-center">
                        <span class="mr-3 font-weight-bold">已选 <span id="selectedCount">0</span> 张</span>
                        <div class="form-group mb-0 mr-3">
                            <select id="targetAlbum" class="form-control form-control-sm">
                                <option value="">-- 选择目标相册 --</option>
                                <?php
                                $albumRes = mysqli_query($connect, "SELECT id, album_name FROM love_album ORDER BY sort_order ASC");
                                while($album = mysqli_fetch_assoc($albumRes)) {
                                    echo "<option value='" . $album['id'] . "'>" . $album['album_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm mr-2" onclick="batchTransfer()">转移相册</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="batchDelete()">批量删除</button>
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
                        // 处理相册显示
                        $albumName = $list['album_name'] ? $list['album_name'] : '未分类';
                        $albumClass = $list['album_name'] ? 'album-badge' : 'album-badge album-none';
                        ?>
                        <tr id="row-<?php echo $list['id']; ?>">
                            <td>
                                <input type="checkbox" class="photo-checkbox" value="<?php echo $list['id']; ?>" onchange="updateBatchPanel()">
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
                                        <i class=" mdi mdi-clipboard-text-play-outline mr-1"></i>修改
                                    </button>
                                </a>
                                <a href="javascript:del(<?php echo $list['id']; ?>,'<?php echo $list['imgText']; ?>');">
                                    <button type="button" class="btn btn-danger btn-rounded">
                                        <i class=" mdi mdi-delete-empty mr-1"></i>删除
                                    </button>
                                </a></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>

            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>


<script>
    // 1. 手动初始化 DataTable，避免 demo.datatable-init.js 导致的重复初始化
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#basic-datatable')) {
            $('#basic-datatable').DataTable().destroy();
        }

        $('#basic-datatable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Chinese.json"
            },
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 0 } // 第一列(勾选框)不参与排序
            ]
        });
    });

    function del(id, imgText) {
        if (confirm('您确认要删除描述为 ' + imgText + ' 的相册图片吗')) {
            location.href = 'delImg.php?id=' + id + '&imgText=' + encodeURIComponent(imgText);
        }
    }

    // 全选逻辑
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        $('.photo-checkbox').prop('checked', isChecked);
        updateBatchPanel();
    });

    function updateBatchPanel() {
        const selected = $('.photo-checkbox:checked').length;
        $('#selectedCount').text(selected);
        if (selected > 0) {
            $('#batchActionPanel').css('display', 'flex').fadeIn();
        } else {
            $('#batchActionPanel').fadeOut();
        }
    }

    function getSelectedIds() {
        const ids = [];
        $('.photo-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function batchDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return alert('请先选择照片');
        if (!confirm('确定要批量删除 ' + ids.length + ' 张照片吗？此操作不可撤销！')) return;

        const $btn = $(this);
        $btn.prop('disabled', true).text('处理中...');

        // 使用相对路径调用接口
                $.post('deletePhotos.php', { ids: ids }, function(res) {
            if (res.code === 200) {
            alert(res.message);
            location.reload();
            } else {
                alert('删除失败: ' + res.message);
                $btn.prop('disabled', false).text('批量删除');
    }
        }, 'json').fail(function() {
            alert('网络请求失败，请检查接口路径');
            $btn.prop('disabled', false).text('批量删除');
        });
    }

    function batchTransfer() {
        const ids = getSelectedIds();
        const albumId = $('#targetAlbum').val();

        if (ids.length === 0) return alert('请先选择照片');
        if (!albumId) return alert('请选择目标相册');
        if (!confirm('确定将选中的 ' + ids.length + ' 张照片转移到指定相册吗？')) return;

        const $btn = $(this);
        $btn.prop('disabled', true).text('处理中...');
        $.post('../transferPhotos.php', { ids: ids, album_id: albumId }, function(res) {
            if (res.code === 200) {
            alert(res.message);
            location.reload();
            } else {
                alert('转移失败: ' + res.message);
                $btn.prop('disabled', false).text('转移');
    }
        }, 'json').fail(function() {
            alert('网络请求失败，请检查接口路径');
            $btn.prop('disabled', false).text('转移');
        });
    }

</script>
<?php
include_once 'Footer.php';
?>
<!-- third party js -->
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
<!-- 移除导致报错的 demo 初始化脚本 -->
<!-- <script src="assets/js/pages/demo.datatable-init.js"></script> -->
<!-- end demo js-->
</body>
</html>

