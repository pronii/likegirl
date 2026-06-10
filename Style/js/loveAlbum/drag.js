// 拖动框选模块
const LoveAlbumDrag = {
    createBox() {
        if (!LoveAlbumState.dragSelectionBox) {
            LoveAlbumState.dragSelectionBox = $('<div class="drag-selection-box"></div>');
            $('body').append(LoveAlbumState.dragSelectionBox);
        }
        return LoveAlbumState.dragSelectionBox;
    },

    updateBox(startX, startY, currentX, currentY) {
        const left = Math.min(startX, currentX);
        const top = Math.min(startY, currentY);
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);

        LoveAlbumState.dragSelectionBox.css({
            left: left + 'px',
            top: top + 'px',
            width: width + 'px',
            height: height + 'px',
            display: 'block'
        });
    },

    isPhotoInBox(photoElement, boxLeft, boxTop, boxRight, boxBottom) {
        const $photo = $(photoElement);
        const offset = $photo.offset();
        const photoLeft = offset.left;
        const photoTop = offset.top;
        const photoRight = photoLeft + $photo.outerWidth();
        const photoBottom = photoTop + $photo.outerHeight();

        return !(photoRight < boxLeft || photoLeft > boxRight ||
                 photoBottom < boxTop || photoTop > boxBottom);
    },

    start(e) {
        if (!LoveAlbumState.selectionMode) return;
        if ($(e.target).closest('.photo-checkbox-overlay, .photo-selection-checkbox').length > 0) return;

        const $gallery = $(e.target).closest('#photoGallery');
        if ($gallery.length === 0) return;

        LoveAlbumState.isDragging = false;
        LoveAlbumState.dragStartX = e.pageX;
        LoveAlbumState.dragStartY = e.pageY;
    },

    move(e) {
        if (!LoveAlbumState.selectionMode) return;
        if (LoveAlbumState.dragStartX === 0 && LoveAlbumState.dragStartY === 0) return;

        const currentX = e.pageX;
        const currentY = e.pageY;
        const deltaX = Math.abs(currentX - LoveAlbumState.dragStartX);
        const deltaY = Math.abs(currentY - LoveAlbumState.dragStartY);

        if (!LoveAlbumState.isDragging && (deltaX > 5 || deltaY > 5)) {
            LoveAlbumState.isDragging = true;

            this.createBox();
            LoveAlbumState.dragSelectionBox.css({
                left: LoveAlbumState.dragStartX + 'px',
                top: LoveAlbumState.dragStartY + 'px',
                width: '0px',
                height: '0px',
                display: 'block'
            });

            $('body').addClass('no-select');
        }

        if (!LoveAlbumState.isDragging) return;

        e.preventDefault();

        this.updateBox(LoveAlbumState.dragStartX, LoveAlbumState.dragStartY, currentX, currentY);

        const boxLeft = Math.min(LoveAlbumState.dragStartX, currentX);
        const boxTop = Math.min(LoveAlbumState.dragStartY, currentY);
        const boxRight = Math.max(LoveAlbumState.dragStartX, currentX);
        const boxBottom = Math.max(LoveAlbumState.dragStartY, currentY);

        $('.photo-item').each(function() {
            const $photoItem = $(this);
            const $checkbox = $photoItem.find('.photo-selection-checkbox');
            const photoId = $checkbox.data('photo-id');

            if (LoveAlbumDrag.isPhotoInBox(this, boxLeft, boxTop, boxRight, boxBottom)) {
                if (!$checkbox.prop('checked')) {
                    $checkbox.prop('checked', true);
                    LoveAlbumState.selectedPhotos.add(photoId);
                    $photoItem.addClass('selected');
                }
            }
        });

        LoveAlbumSelection.updateCount();
    },

    end(e) {
        if (!LoveAlbumState.isDragging && LoveAlbumState.dragStartX === 0 && LoveAlbumState.dragStartY === 0) return;

        if (LoveAlbumState.isDragging) {
            if (LoveAlbumState.dragSelectionBox) {
                LoveAlbumState.dragSelectionBox.hide();
            }
            $('body').removeClass('no-select');
        }

        LoveAlbumState.isDragging = false;
        LoveAlbumState.dragStartX = 0;
        LoveAlbumState.dragStartY = 0;
    }
};
