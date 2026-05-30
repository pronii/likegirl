// ========== 恋爱相册 - 文件夹式功能 ==========
console.log('[LoveAlbum] 脚本已加载');

let currentAlbumId = 0;
let currentAlbumName = '';
let currentPage = 1;
const limit = 9;
let total = 0;
let isLoading = false;

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
    });
}

// 打开相册
function openAlbum(albumId, albumName) {
    console.log('[LoveAlbum] 打开相册:', albumId, albumName);
    currentAlbumId = albumId;
    currentAlbumName = albumName;
    currentPage = 1;
    total = 0;
    
    $('#albumGallery').hide();
    $('#photoGallery').show().empty();
    $('#breadcrumb').show();
    $('#currentAlbumName').show().text(' > ' + albumName);
    $('#loadMoreBtn').show();
    
    loadPhotos();
}

// 显示所有相册（面包屑返回）
function showAlbums() {
    console.log('[LoveAlbum] 返回相册列表');
    currentAlbumId = 0;
    currentPage = 1;
    total = 0;
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
}

// 创建照片元素
function createPhotoElement(photo) {
    console.log('[LoveAlbum] 创建照片元素:', photo.img);
    return `
        <div class="img_card col-lg-4 col-md-6 col-sm-12 col-sm-x-12 photo-item">
            <div class="love_img">
                <img class="spotlight" src="Style/img/Loading2.gif" data-funlazy="${photo.img}" alt="${photo.text}" data-description="${photo.date}">
                
                <div class="words" data-tip="${photo.text}" data-tip-position="top">
                    <i>${photo.date}</i>
                    <span>${photo.text}</span>
                </div>
            </div>
        </div>
    `;
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
    
    console.log('[LoveAlbum] 开始初始化，加载相册列表');
    loadAlbums();
    
    $('#loadMoreBtn').off('click').on('click', loadPhotos);
    $('#breadcrumb').off('click').on('click', showAlbums);
}
