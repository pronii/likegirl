<?php
include_once 'head.php';
?>

<head>
    <link rel="stylesheet" href="Style/css/loveImg.css?LikeGirl=<?php echo $version ?>">
    <meta charset="utf-8" />
    <title><?php echo $text['title'] ?> — 恋爱相册</title>
</head>

<body>
    <div id="pjax-container">
        <div class="page-header central" style="margin-bottom: 10px;">
            <h4 class="text-ce" style="margin: 0;">记录下你的最美瞬间</h4>
        </div>

        <!-- 面包屑导航 -->
        <div class="central" style="margin-bottom: 20px;">
            <span id="breadcrumb" style="display:none; cursor: pointer; color: #666;">
                <svg class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16"><path d="M928 160H96c-17.7 0-32 14.3-32 32v640c0 17.7 14.3 32 32 32h832c17.7 0 32-14.3 32-32V192c0-17.7-14.3-32-32-32z m-40 632H136V232h752v560z" fill="#666"/></svg>
                返回相册
            </span>
            <span id="currentAlbumName" style="display:none; color: #333; margin-left: 10px; font-weight: 500;"></span>
        </div>
        <!-- 相册文件夹区域 -->
        <div class="row central gallery" id="albumGallery">
        </div>

        <!-- 图片展示区域 -->
        <div class="row central gallery" id="photoGallery" style="display:none;">
    </div>

        <div class="loading" id="loading">数据加载中...</div>

        <div class="load-more">
            <button class="lg-btn-alt" id="loadMoreBtn" style="display:none;">
                <svg t="1756817125714" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4311" width="256" height="256"><path d="M849.799529 168.357647A481.882353 481.882353 0 1 0 993.882353 512a90.352941 90.352941 0 0 0-180.705882 0 301.176471 301.176471 0 1 1-90.051765-214.799059 90.352941 90.352941 0 1 0 126.674823-128.843294z" p-id="4312"></path></svg>
              加载更多
            </button>
        </div>
    </div>

    <script src="Style/js/loveAlbum.js"></script>
    <script>
        // 前台页面 - 仅浏览模式，禁用选择功能
        // 所有选择功能仅在 admin/loveImgSet.php 后台管理页面可用
    </script>

    <?php
    include_once 'footer.php';
    ?>
</body>

</html>
