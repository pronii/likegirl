// 恋爱相册主入口模块
const LoveAlbum = {
    init() {
        console.log('🚀 LoveAlbum.init() 开始执行');
        console.log('🕐 执行时间:', new Date().toLocaleTimeString());
        console.log('📄 document.readyState:', document.readyState);

        // 检查 jQuery 是否加载
        console.log('🔍 检查 jQuery...');
        if (typeof jQuery === 'undefined') {
            console.error('❌ jQuery 未加载，无法初始化');
            // 尝试隐藏loading
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
                document.getElementById('albumGallery').innerHTML = '<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">jQuery 未加载</div>';
            }
            return;
        }
        console.log('✅ jQuery 版本:', jQuery.fn.jquery);

        // 检查容器是否存在
        console.log('🔍 检查相册容器...');
        const $albumGallery = $('#albumGallery');
        console.log('📦 #albumGallery 元素数量:', $albumGallery.length);

        if ($albumGallery.length > 0) {
            console.log('📐 容器尺寸:', $albumGallery.width() + 'x' + $albumGallery.height());
            console.log('👁 容器可见性:', $albumGallery.is(':visible'));
        }

        if ($albumGallery.length === 0) {
            console.warn('⚠️ 未找到 #albumGallery 容器，可能不在相册页面');
            return;
        }

        // 检查核心模块是否加载
        console.log('🔍 检查核心模块...');
        if (typeof LoveAlbumState === 'undefined') {
            console.error('❌ LoveAlbumState 模块未加载');
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">LoveAlbumState 模块未加载</div>');
            return;
        }
        console.log('✅ LoveAlbumState 已加载');

        if (typeof LoveAlbumCore === 'undefined') {
            console.error('❌ LoveAlbumCore 模块未加载');
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">LoveAlbumCore 模块未加载</div>');
            return;
        }
        console.log('✅ LoveAlbumCore 已加载');

        console.log('✅ 所有前置检查通过，开始加载相册');

        // 重置状态
        console.log('🔄 重置状态...');
        LoveAlbumState.reset();

        // 加载相册列表
        console.log('📂 调用 LoveAlbumCore.loadAlbums()...');
        try {
            LoveAlbumCore.loadAlbums();
        } catch (e) {
            console.error('❌ loadAlbums 执行出错:', e);
            $('#loading').hide();
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">加载失败: ' + e.message + '</div>');
            return;
        }

        // 绑定事件
        console.log('🔗 绑定事件...');
        this.bindEvents();

        console.log('✅ LoveAlbum.init() 完成');
    },

    bindEvents() {
        console.log('🔗 bindEvents() 开始');
        $('#loadMoreBtn').off('click').on('click', () => LoveAlbumCore.loadPhotos());
        $('#breadcrumb').off('click').on('click', () => LoveAlbumCore.loadAlbums());

        if (LoveAlbumState.isAdminPage) {
            console.log('👤 检测到管理员页面，绑定管理员事件');
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
        console.log('✅ bindEvents() 完成');
    }
};

// 全局初始化函数（向后兼容）
function initLoveAlbum() {
    console.log('📞 调用 initLoveAlbum()');
    LoveAlbum.init();
}
