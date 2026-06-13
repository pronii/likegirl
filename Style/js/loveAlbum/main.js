// 恋爱相册主入口模块
const LoveAlbum = {
    init() {
        // 检查 jQuery 是否加载
        if (typeof jQuery === 'undefined') {
            console.error('❌ jQuery 未加载');
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
                document.getElementById('albumGallery').innerHTML = '<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">jQuery 未加载</div>';
            }
            return;
        }

        // 检查容器是否存在
        const $albumGallery = $('#albumGallery');
        if ($albumGallery.length === 0) {
            return;
        }

        // 检查核心模块是否加载
        if (typeof LoveAlbumState === 'undefined') {
            console.error('❌ LoveAlbumState 模块未加载');
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">LoveAlbumState 模块未加载</div>');
            return;
        }

        if (typeof LoveAlbumCore === 'undefined') {
            console.error('❌ LoveAlbumCore 模块未加载');
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">LoveAlbumCore 模块未加载</div>');
            return;
        }

        // 重置状态
        LoveAlbumState.reset();

        // 加载相册列表
        try {
            LoveAlbumCore.loadAlbums();
        } catch (e) {
            console.error('❌ 加载失败:', e);
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">加载失败: ' + e.message + '</div>');
            return;
        }

        // 绑定事件
        this.bindEvents();
    },

    bindEvents() {
        $('#loadMoreBtn').off('click').on('click', () => LoveAlbumCore.loadPhotos());
        $('#breadcrumb').off('click').on('click', () => LoveAlbumCore.loadAlbums());

        if (LoveAlbumState.isAdminPage) {
            // 选择模式按钮
            $('#selectionModeBtn').off('click').on('click', () => LoveAlbumSelection.toggle());

            // 浮动操作栏
            $('#downloadSelectedBtn').off('click').on('click', () => LoveAlbumSelection.downloadSelected());
            $('#favoriteSelectedBtn').off('click').on('click', () => LoveAlbumSelection.favoriteSelected());
            $('#cancelSelectionBtn').off('click').on('click', () => LoveAlbumSelection.exit());

            // 快捷选择按钮
            $('#selectAllBtn').off('click').on('click', () => LoveAlbumSelection.selectAll());
            $('#invertSelectionBtn').off('click').on('click', () => LoveAlbumSelection.invert());
            $('#clearSelectionBtn').off('click').on('click', () => LoveAlbumSelection.clear());

            // 键盘快捷键
            $(document).off('keydown.photoSelection');
            $(document).on('keydown.photoSelection', function(e) {
                if (!LoveAlbumState.selectionMode) return;

                if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                    e.preventDefault();
                    LoveAlbumSelection.selectAll();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    LoveAlbumSelection.exit();
                } else if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
                    e.preventDefault();
                    LoveAlbumSelection.invert();
                }
            });

            // 拖动框选
            $(document).off('mousedown.dragSelect').on('mousedown.dragSelect', e => LoveAlbumDrag.start(e));
            $(document).off('mousemove.dragSelect').on('mousemove.dragSelect', e => LoveAlbumDrag.move(e));
            $(document).off('mouseup.dragSelect').on('mouseup.dragSelect', e => LoveAlbumDrag.end(e));
        }
    }
};

// 全局初始化函数（向后兼容）
function initLoveAlbum() {
    LoveAlbum.init();
}
