// 相册操作模块
const LoveAlbumCore = {
    loadAlbums() {
        const $albumGallery = $('#albumGallery');
        const $loading = $('#loading');

        $loading.show();
        $albumGallery.empty();
        $('#photoGallery').hide();
        $('#albumGallery').show();
        $('#loadMoreBtn').hide();
        $('#breadcrumb').hide();
        $('#currentAlbumName').hide();

        LoveAlbumSelection.exit();
        LoveAlbumState.currentAlbumId = 0;

        $.post('getAlbums.php', function(res) {
            $loading.hide();
            if (res.code === 200 && res.data.length > 0) {
                res.data.forEach(function(album, index) {
                    const coverImg = album.album_cover || 'Style/img/Loading2.gif';
                    const albumHtml = `
                        <div class="album-card col-lg-4 col-md-6 col-sm-12 col-sm-x-12" onclick="LoveAlbumCore.open(${album.id}, '${album.album_name}')" style="cursor: pointer;">
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
                    setTimeout(() => $item.addClass('show'), index * 100);
                });
            } else {
                $albumGallery.html('<div class="col-12 text-center" style="padding: 40px; color: #999;">暂无相册</div>');
            }
        }, 'json').fail(function() {
            $loading.hide();
            toastr["error"]("网络错误，无法加载相册列表！", "Like_Girl");
        });
    },

    open(albumId, albumName) {
        LoveAlbumState.currentAlbumId = albumId;
        LoveAlbumState.currentAlbumName = albumName;
        LoveAlbumState.currentPage = 1;
        LoveAlbumState.total = 0;

        LoveAlbumSelection.exit();

        $('#albumGallery').hide();
        $('#photoGallery').show().empty();
        $('#breadcrumb').show();
        $('#currentAlbumName').show().text(' > ' + albumName);
        $('#loadMoreBtn').show();

        if (LoveAlbumState.isAdminPage) {
            $('#selectionModeBtn').show();
        }

        this.loadPhotos();
    },

    loadPhotos() {
        if (LoveAlbumState.isLoading) return;
        LoveAlbumState.isLoading = true;

        const $loading = $('#loading');
        const $loadBtn = $('#loadMoreBtn');

        $loading.show();
        $loadBtn.prop('disabled', true);

        const postData = {
            page: LoveAlbumState.currentPage,
            limit: 12, // 增加每页数量
            album_id: LoveAlbumState.currentAlbumId
        };

        $.post('getPhotos.php', postData, function(res) {
            console.log('📷 照片加载响应:', res);
            LoveAlbumState.isLoading = false;
            $loading.hide();

            if (res.code === 200) {
                console.log('✅ 加载照片数量:', res.data.length);
                LoveAlbumState.total = res.total;

                res.data.forEach(photo => {
                    if (photo.id) LoveAlbumState.photoDataMap.set(photo.id, photo);
                });

                LoveAlbumCore.showPhotos(res.data);

                FunLazy({
                    placeholder: 'Style/img/Loading2.gif',
                    effect: 'show',
                    strictLazyMode: false,
                    useErrorImagePlaceholder: 'Style/img/error.svg'
                });

                LoveAlbumState.currentPage++;

                const photoCount = $('#photoGallery .photo-item').length;

                if (photoCount >= LoveAlbumState.total) {
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
                $loadBtn.prop('disabled', false);
            }
        }, 'json').fail(function() {
            LoveAlbumState.isLoading = false;
            $loading.hide();
            $loadBtn.prop('disabled', false);
            toastr["error"]("网络错误，无法加载照片！", "Like_Girl");
        });
    },

    showPhotos(photos) {
        console.log('🖼️ 开始渲染照片:', photos.length);
        const $gallery = $('#photoGallery');
        const startIndex = $gallery.children().length;
        const fragment = document.createDocumentFragment();

        photos.forEach(photo => {
            const div = document.createElement('div');
            div.innerHTML = this.createPhotoElement(photo);
            fragment.appendChild(div.firstElementChild);
        });

        $gallery[0].appendChild(fragment);
        console.log('✅ DOM 插入完成，当前照片总数:', $gallery.children().length);

        const newItems = $gallery.children().slice(startIndex);
        newItems.each(function(index) {
            const $item = $(this);
            setTimeout(() => $item.addClass('show'), index * 50);
        });

        if (LoveAlbumState.selectionMode) {
            LoveAlbumSelection.restoreState();
        }
    },

    createPhotoElement(photo) {
        const photoId = photo.id || `photo_${Date.now()}_${Math.random()}`;

        return `
            <div class="img_card col-lg-4 col-md-6 col-sm-12 col-sm-x-12 photo-item" data-photo-id="${photoId}">
                <div class="love_img photo-selectable">
                    <div class="photo-checkbox-overlay" style="display: none;">
                        <input type="checkbox" class="photo-selection-checkbox" data-photo-id="${photoId}">
                        <label class="checkbox-label" for="checkbox-${photoId}"></label>
                    </div>
                    <img class="spotlight" src="Style/img/Loading2.gif" data-funlazy="${photo.img}" alt="${photo.text}" data-description="${photo.date}" onclick="LoveAlbumCore.forceLoadAllImages(event)">

                    <div class="words" data-tip="${photo.text}" data-tip-position="top">
                        <i>${photo.date}</i>
                        <span>${photo.text}</span>
                    </div>
                </div>
            </div>
        `;
    },

    forceLoadAllImages(event) {
        $('.spotlight[data-funlazy]').each(function() {
            const $img = $(this);
            const realSrc = $img.attr('data-funlazy');
            if (realSrc) {
                $img.attr('src', realSrc);
                $img.removeAttr('data-funlazy');
            }
        });
    }
};

// 全局函数别名（向后兼容）
function loadAlbums() { LoveAlbumCore.loadAlbums(); }
function openAlbum(id, name) { LoveAlbumCore.open(id, name); }
function showAlbums() { LoveAlbumCore.loadAlbums(); }
function loadPhotos() { LoveAlbumCore.loadPhotos(); }
