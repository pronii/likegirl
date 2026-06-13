// 恋爱相册主入口模块
const LoveAlbum = {
    init() {
        console.log('🚀 LoveAlbum 初始化开始');
        const $albumGallery = $('#albumGallery');
        console.log('📦 相册容器:', $albumGallery.length);
        if ($albumGallery.length === 0) return;

        LoveAlbumState.reset();
        LoveAlbumCore.loadAlbums();

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
