<?php
include_once 'head.php';
?>

<head>
    <link rel="stylesheet" href="Style/css/loveImg.css?LikeGirl=<?php echo $version ?>">
    <link rel="stylesheet" href="Style/css/photo-selection.css?LikeGirl=<?php echo $version ?>">
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

        <!-- 选择模式按钮（独立显示） -->
        <button id="selectionModeBtn" class="selection-mode-btn" style="display:none;">
            <svg viewBox="0 0 1024 1024" width="20" height="20"><path d="M866.9 169.9L527.1 54.1C523 52.7 517.5 52 512 52s-11 .7-15.1 2.1L157.1 169.9c-8.3 2.8-15.1 12.4-15.1 21.2v482.4c0 8.8 5.7 20.4 12.6 25.9L499.3 968c3.5 2.7 8 4.1 12.6 4.1s9.2-1.4 12.6-4.1l344.7-268.6c6.9-5.4 12.6-17 12.6-25.9V191.1c.2-8.8-6.6-18.3-14.9-21.2zM810 654.3L512 886.5 214 654.3V226.7l298-101.6 298 101.6v427.6z"/><path d="M402.9 528.8l-77.5-77.5c-6.2-6.2-16.4-6.2-22.6 0l-22.6 22.6c-6.2 6.2-6.2 16.4 0 22.6l112.7 112.7c6.2 6.2 16.4 6.2 22.6 0l226.9-226.9c6.2-6.2 6.2-16.4 0-22.6L619.8 337c-6.2-6.2-16.4-6.2-22.6 0L402.9 528.8z"/></svg>
            选择
        </button>

        <!-- 浮动操作栏 -->
        <div id="floatingActionBar" class="floating-action-bar">
            <div class="selection-counter">
                <span>已选择</span>
                <span id="selectionCount" class="count-badge">0</span>
                <span>项</span>
            </div>
            <div class="action-buttons" style="display:none;">
                <button id="selectAllBtn" class="action-btn action-btn-primary" title="全选当前页 (Ctrl+A)">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M866.133 258.133L360.533 763.733l-213.333-213.333L64 633.6l296.533 296.533L949.333 341.333z" fill="currentColor"/>
                    </svg>
                    全选
                </button>
                <button id="invertSelectionBtn" class="action-btn" title="反选 (Ctrl+I)">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="currentColor"/>
                        <path d="M623.6 316.7C593.6 290.4 554 276 512 276s-81.6 14.5-111.6 40.7C369.2 344 352 380.7 352 420.8c0 6.8 7.9 10.5 13.3 6.2l48.3-38.3c2.5-2 6.1-1.9 8.5.3l38.8 36.8c2.4 2.3 6.1 2.3 8.5 0l38.8-36.8c2.4-2.2 6-2.3 8.5-.3l48.3 38.3c5.3 4.2 13.3.6 13.3-6.2 0-40.1-17.2-76.8-48.4-104.1z" fill="currentColor"/>
                    </svg>
                    反选
                </button>
                <button id="clearSelectionBtn" class="action-btn" title="清空选择">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M685.4 354.8c0-4.4-3.6-8-8-8l-66 .3L512 465.6l-99.3-118.4-66.1-.3c-4.4 0-8 3.5-8 8 0 1.9.7 3.7 1.9 5.2l130.1 155L340.5 670a8.32 8.32 0 0 0-1.9 5.2c0 4.4 3.6 8 8 8l66.1-.3L512 564.4l99.3 118.4 66 .3c4.4 0 8-3.5 8-8 0-1.9-.7-3.7-1.9-5.2L553.5 515l130.1-155c1.2-1.4 1.8-3.3 1.8-5.2z" fill="currentColor"/>
                        <path d="M512 65C264.6 65 64 265.6 64 513s200.6 448 448 448 448-200.6 448-448S759.4 65 512 65zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="currentColor"/>
                    </svg>
                    清空
                </button>
                <button id="downloadSelectedBtn" class="action-btn action-btn-success">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M505.7 661c3.2 4.4 9.7 4.4 12.9 0l178-246c3.8-5.3 0-12.7-6.5-12.7H643c-10.2 0-19.9 4.9-25.9 13.3L512 558.6 406.8 415.7c-6-8.4-15.7-13.3-25.9-13.3H334c-6.5 0-10.3 7.4-6.5 12.7l178 246z" fill="currentColor"/>
                        <path d="M880 112H144c-17.7 0-32 14.3-32 32v736c0 17.7 14.3 32 32 32h736c17.7 0 32-14.3 32-32V144c0-17.7-14.3-32-32-32zm-40 728H184V184h656v656z" fill="currentColor"/>
                    </svg>
                    下载
                </button>
                <button id="favoriteSelectedBtn" class="action-btn action-btn-warning">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M908.1 353.1l-253.9-36.9L540.7 86.1c-3.1-6.3-8.2-11.4-14.5-14.5-15.8-7.8-35-1.3-42.9 14.5L369.8 316.2l-253.9 36.9c-7 1-13.4 4.3-18.3 9.3-12.3 12.7-12.1 32.9.6 45.3l183.7 179.1-43.4 252.9c-1.2 6.9-.1 14.1 3.2 20.3 8.2 15.6 27.6 21.7 43.2 13.4L512 754l227.1 119.4c6.2 3.3 13.4 4.4 20.3 3.2 17.4-3 29.1-19.5 26.1-36.9l-43.4-252.9 183.7-179.1c5-4.9 8.3-11.3 9.3-18.3 2.7-17.5-9.5-33.7-27-36.3z" fill="currentColor"/>
                    </svg>
                    收藏
                </button>
                <button id="cancelSelectionBtn" class="action-btn action-btn-secondary" title="退出选择模式 (Esc)">
                    <svg viewBox="0 0 1024 1024" width="18" height="18">
                        <path d="M685.4 354.8c0-4.4-3.6-8-8-8l-66 .3L512 465.6l-99.3-118.4-66.1-.3c-4.4 0-8 3.5-8 8 0 1.9.7 3.7 1.9 5.2l130.1 155L340.5 670a8.32 8.32 0 0 0-1.9 5.2c0 4.4 3.6 8 8 8l66.1-.3L512 564.4l99.3 118.4 66 .3c4.4 0 8-3.5 8-8 0-1.9-.7-3.7-1.9-5.2L553.5 515l130.1-155c1.2-1.4 1.8-3.3 1.8-5.2z" fill="currentColor"/>
                    </svg>
                    取消
                </button>
            </div>
        </div>
    </div>

    <script src="Style/js/loveAlbum.js"></script>

    <?php
    include_once 'footer.php';
    ?>
</body>

</html>
