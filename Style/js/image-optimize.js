// 前端图片优化：懒加载 + 自适应压缩
(function() {
    // 判断图片是否在视口内
    function isInViewport(el) {
        const rect = el.getBoundingClientRect();
        return rect.top < window.innerHeight + 200 && rect.bottom > -200;
    }

    // 根据容器大小生成合适的图片URL
    function getOptimizedUrl(url, width) {
        // 如果是外部CDN，可以添加参数
        // 例如：七牛云 url + '?imageView2/2/w/' + width
        // 阿里云OSS: url + '?x-oss-process=image/resize,w_' + width

        // 本地图片直接返回原URL，浏览器会根据CSS缩放
        return url;
    }

    // 加载图片
    function loadImage(img) {
        if (img.dataset.loaded) return;

        const src = img.dataset.src;
        if (!src) return;

        const width = img.offsetWidth * (window.devicePixelRatio || 1);
        const optimizedSrc = getOptimizedUrl(src, Math.ceil(width));

        img.classList.add('loading');

        const tempImg = new Image();
        tempImg.onload = function() {
            img.src = optimizedSrc;
            img.classList.remove('loading');
            img.classList.add('loaded');
            img.dataset.loaded = 'true';
        };
        tempImg.onerror = function() {
            img.classList.remove('loading');
            img.src = src; // 失败后加载原图
        };
        tempImg.src = optimizedSrc;
    }

    // 检查并加载可见图片
    function checkImages() {
        const images = document.querySelectorAll('img[data-src]:not([data-loaded])');
        images.forEach(function(img) {
            if (isInViewport(img)) {
                loadImage(img);
            }
        });
    }

    // 防抖函数
    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(func, wait);
        };
    }

    // 绑定事件
    const debouncedCheck = debounce(checkImages, 100);
    window.addEventListener('scroll', debouncedCheck);
    window.addEventListener('resize', debouncedCheck);

    // 初始检查
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkImages);
    } else {
        checkImages();
    }

    // 导出全局方法供动态加载内容使用
    window.refreshImageLoader = checkImages;
})();
