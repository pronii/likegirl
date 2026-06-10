// 选择功能模块
const LoveAlbumSelection = {
    enter() {
        LoveAlbumState.selectionMode = true;
        LoveAlbumState.selectedPhotos.clear();

        $('.photo-checkbox-overlay').show();
        $('#floatingActionBar').addClass('show');
        $('#selectionModeBtn').html(`
            <svg viewBox="0 0 1024 1024" width="20" height="20"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm192.5 301.9l-210.6 292c-12.7 17.7-39 17.7-51.7 0L310.5 484.9c-12.7-17.7-2.6-44.9 18.5-44.9h78.9c10.1 0 19.6 4.7 25.9 12.7l67.3 93.1L724.5 300.8c6.3-8.8 16.5-13.9 27.3-13.9h78.9c21.1 0 31.2 27.2 18.8 44.9z"/></svg>
            退出选择
        `).addClass('active');

        this.bindEvents();
        this.updateCount();
    },

    exit() {
        LoveAlbumState.selectionMode = false;
        LoveAlbumState.selectedPhotos.clear();

        if (LoveAlbumState.isAdminPage) {
            $('.photo-checkbox-overlay').hide();
            $('.photo-selection-checkbox').prop('checked', false);
            $('.photo-item').removeClass('selected');
            $('#floatingActionBar').removeClass('show');
            $('#selectionModeBtn').html(`
                <svg viewBox="0 0 1024 1024" width="20" height="20"><path d="M866.9 169.9L527.1 54.1C523 52.7 517.5 52 512 52s-11 .7-15.1 2.1L157.1 169.9c-8.3 2.8-15.1 12.4-15.1 21.2v482.4c0 8.8 5.7 20.4 12.6 25.9L499.3 968c3.5 2.7 8 4.1 12.6 4.1s9.2-1.4 12.6-4.1l344.7-268.6c6.9-5.4 12.6-17 12.6-25.9V191.1c.2-8.8-6.6-18.3-14.9-21.2zM810 654.3L512 886.5 214 654.3V226.7l298-101.6 298 101.6v427.6z"/><path d="M402.9 528.8l-77.5-77.5c-6.2-6.2-16.4-6.2-22.6 0l-22.6 22.6c-6.2 6.2-6.2 16.4 0 22.6l112.7 112.7c6.2 6.2 16.4 6.2 22.6 0l226.9-226.9c6.2-6.2 6.2-16.4 0-22.6L619.8 337c-6.2-6.2-16.4-6.2-22.6 0L402.9 528.8z"/></svg>
                选择
            `).removeClass('active');
        }
    },

    toggle() {
        LoveAlbumState.selectionMode ? this.exit() : this.enter();
    },

    bindEvents() {
        $(document).off('change', '.photo-selection-checkbox');
        $(document).off('click', '.photo-checkbox-overlay, .photo-selectable');

        $(document).on('click', '.photo-selectable', function(e) {
            if (!LoveAlbumState.selectionMode || LoveAlbumState.isDragging) return;

            e.preventDefault();
            e.stopPropagation();

            const $photoItem = $(this).closest('.photo-item');
            const $checkbox = $photoItem.find('.photo-selection-checkbox');
            const photoId = $checkbox.data('photo-id');
            const currentIndex = $('.photo-item').index($photoItem);

            if (e.shiftKey && LoveAlbumState.lastCheckedIndex !== null) {
                LoveAlbumSelection.handleRange(LoveAlbumState.lastCheckedIndex, currentIndex);
            } else {
                LoveAlbumSelection.toggle($checkbox, photoId, $photoItem);
                LoveAlbumState.lastCheckedIndex = currentIndex;
            }

            LoveAlbumSelection.updateCount();
        });

        $(document).on('change', '.photo-selection-checkbox', function(e) {
            e.stopPropagation();

            const photoId = $(this).data('photo-id');
            const isChecked = $(this).prop('checked');
            const $photoItem = $(this).closest('.photo-item');

            if (isChecked) {
                LoveAlbumState.selectedPhotos.add(photoId);
                $photoItem.addClass('selected');
            } else {
                LoveAlbumState.selectedPhotos.delete(photoId);
                $photoItem.removeClass('selected');
            }

            LoveAlbumState.lastCheckedIndex = $('.photo-item').index($photoItem);
            LoveAlbumSelection.updateCount();
        });

        $(document).on('click', '.photo-checkbox-overlay', e => e.stopPropagation());
    },

    togglePhoto($checkbox, photoId, $photoItem) {
        const newState = !$checkbox.prop('checked');
        $checkbox.prop('checked', newState);

        if (newState) {
            LoveAlbumState.selectedPhotos.add(photoId);
            $photoItem.addClass('selected');
        } else {
            LoveAlbumState.selectedPhotos.delete(photoId);
            $photoItem.removeClass('selected');
        }
    },

    handleRange(startIndex, endIndex) {
        const start = Math.min(startIndex, endIndex);
        const end = Math.max(startIndex, endIndex);
        const $allPhotos = $('.photo-item');

        for (let i = start; i <= end; i++) {
            const $photoItem = $allPhotos.eq(i);
            const $checkbox = $photoItem.find('.photo-selection-checkbox');
            const photoId = $checkbox.data('photo-id');

            $checkbox.prop('checked', true);
            LoveAlbumState.selectedPhotos.add(photoId);
            $photoItem.addClass('selected');
        }
    },

    restoreState() {
        LoveAlbumState.selectedPhotos.forEach(photoId => {
            const $checkbox = $(`.photo-selection-checkbox[data-photo-id="${photoId}"]`);
            if ($checkbox.length > 0) {
                $checkbox.prop('checked', true);
                $checkbox.closest('.photo-item').addClass('selected');
            }
        });
    },

    updateCount() {
        const count = LoveAlbumState.selectedPhotos.size;
        $('#selectionCount').text(count);

        if (count > 0) {
            $('#floatingActionBar .action-buttons').show();
        } else {
            $('#floatingActionBar .action-buttons').hide();
        }
    },

    selectAll() {
        $('.photo-item').each(function() {
            const $photoItem = $(this);
            const $checkbox = $photoItem.find('.photo-selection-checkbox');
            const photoId = $checkbox.data('photo-id');

            $checkbox.prop('checked', true);
            LoveAlbumState.selectedPhotos.add(photoId);
            $photoItem.addClass('selected');
        });

        this.updateCount();
        toastr["info"](`已选择 ${LoveAlbumState.selectedPhotos.size} 张照片`, "Like_Girl");
    },

    invert() {
        $('.photo-item').each(function() {
            const $photoItem = $(this);
            const $checkbox = $photoItem.find('.photo-selection-checkbox');
            const photoId = $checkbox.data('photo-id');
            const newState = !$checkbox.prop('checked');

            $checkbox.prop('checked', newState);

            if (newState) {
                LoveAlbumState.selectedPhotos.add(photoId);
                $photoItem.addClass('selected');
            } else {
                LoveAlbumState.selectedPhotos.delete(photoId);
                $photoItem.removeClass('selected');
            }
        });

        this.updateCount();
        toastr["info"](`当前选择 ${LoveAlbumState.selectedPhotos.size} 张照片`, "Like_Girl");
    },

    clear() {
        $('.photo-selection-checkbox').prop('checked', false);
        $('.photo-item').removeClass('selected');
        LoveAlbumState.selectedPhotos.clear();
        this.updateCount();
    },

    downloadSelected() {
        if (LoveAlbumState.selectedPhotos.size === 0) {
            toastr["warning"]("请先选择要下载的照片！", "Like_Girl");
            return;
        }

        let downloadCount = 0;
        const totalCount = LoveAlbumState.selectedPhotos.size;

        LoveAlbumState.selectedPhotos.forEach(photoId => {
            const photoData = LoveAlbumState.photoDataMap.get(photoId);
            if (photoData && photoData.img) {
                const link = document.createElement('a');
                link.href = photoData.img;
                link.download = `photo_${photoId}_${photoData.date || Date.now()}.jpg`;
                link.target = '_blank';

                setTimeout(() => {
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    downloadCount++;

                    if (downloadCount === totalCount) {
                        toastr["success"](`已开始下载 ${totalCount} 张照片！`, "Like_Girl");
                    }
                }, downloadCount * 500);
            }
        });
    },

    favoriteSelected() {
        if (LoveAlbumState.selectedPhotos.size === 0) {
            toastr["warning"]("请先选择要收藏的照片！", "Like_Girl");
            return;
        }

        const favorites = JSON.parse(localStorage.getItem('favoritePhotos') || '[]');
        LoveAlbumState.selectedPhotos.forEach(photoId => {
            if (!favorites.includes(photoId)) {
                favorites.push(photoId);
            }
        });
        localStorage.setItem('favoritePhotos', JSON.stringify(favorites));

        toastr["success"](`已收藏 ${LoveAlbumState.selectedPhotos.size} 张照片！`, "Like_Girl");
        this.exit();
    }
};

// 全局函数别名
function toggleSelectionMode() { LoveAlbumSelection.toggle(); }
function selectAllPhotos() { LoveAlbumSelection.selectAll(); }
function invertSelection() { LoveAlbumSelection.invert(); }
function clearSelection() { LoveAlbumSelection.clear(); }
function downloadSelectedPhotos() { LoveAlbumSelection.downloadSelected(); }
function favoriteSelectedPhotos() { LoveAlbumSelection.favoriteSelected(); }
function exitSelectionMode() { LoveAlbumSelection.exit(); }
