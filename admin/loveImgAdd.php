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

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">新增图片</h4>
                <form class="needs-validation" action="ImgAddPost.php" method="post" onsubmit="return check()" novalidate>
                    <div class="form-group mb-3">
                        <label>所属相册 <span class="text-muted" style="font-size:12px;">（不选则不在前台显示）</span></label>
                        <select name="album_id" class="form-control">
                            <option value="0">-- 未分类（仅后台可见） --</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['id'] ?>"><?php echo $album['album_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>日期</label>
                        <input class="form-control col-sm-4" id="example-date" type="date" name="imgDatd" placeholder="日期" value="<?php echo $inv_date ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>图片描述<span class="margin_left badge badge-success-lighten">尽量控制在25个字符以内 </span></label>
                        <input name="imgText" type="text" class="form-control" placeholder="请输入图片描述" value="" required>
                    </div>

                    <div class="form-group mb-3" id="img_url">
                        <label>图片URL</label>
                        <input type="text" name="imgUrl" class="form-control" placeholder="请输入图片URL地址" value="" required>
                    </div>
                    <div class="form-group mb-3 text_right">
                        <button class="btn btn-primary" type="submit">保存图片</button>
                        <a href="loveImgSet.php" class="btn btn-secondary">返回列表</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function check() {
        let title = document.getElementsByName('imgText')[0].value.trim();
        if (title.length == 0) {
            alert("图片描述不能为空");
            return false;
        }
        let imgUrl = document.getElementsByName('imgUrl')[0].value.trim();
        if (imgUrl.length == 0) {
            alert("图片URL不能为空");
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