<?php
session_start();
include_once 'Nav.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3 size_18">新增相册</h4>
                <form class="needs-validation" action="albumAddPost.php" method="post" onsubmit="return check()" novalidate>
                    <div class="form-group mb-3">
                        <label>相册名称 <span class="text-danger">*</span></label>
                        <input type="text" name="album_name" class="form-control" placeholder="请输入相册名称" maxlength="50" required>
                    </div>

                    <?php
                    $currentCover = '';
                    include_once 'albumCoverPicker.php';
                    ?>

                    <div class="form-group mb-3">
                        <label>相册描述</label>
                        <input type="text" name="album_desc" class="form-control" placeholder="请输入相册描述（可选）" maxlength="200">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>排序顺序</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        <small class="form-text text-muted">数字越小越靠前</small>
                    </div>
                    
                    <div class="form-group mb-3 text_right">
                        <button class="btn btn-primary" type="submit">保存相册</button>
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