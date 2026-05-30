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
                <table id="basic-datatable" class="table dt-responsive nowrap" width="100%">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>图片预览</th>
                        <th>图片描述</th>
                        <th>所属相册</th>
                        <th>日期</th>
                        <th style="width:150px;">操作</th>
                    </tr>
                    </thead>


                    <tbody>
                    <?php
                    $SerialNumber = 0;
                    while ($list = mysqli_fetch_array($resImg)) {
                        $SerialNumber++;
                        // 处理相册显示
                        $albumName = $list['album_name'] ? $list['album_name'] : '未分类';
                        $albumClass = $list['album_name'] ? 'album-badge' : 'album-badge album-none';
                        ?>
                        <tr>
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
    function del(id, imgText) {
        if (confirm('您确认要删除描述为 ' + imgText + ' 的相册图片吗')) {
            location.href = 'delImg.php?id=' + id + '&imgText' + imgText;
        }
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
<!-- demo app -->
<script src="assets/js/pages/demo.datatable-init.js"></script>
<!-- end demo js-->
</body>
</html>