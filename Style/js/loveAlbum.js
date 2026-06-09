// ========== 恋爱相册 - 文件夹式功能 ==========
console.log('[LoveAlbum] 脚本已加载');

let currentAlbumId = 0;
let currentAlbumName = '';
let currentPage = 1;
const limit = 9;
let total = 0;
let isLoading = false;

// 检测是否为管理员页面（只有管理员页面才启用选择功能）
const isAdminPage = window.location.pathname.includes('/admin/');
console.log('[LoveAlbum] 当前页面类型:', isAdminPage ? '管理员后台' : '前台浏览');

// 选择模式状态管理（仅管理员页面使用）
let selectionMode = false;
let selectedPhotos = new Set(); // 使用 Set 存储选中的照片ID，跨页保持
let photoDataMap = new Map(); // 存储照片数据，用于下载和收藏
let lastCheckedIndex = null; // 用于 Shift+点击范围选择

// 拖动框选状态（仅管理员页面使用）
let isDragging = false;
let dragStartX = 0;
let dragStartY = 0;
let dragSelectionBox = null;

// 注意：initLoveAlbum() 由 footer.php 和 head.php(Pjax回调) 统一调用

// 加载相册列表
function loadAlbums() {
    console.log('[LoveAlbum] 加载相册列表...');
    const $albumGallery = $('#albumGallery');
    const $loading = $('#loading');

    $loading.show();
    $albumGallery.empty();
    $('#photoGallery').hide();
    $('#albumGallery').show();
    $('#loadMoreBtn').hide();
    $('#breadcrumb').hide();
    $('#currentAlbumName').hide();

    // 退出选择模式
    exitSelectionMode();
    currentAlbumId = 0;

    $.post('getAlbums.php', function(res) {
        console.log('[LoveAlbum] 相册列表响应:', res);
        $loading.hide();
        if (res.code === 200 && res.data.length > 0) {
            res.data.forEach(function(album, index) {
                const coverImg = album.album_cover || 'Style/img/Loading2.gif';
                const albumHtml = `
                    <div class="album-card col-lg-4 col-md-6 col-sm-12 col-sm-x-12" onclick="openAlbum(${album.id}, '${album.album_name}')" style="cursor: pointer;">
                        <div class="love_img album-folder">
                            <div class="album-cover">
                                <img src="${coverImg}" alt="${album.album_name}" onerror="this.src='Style/img/Loading2.gif'">
                            </div>
                            <div class="album-info">
                                <h4 class="album-title">${album.album_name}</h4>
                                <p class="album-count">${album.photo_count || 0} 张照片</p>
                                ${album.album_desc ? `<p class="album-desc">${album.album_desc}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                $albumGallery.append(albumHtml);
            });

            $albumGallery.children().each(function(index) {
                const $item = $(this);
                setTimeout(function() {
                    $item.addClass('show');
                }, index * 100);
            });
        } else {
            $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #999;">暂无相册</div>');
        }
    }, 'json').fail(function(err) {
        console.error('[LoveAlbum] 相册列表请求失败:', err);
        $loading.hide();
        toastr["error"]("网络错误，无法加载相册列表！", "Like_Girl");
    });
}

// 打开相册
function openAlbum(albumId, albumName) {
    console.log('[LoveAlbum] 打开相册:', albumId, albumName);
    currentAlbumId = albumId;
    currentAlbumName = albumName;
    currentPage = 1;
    total = 0;

    // 重置选择状态
    exitSelectionMode();

    $('#albumGallery').hide();
    $('#photoGallery').show().empty();
    $('#breadcrumb').show();
    $('#currentAlbumName').show().text(' > ' + albumName);
    $('#loadMoreBtn').show();

    // 只在管理员页面显示选择模式按钮
    if (isAdminPage) {
        $('#selectionModeBtn').show();
    }

    loadPhotos();
}

// 显示所有相册（面包屑返回）
function showAlbums() {
    console.log('[LoveAlbum] 返回相册列表');
    currentAlbumId = 0;
    currentPage = 1;
    total = 0;
    exitSelectionMode();
    $('#selectionModeBtn').hide(); // 隐藏选择模式按钮
    loadAlbums();
}

// 加载照片
function loadPhotos() {
    console.log('[LoveAlbum] 加载照片, album_id:', currentAlbumId, 'page:', currentPage);
    if (isLoading) {
        console.log('[LoveAlbum] 正在加载中，跳过');
        return;
    }
    isLoading = true;

    const $loading = $('#loading');
    const $loadBtn = $('#loadMoreBtn');

    $loading.show();
    $loadBtn.prop('disabled', true);

    const postData = {
        page: currentPage,
        limit: limit,
        album_id: currentAlbumId
    };
    console.log('[LoveAlbum] 发送请求参数:', postData);

    $.post('getPhotos.php', postData, function(res) {
        console.log('[LoveAlbum] 照片响应:', res);
        isLoading = false;
        $loading.hide();

        if (res.code === 200) {
            total = res.total;
            console.log('[LoveAlbum] 获取到', res.data.length, '张照片, 总数:', total);

            // 存储照片数据到 Map
            res.data.forEach(function(photo) {
                if (photo.id) {
                    photoDataMap.set(photo.id, photo);
                }
            });

            showPhotos(res.data);

            FunLazy({
                placeholder: 'Style/img/Loading2.gif',
                effect: 'show',
                strictLazyMode: false,
                useErrorImagePlaceholder: 'https://img.gejiba.com/images/dbc7f2562e051afc3c39f916689ba5f0.png'
            });

            currentPage++;

            const photoCount = $('#photoGallery .photo-item').length;
            console.log('[LoveAlbum] 当前显示照片数:', photoCount, '/ 总数:', total);

            if (photoCount >= total) {
                $loadBtn.html(`
                    <svg viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" width="20" height="20"><path d="M866.944 256.768c-95.488-95.488-250.496-95.488-345.984 0l-13.312 13.312-9.472-9.472c-93.824-93.824-246.656-100.736-343.68-10.368-101.888 94.976-104.064 254.592-6.4 352.256l13.568 13.568 299.264 299.264c25.728 25.728 67.584 25.728 93.44 0l312.576-312.576c95.488-95.488 95.488-250.368 0-345.984z" fill="#333"/></svg>
                    暂无更多数据
                `).prop('disabled', true);
            } else {
                $loadBtn.prop('disabled', false).html(`
                    <svg t="1756817125714" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4311" width="256" height="256"><path d="M849.799529 168.357647A481.882353 481.882353 0 1 0 993.882353 512a90.352941 90.352941 0 0 0-180.705882 0 301.176471 301.176471 0 1 1-90.051765-214.799059 90.352941 90.352941 0 1 0 126.674823-128.843294z" p-id="4312"></path></svg>
                    加载更多
                `);
            }
        } else {
            console.error('[LoveAlbum] 照片响应code异常:', res.code);
            $loadBtn.prop('disabled', false);
        }
    }, 'json').fail(function(err) {
        console.error('[LoveAlbum] 照片请求失败:', err);
        isLoading = false;
        $loading.hide();
        $loadBtn.prop('disabled', false);
        toastr["error"]("网络错误，无法加载照片！", "Like_Girl");
    });
}

// 显示照片
function showPhotos(photos) {
    console.log('[LoveAlbum] 渲染照片，数量:', photos.length);
    const $gallery = $('#photoGallery');
    const startIndex = $gallery.children().length;

    photos.forEach(function(photo, index) {
        const photoElement = createPhotoElement(photo);
        $gallery.append(photoElement);
    });

    const newItems = $gallery.children().slice(startIndex);
    newItems.each(function(index) {
        const $item = $(this);
        setTimeout(function() {
            $item.addClass('show');
        }, index * 200);
    });

    // 如果在选择模式下，恢复已选中的照片状态
    if (selectionMode) {
        restoreSelectionState();
    }
}

// 创建照片元素
function createPhotoElement(photo) {
    console.log('[LoveAlbum] 创建照片元素:', photo.img);
    const photoId = photo.id || `photo_${Date.now()}_${Math.random()}`;

    return `
        <div class="img_card col-lg-4 col-md-6 col-sm-12 col-sm-x-12 photo-item" data-photo-id="${photoId}">
            <div class="love_img photo-selectable">
                <div class="photo-checkbox-overlay" style="display: none;">
                    <input type="checkbox" class="photo-selection-checkbox" data-photo-id="${photoId}">
                    <label class="checkbox-label" for="checkbox-${photoId}"></label>
                </div>
                <img class="spotlight" src="Style/img/Loading2.gif" data-funlazy="${photo.img}" alt="${photo.text}" data-description="${photo.date}">

                <div class="words" data-tip="${photo.text}" data-tip-position="top">
                    <i>${photo.date}</i>
                    <span>${photo.text}</span>
                </div>
            </div>
        </div>
    `;
}

// ========== 选择模式功能 ==========

// 进入选择模式
function enterSelectionMode() {
    console.log('[LoveAlbum] 进入选择模式');
    selectionMode = true;
    selectedPhotos.clear(); // 清空之前的选择

    // 显示所有照片的复选框
    $('.photo-checkbox-overlay').show();

    // 显示浮动操作栏
    $('#floatingActionBar').addClass('show');

    // 更新选择模式按钮
    $('#selectionModeBtn').html(`
        <svg viewBox="0 0 1024 1024" width="20" height="20"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm192.5 301.9l-210.6 292c-12.7 17.7-39 17.7-51.7 0L310.5 484.9c-12.7-17.7-2.6-44.9 18.5-44.9h78.9c10.1 0 19.6 4.7 25.9 12.7l67.3 93.1L724.5 300.8c6.3-8.8 16.5-13.9 27.3-13.9h78.9c21.1 0 31.2 27.2 18.8 44.9z"/></svg>
        退出选择
    `).addClass('active');

    // 绑定复选框点击事件
    bindCheckboxEvents();

    // 更新计数
    updateSelectionCount();
}

// 退出选择模式
function exitSelectionMode() {
    console.log('[LoveAlbum] 退出选择模式');
    selectionMode = false;
    selectedPhotos.clear();

    // 只在管理员页面操作选择相关的UI元素
    if (isAdminPage) {
        // 隐藏所有照片的复选框
        $('.photo-checkbox-overlay').hide();
        $('.photo-selection-checkbox').prop('checked', false);
        $('.photo-item').removeClass('selected');

        // 隐藏浮动操作栏
        $('#floatingActionBar').removeClass('show');

        // 恢复选择模式按钮
        $('#selectionModeBtn').html(`
            <svg viewBox="0 0 1024 1024" width="20" height="20"><path d="M866.9 169.9L527.1 54.1C523 52.7 517.5 52 512 52s-11 .7-15.1 2.1L157.1 169.9c-8.3 2.8-15.1 12.4-15.1 21.2v482.4c0 8.8 5.7 20.4 12.6 25.9L499.3 968c3.5 2.7 8 4.1 12.6 4.1s9.2-1.4 12.6-4.1l344.7-268.6c6.9-5.4 12.6-17 12.6-25.9V191.1c.2-8.8-6.6-18.3-14.9-21.2zM810 654.3L512 886.5 214 654.3V226.7l298-101.6 298 101.6v427.6z"/><path d="M402.9 528.8l-77.5-77.5c-6.2-6.2-16.4-6.2-22.6 0l-22.6 22.6c-6.2 6.2-6.2 16.4 0 22.6l112.7 112.7c6.2 6.2 16.4 6.2 22.6 0l226.9-226.9c6.2-6.2 6.2-16.4 0-22.6L619.8 337c-6.2-6.2-16.4-6.2-22.6 0L402.9 528.8z"/></svg>
            选择
        `).removeClass('active');
    }
}

// 切换选择模式
function toggleSelectionMode() {
    if (selectionMode) {
        exitSelectionMode();
    } else {
        enterSelectionMode();
    }
}

// 绑定复选框事件
function bindCheckboxEvents() {
    // 移除旧的事件监听，避免重复绑定
    $(document).off('change', '.photo-selection-checkbox');
    $(document).off('click', '.photo-checkbox-overlay, .photo-selectable');

    // 使用事件委托处理照片项点击（支持 Shift/Ctrl 修饰键）
    $(document).on('click', '.photo-selectable', function(e) {
        if (!selectionMode) return;

        // 如果正在拖动或刚结束拖动，不处理点击
        if (isDragging) return;

        e.preventDefault();
        e.stopPropagation();

        const $photoItem = $(this).closest('.photo-item');
        const $checkbox = $photoItem.find('.photo-selection-checkbox');
        const photoId = $checkbox.data('photo-id');
        const currentIndex = $('.photo-item').index($photoItem);

        // Shift + 点击：范围选择
        if (e.shiftKey && lastCheckedIndex !== null) {
            handleRangeSelection(lastCheckedIndex, currentIndex);
        }
        // Ctrl/Cmd + 点击：切换当前项
        else if (e.ctrlKey || e.metaKey) {
            togglePhotoSelection($checkbox, photoId, $photoItem);
            lastCheckedIndex = currentIndex;
        }
        // 普通点击：切换当前项
        else {
            togglePhotoSelection($checkbox, photoId, $photoItem);
            lastCheckedIndex = currentIndex;
        }

        updateSelectionCount();
    });

    // 复选框变化事件（用于直接点击复选框）
    $(document).on('change', '.photo-selection-checkbox', function(e) {
        e.stopPropagation();

        const photoId = $(this).data('photo-id');
        const isChecked = $(this).prop('checked');
        const $photoItem = $(this).closest('.photo-item');

        console.log('[LoveAlbum] 照片选择状态改变:', photoId, isChecked);

        if (isChecked) {
            selectedPhotos.add(photoId);
            $photoItem.addClass('selected');
        } else {
            selectedPhotos.delete(photoId);
            $photoItem.removeClass('selected');
        }

        lastCheckedIndex = $('.photo-item').index($photoItem);
        updateSelectionCount();
    });

    // 防止复选框区域触发图片查看
    $(document).on('click', '.photo-checkbox-overlay', function(e) {
        e.stopPropagation();
    });
}

// 切换单个照片选择状态
function togglePhotoSelection($checkbox, photoId, $photoItem) {
    const isCurrentlyChecked = $checkbox.prop('checked');
    const newState = !isCurrentlyChecked;

    $checkbox.prop('checked', newState);

    if (newState) {
        selectedPhotos.add(photoId);
        $photoItem.addClass('selected');
    } else {
        selectedPhotos.delete(photoId);
        $photoItem.removeClass('selected');
    }

    console.log('[LoveAlbum] 切换照片选择:', photoId, newState);
}

// 范围选择处理（Shift + 点击）
function handleRangeSelection(startIndex, endIndex) {
    console.log('[LoveAlbum] 范围选择:', startIndex, 'to', endIndex);

    const start = Math.min(startIndex, endIndex);
    const end = Math.max(startIndex, endIndex);

    const $allPhotos = $('.photo-item');

    for (let i = start; i <= end; i++) {
        const $photoItem = $allPhotos.eq(i);
        const $checkbox = $photoItem.find('.photo-selection-checkbox');
        const photoId = $checkbox.data('photo-id');

        // 范围选择时全部选中
        $checkbox.prop('checked', true);
        selectedPhotos.add(photoId);
        $photoItem.addClass('selected');
    }
}

// 恢复选择状态（跨页保持）
function restoreSelectionState() {
    console.log('[LoveAlbum] 恢复选择状态，已选:', selectedPhotos.size);

    selectedPhotos.forEach(function(photoId) {
        const $checkbox = $(`.photo-selection-checkbox[data-photo-id="${photoId}"]`);
        if ($checkbox.length > 0) {
            $checkbox.prop('checked', true);
            $checkbox.closest('.photo-item').addClass('selected');
        }
    });
}

// 更新选择计数
function updateSelectionCount() {
    const count = selectedPhotos.size;
    $('#selectionCount').text(count);

    console.log('[LoveAlbum] 更新选择计数:', count);

    // 根据选择数量显示/隐藏操作按钮
    if (count > 0) {
        $('#floatingActionBar .action-buttons').show();
    } else {
        $('#floatingActionBar .action-buttons').hide();
    }
}

// 全选当前页所有照片
function selectAllPhotos() {
    console.log('[LoveAlbum] 全选照片');

    $('.photo-item').each(function() {
        const $photoItem = $(this);
        const $checkbox = $photoItem.find('.photo-selection-checkbox');
        const photoId = $checkbox.data('photo-id');

        $checkbox.prop('checked', true);
        selectedPhotos.add(photoId);
        $photoItem.addClass('selected');
    });

    updateSelectionCount();
    toastr["info"](`已选择 ${selectedPhotos.size} 张照片`, "Like_Girl");
}

// 反选当前页所有照片
function invertSelection() {
    console.log('[LoveAlbum] 反选照片');

    $('.photo-item').each(function() {
        const $photoItem = $(this);
        const $checkbox = $photoItem.find('.photo-selection-checkbox');
        const photoId = $checkbox.data('photo-id');

        const isChecked = $checkbox.prop('checked');
        const newState = !isChecked;

        $checkbox.prop('checked', newState);

        if (newState) {
            selectedPhotos.add(photoId);
            $photoItem.addClass('selected');
        } else {
            selectedPhotos.delete(photoId);
            $photoItem.removeClass('selected');
        }
    });

    updateSelectionCount();
    toastr["info"](`当前选择 ${selectedPhotos.size} 张照片`, "Like_Girl");
}

// 清空所有选择
function clearSelection() {
    console.log('[LoveAlbum] 清空选择');

    $('.photo-selection-checkbox').prop('checked', false);
    $('.photo-item').removeClass('selected');
    selectedPhotos.clear();

    updateSelectionCount();
}

// ========== 拖动框选功能 ==========

// 创建选择框
function createSelectionBox() {
    if (!dragSelectionBox) {
        dragSelectionBox = $('<div class="drag-selection-box"></div>');
        $('body').append(dragSelectionBox);
    }
    return dragSelectionBox;
}

// 更新选择框位置和大小
function updateSelectionBox(startX, startY, currentX, currentY) {
    const left = Math.min(startX, currentX);
    const top = Math.min(startY, currentY);
    const width = Math.abs(currentX - startX);
    const height = Math.abs(currentY - startY);

    dragSelectionBox.css({
        left: left + 'px',
        top: top + 'px',
        width: width + 'px',
        height: height + 'px',
        display: 'block'
    });
}

// 检查照片是否在选择框内
function isPhotoInSelectionBox(photoElement, boxLeft, boxTop, boxRight, boxBottom) {
    const $photo = $(photoElement);
    const offset = $photo.offset();
    const photoLeft = offset.left;
    const photoTop = offset.top;
    const photoRight = photoLeft + $photo.outerWidth();
    const photoBottom = photoTop + $photo.outerHeight();

    // 检查是否有交集
    return !(photoRight < boxLeft ||
             photoLeft > boxRight ||
             photoBottom < boxTop ||
             photoTop > boxBottom);
}

// 开始拖动选择
function startDragSelection(e) {
    if (!selectionMode) return;

    // 不要在复选框上触发拖动
    if ($(e.target).closest('.photo-checkbox-overlay, .photo-selection-checkbox').length > 0) return;

    // 只有在photoGallery区域内才启用拖动
    const $gallery = $(e.target).closest('#photoGallery');
    if ($gallery.length === 0) return;

    // 记录起始位置，但不立即开始拖动（等移动一定距离后再开始）
    isDragging = false;
    dragStartX = e.pageX;
    dragStartY = e.pageY;

    console.log('[LoveAlbum] 记录拖动起始位置', dragStartX, dragStartY);
}

// 拖动过程中
function onDragSelection(e) {
    if (!selectionMode) return;
    if (dragStartX === 0 && dragStartY === 0) return; // 没有记录起始位置

    const currentX = e.pageX;
    const currentY = e.pageY;
    const deltaX = Math.abs(currentX - dragStartX);
    const deltaY = Math.abs(currentY - dragStartY);

    // 只有移动超过5px才认为是拖动（避免误触）
    if (!isDragging && (deltaX > 5 || deltaY > 5)) {
        isDragging = true;

        // 创建并显示选择框
        createSelectionBox();
        dragSelectionBox.css({
            left: dragStartX + 'px',
            top: dragStartY + 'px',
            width: '0px',
            height: '0px',
            display: 'block'
        });

        // 禁用文本选择
        $('body').addClass('no-select');

        console.log('[LoveAlbum] 开始拖动选择', dragStartX, dragStartY);
    }

    if (!isDragging) return;

    e.preventDefault();

    // 更新选择框
    updateSelectionBox(dragStartX, dragStartY, currentX, currentY);

    // 计算选择框范围
    const boxLeft = Math.min(dragStartX, currentX);
    const boxTop = Math.min(dragStartY, currentY);
    const boxRight = Math.max(dragStartX, currentX);
    const boxBottom = Math.max(dragStartY, currentY);

    // 检查每个照片是否在选择框内
    $('.photo-item').each(function() {
        const $photoItem = $(this);
        const $checkbox = $photoItem.find('.photo-selection-checkbox');
        const photoId = $checkbox.data('photo-id');

        if (isPhotoInSelectionBox(this, boxLeft, boxTop, boxRight, boxBottom)) {
            // 在选择框内，选中
            if (!$checkbox.prop('checked')) {
                $checkbox.prop('checked', true);
                selectedPhotos.add(photoId);
                $photoItem.addClass('selected');
            }
        }
    });

    updateSelectionCount();
}

// 结束拖动选择
function endDragSelection(e) {
    if (!isDragging && dragStartX === 0 && dragStartY === 0) return;

    // 如果确实在拖动，则隐藏选择框
    if (isDragging) {
        // 隐藏选择框
        if (dragSelectionBox) {
            dragSelectionBox.hide();
        }

        // 恢复文本选择
        $('body').removeClass('no-select');

        console.log('[LoveAlbum] 结束拖动选择，已选:', selectedPhotos.size);
    }

    // 重置状态
    isDragging = false;
    dragStartX = 0;
    dragStartY = 0;
}

// 批量下载选中的照片
function downloadSelectedPhotos() {
    if (selectedPhotos.size === 0) {
        toastr["warning"]("请先选择要下载的照片！", "Like_Girl");
        return;
    }

    console.log('[LoveAlbum] 批量下载照片，数量:', selectedPhotos.size);

    let downloadCount = 0;
    const totalCount = selectedPhotos.size;

    selectedPhotos.forEach(function(photoId) {
        const photoData = photoDataMap.get(photoId);
        if (photoData && photoData.img) {
            // 创建下载链接
            const link = document.createElement('a');
            link.href = photoData.img;
            link.download = `photo_${photoId}_${photoData.date || Date.now()}.jpg`;
            link.target = '_blank';

            // 触发下载（延迟以避免浏览器阻止多个下载）
            setTimeout(function() {
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                downloadCount++;

                if (downloadCount === totalCount) {
                    toastr["success"](`已开始下载 ${totalCount} 张照片！`, "Like_Girl");
                }
            }, downloadCount * 500); // 每个下载间隔500ms
        }
    });
}

// 收藏选中的照片（示例功能，可根据需求实现）
function favoriteSelectedPhotos() {
    if (selectedPhotos.size === 0) {
        toastr["warning"]("请先选择要收藏的照片！", "Like_Girl");
        return;
    }

    console.log('[LoveAlbum] 收藏照片，数量:', selectedPhotos.size);

    // 这里可以实现收藏功能，例如：
    // 1. 发送AJAX请求到后端，标记照片为收藏
    // 2. 或者使用localStorage保存收藏的照片ID

    // 示例：使用localStorage
    const favorites = JSON.parse(localStorage.getItem('favoritePhotos') || '[]');
    selectedPhotos.forEach(function(photoId) {
        if (!favorites.includes(photoId)) {
            favorites.push(photoId);
        }
    });
    localStorage.setItem('favoritePhotos', JSON.stringify(favorites));

    toastr["success"](`已收藏 ${selectedPhotos.size} 张照片！`, "Like_Girl");
    exitSelectionMode();
}

// 初始化（兼容 Pjax）
function initLoveAlbum() {
    console.log('[LoveAlbum] initLoveAlbum() 被调用');
    const $albumGallery = $('#albumGallery');
    if ($albumGallery.length === 0) {
        console.log('[LoveAlbum] 非相册页面，跳过初始化');
        return;
    }

    // 重置
    currentPage = 1;
    total = 0;
    currentAlbumId = 0;
    isLoading = false;
    selectionMode = false;
    selectedPhotos.clear();
    photoDataMap.clear();
    lastCheckedIndex = null;

    console.log('[LoveAlbum] 开始初始化，加载相册列表');
    loadAlbums();

    $('#loadMoreBtn').off('click').on('click', loadPhotos);
    $('#breadcrumb').off('click').on('click', showAlbums);

    // 只在管理员页面绑定选择功能
    if (isAdminPage) {
        console.log('[LoveAlbum] 管理员页面 - 启用选择功能');

        // 绑定选择模式按钮
        $('#selectionModeBtn').off('click').on('click', toggleSelectionMode);

        // 绑定浮动操作栏按钮
        $('#downloadSelectedBtn').off('click').on('click', downloadSelectedPhotos);
        $('#favoriteSelectedBtn').off('click').on('click', favoriteSelectedPhotos);
        $('#cancelSelectionBtn').off('click').on('click', exitSelectionMode);

        // 绑定快捷选择按钮
        $('#selectAllBtn').off('click').on('click', selectAllPhotos);
        $('#invertSelectionBtn').off('click').on('click', invertSelection);
        $('#clearSelectionBtn').off('click').on('click', clearSelection);

        // 键盘快捷键支持
        $(document).off('keydown.photoSelection');
        $(document).on('keydown.photoSelection', function(e) {
            if (!selectionMode) return;

            // Ctrl/Cmd + A: 全选
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                selectAllPhotos();
            }
            // Escape: 退出选择模式
            else if (e.key === 'Escape') {
                e.preventDefault();
                exitSelectionMode();
            }
            // Ctrl/Cmd + I: 反选
            else if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
                e.preventDefault();
                invertSelection();
            }
        });

        // 拖动框选事件绑定
        $(document).off('mousedown.dragSelect');
        $(document).off('mousemove.dragSelect');
        $(document).off('mouseup.dragSelect');

        $(document).on('mousedown.dragSelect', function(e) {
            startDragSelection(e);
        });

        $(document).on('mousemove.dragSelect', function(e) {
            onDragSelection(e);
        });

        $(document).on('mouseup.dragSelect', function(e) {
            endDragSelection(e);
        });
    } else {
        console.log('[LoveAlbum] 前台页面 - 选择功能已禁用');
    }
}
