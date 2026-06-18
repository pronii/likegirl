// 状态管理模块
const LoveAlbumState = {
    currentAlbumId: 0,
    currentAlbumName: '',
    currentPage: 1,
    limit: 9,
    total: 0,
    isLoading: false,
    isAdminPage: window.location.pathname.includes('/admin/'),
    logger: typeof LikeGirlLog !== 'undefined' ? LikeGirlLog : {
        log: function() {},
        warn: function() {}
    },

    // 选择模式状态
    selectionMode: false,
    selectedPhotos: new Set(),
    photoDataMap: new Map(),
    lastCheckedIndex: null,

    // 拖动状态
    isDragging: false,
    dragStartX: 0,
    dragStartY: 0,
    dragSelectionBox: null,

    reset() {
        this.currentPage = 1;
        this.total = 0;
        this.currentAlbumId = 0;
        this.isLoading = false;
        this.selectionMode = false;
        this.selectedPhotos.clear();
        this.photoDataMap.clear();
        this.lastCheckedIndex = null;
    }
};
