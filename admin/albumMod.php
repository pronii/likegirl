<?php
session_start();
include_once 'Nav.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo '<script>alert("参数错误");location.href="albumSet.php";</script>';
    exit;
}

include_once 'connect.php';
$album = "SELECT * FROM love_album WHERE id = $id LIMIT 1";
$resAlbum = mysqli_query($connect, $album);
$albumInfo = mysqli_fetch_array($resAlbum);

if (!$albumInfo) {
    echo '<script>alert("相册不存在");location.href="albumSet.php";</script>';
    exit;
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">修改相册 — ID：<?php echo $id ?></h4>
                <form class="needs-validation" action="albumModPost.php" method="post" onsubmit="return check()" novalidate>
                    <div class="form-group mb-3">
                        <label>相册名称 <span class="text-danger">*</span></label>
                        <input type="text" name="album_name" class="form-control" value="<?php echo $albumInfo['album_name'] ?>" maxlength="50" required>
                    </div>

                    <?php
                    $currentCover = $albumInfo['album_cover'];
                    include_once 'albumCoverPicker.php';
                    ?>

                    <div class="form-group mb-3">
                        <label>相册描述</label>
                        <input type="text" name="album_desc" class="form-control" value="<?php echo $albumInfo['album_desc'] ?>" maxlength="200">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>排序顺序</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo $albumInfo['sort_order'] ?>" min="0">
                        <small class="form-text text-muted">数字越小越靠前</small>
                    </div>
                    
                    <div class="form-group mb-3 text_right">
                        <input type="hidden" name="id" value="<?php echo $id ?>">
                        <button class="btn btn-primary" type="submit">保存修改</button>
                        <a href="albumSet.php" class="btn btn-secondary">返回列表</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function check() {
        let name = document.querySelector('input[name="album_name"]').value.trim();
        if (name.length == 0) {
            alert("相册名称不能为空");
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