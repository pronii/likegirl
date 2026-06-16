<?php
session_start();
include_once 'Nav.php';
include_once 'Function.php';

$id = $_GET['id'];
include_once 'connect.php';

// 获取图片信息
$loveImg = "SELECT * FROM loveImg WHERE id=$id LIMIT 1";
$resImg = mysqli_query($connect, $loveImg);
$Imglist = mysqli_fetch_array($resImg);

// 获取所有相册
$albumSql = "SELECT * FROM love_album ORDER BY sort_order ASC, id ASC";
$albumRes = mysqli_query($connect, $albumSql);
$albums = [];
while ($row = mysqli_fetch_array($albumRes)) {
    $albums[] = $row;
}

$isVideo = ($Imglist['media_type'] ?? 'image') === 'video';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">修改相册 — ID：<?php echo $Imglist['id'] ?></h4>

                <!-- 预览区域 -->
                <div class="form-group mb-4">
                    <label><?php echo $isVideo ? '视频预览' : '图片预览'; ?></label>
                    <div>
                        <?php if ($isVideo): ?>
                            <video controls style="max-width: 400px; max-height: 300px;">
                                <source src="<?php echo $Imglist['imgUrl']; ?>">
                                您的浏览器不支持视频播放。
                            </video>
                            <?php if (!empty($Imglist['video_duration']) || !empty($Imglist['file_size'])): ?>
                            <div class="mt-2">
                                <?php if (!empty($Imglist['video_duration'])): ?>
                                <p class="mb-1"><i class="mdi mdi-clock-outline"></i> 时长: <?php echo formatDuration($Imglist['video_duration']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($Imglist['file_size'])): ?>
                                <p class="mb-1"><i class="mdi mdi-file-outline"></i> 文件大小: <?php echo formatFileSize($Imglist['file_size']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <img src="<?php echo $Imglist['imgUrl']; ?>"
                                 style="max-width: 400px; max-height: 300px; object-fit: contain;"
                                 alt="预览">
                        <?php endif; ?>
                    </div>
                </div>

                <form class="needs-validation" action="ImgUpdaPost.php" method="post" onsubmit="return check()" novalidate>
                    <div class="form-group mb-3">
                        <label>所属相册 <span class="text-danger">*</span></label>
                        <select name="album_id" class="form-control" required>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['id'] ?>" <?php echo $Imglist['album_id'] == $album['id'] ? 'selected' : ''; ?>><?php echo $album['album_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>日期</label>
                        <input class="form-control col-sm-4" id="example-date" type="date" name="imgDatd" placeholder="日期" value="<?php echo $Imglist['imgDatd'] ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label><?php echo $isVideo ? '视频描述' : '图片描述'; ?></label>
                        <input name="imgText" type="text" class="form-control" placeholder="请输入<?php echo $isVideo ? '视频' : '图片'; ?>描述" value="<?php echo $Imglist['imgText'] ?>" required>
                    </div>

                    <div class="form-group mb-3" id="img_url">
                        <label><?php echo $isVideo ? '视频URL' : '图片URL'; ?></label>
                        <input type="text" name="imgUrl" class="form-control" placeholder="请输入<?php echo $isVideo ? '视频' : '图片'; ?>URL地址" value="<?php echo $Imglist['imgUrl'] ?>" required>
                    </div>
                    <div class="form-group mb-3 text_right">
                        <input name="id" value="<?php echo $id ?>" type="hidden">
                        <button class="btn btn-primary" type="submit">保存修改</button>
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
            alert("描述不能为空");
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