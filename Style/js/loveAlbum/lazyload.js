/**
 * 图片懒加载 + 并发控制优化
 * 解决并发加载几十张图片的性能问题
 */
const ImageLazyLoader = {
    observer: null,
    maxConcurrent: 6, // 最大并发数
    activeCount: 0,
    queue: [],

    /**
     * 初始化懒加载
     */
    init() {
        // 方案1：使用 Intersection Observer（推荐）
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            this.loadImage(img);
                            this.observer.unobserve(img);
                        }
                    });
                },
                {
                    rootMargin: '100px', // 提前100px开始加载
                    threshold: 0.01
                }
            );
        }

        // 自动处理页面中已存在的图片
        this.observeImages();
    },

    /**
     * 观察页面中的图片
     */
    observeImages() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            if (this.observer) {
                this.observer.observe(img);
            } else {
                // 降级方案：直接加载
                this.loadImage(img);
            }
        });
    },

    /**
     * 加载单张图片（带并发控制）
     */
    loadImage(img) {
        const src = img.dataset.src || img.getAttribute('data-src');
        if (!src || img.classList.contains('loaded')) return;

        // 添加到队列
        this.queue.push({ img, src });
        this.processQueue();
    },

    /**
     * 处理加载队列（并发控制）
     */
    processQueue() {
        while (this.activeCount < this.maxConcurrent && this.queue.length > 0) {
            const { img, src } = this.queue.shift();
            this.activeCount++;

            // 显示加载状态
            img.style.opacity = '0.3';
            img.style.filter = 'blur(5px)';

            const tempImg = new Image();

            tempImg.onload = () => {
                img.src = src;
                img.classList.add('loaded');
                img.style.opacity = '1';
                img.style.filter = 'none';
                img.style.transition = 'all 0.3s ease';
                this.activeCount--;
                this.processQueue();
            };

            tempImg.onerror = () => {
                img.src = img.dataset.fallback || 'Style/img/Loading2.gif';
                img.classList.add('error');
                const logger = typeof LikeGirlLog !== 'undefined' ? LikeGirlLog : { warn: function() {} };
                logger.warn('图片加载失败:', src);
                this.activeCount--;
                this.processQueue();
            };

            tempImg.src = src;
        }
    },

    /**
     * 动态添加图片到懒加载
     */
    addImage(imgElement) {
        if (this.observer && imgElement.dataset.src) {
            this.observer.observe(imgElement);
        } else {
            this.loadImage(imgElement);
        }
    },

    /**
     * 预加载关键图片（首屏）
     */
    preloadCritical(urls) {
        urls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = url;
            document.head.appendChild(link);
        });
    }
};

// 自动初始化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ImageLazyLoader.init());
} else {
    ImageLazyLoader.init();
}

// 导出到全局（兼容旧代码）
window.ImageLazyLoader = ImageLazyLoader;
