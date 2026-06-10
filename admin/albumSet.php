<?php
session_start();
include_once 'Nav.php';

mysqli_set_charset($connect, "utf8mb4");
$loveImg = "select * from love_album order by sort_order asc, id desc";
$resImg = mysqli_query($connect, $loveImg);
?>

<link href="assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css"/>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">相册管理
                    <a href="/admin/albumAdd.php">
                        <button type="button" class="btn btn-success btn-sm right_10">
                            <i class="mdi mdi-plus-circle"></i> 新增相册
                        </button>
                    </a>
                </h4>
                <table id="basic-datatable" class="table dt-responsive nowrap" width="100%">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>相册名称</th>
                        <th>相册描述</th>
                        <th>排序</th>
                        <th>创建时间</th>
                        <th style="width:200px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $SerialNumber = 0;
                    while ($list = mysqli_fetch_array($resImg)) {
                        $SerialNumber++;
                        // 统计该相册下的图片数量
                        $countSql = "SELECT COUNT(*) as count FROM loveImg WHERE album_id = " . $list['id'];
                        $countRes = mysqli_query($connect, $countSql);
                        $countRow = mysqli_fetch_array($countRes);
                        $photoCount = $countRow['count'];
                        ?>
                        <tr>
                            <td><?php echo $SerialNumber ?></td>
                            <td><strong><?php echo $list['album_name'] ?></strong></td>
                            <td><?php echo $list['album_desc'] ? $list['album_desc'] : '-' ?></td>
                            <td><?php echo $list['sort_order'] ?></td>
                            <td><?php echo $list['create_time'] ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo $photoCount ?> 张</span>
                                <a href="albumMod.php?id=<?php echo $list['id'] ?>">
                                    <button type="button" class="btn btn-secondary btn-rounded btn-sm">
                                        <i class="mdi mdi-clipboard-text-play-outline mr-1"></i>修改
                                    </button>
                                </a>
                                <a href="albumDel.php?id=<?php echo $list['id']; ?>"
                                   onclick="return confirmAlbumDelete(<?php echo json_encode($list['album_name'], JSON_UNESCAPED_UNICODE); ?>);">
                                    <button type="button" class="btn btn-danger btn-rounded btn-sm">
                                        <i class="mdi mdi-delete-empty mr-1"></i>删除
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmAlbumDelete(name) {
        return confirm('确定要删除相册 "' + name + '" 吗？\n注意：相册下的图片不会删除，会移动到默认相册。');
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
<script src="assets/js/pages/demo.datatable-init.js"></script>
</body>
</html>
