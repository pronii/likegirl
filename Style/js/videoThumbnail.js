/**
 * 视频缩略图提取模块
 * 使用 HTML5 Canvas API 从视频文件中提取第一帧作为缩略图
 */
(function() {
    'use strict';

    /**
     * 视频缩略图提取器
     */
    var VideoThumbnail = {
        /**
         * 提取视频缩略图和元数据
         * @param {File} videoFile - 视频文件对象
         * @param {Function} callback - 回调函数 callback(error, result)
         *   result: { thumbnail: base64String, duration: seconds, width: number, height: number, fileSize: bytes }
         */
        extractVideoThumbnail: function(videoFile, callback) {
            // 验证文件类型
            if (!videoFile || !videoFile.type.startsWith('video/')) {
                callback(new Error('请选择有效的视频文件'), null);
                return;
            }

            // 验证文件大小（最大 100MB）
            var maxSize = 100 * 1024 * 1024; // 100MB
            if (videoFile.size > maxSize) {
                callback(new Error('视频文件大小不能超过 100MB'), null);
                return;
            }

            var video = document.createElement('video');
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var objectUrl = URL.createObjectURL(videoFile);

            video.muted = true; // 静音避免浏览器自动播放限制
            video.preload = 'metadata';

            // 监听元数据加载完成事件
            video.addEventListener('loadeddata', function() {
                try {
                    // 设置 canvas 尺寸与视频相同
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    // 将视频第一帧绘制到 canvas
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // 转换为 base64 格式的 JPEG 图片（质量 0.8）
                    var thumbnail = canvas.toDataURL('image/jpeg', 0.8);

                    // 构建结果对象
                    var result = {
                        thumbnail: thumbnail,
                        duration: video.duration,
                        width: video.videoWidth,
                        height: video.videoHeight,
                        fileSize: videoFile.size,
                        fileName: videoFile.name
                    };

                    // 清理对象 URL
                    URL.revokeObjectURL(objectUrl);

                    callback(null, result);
                } catch (error) {
                    URL.revokeObjectURL(objectUrl);
                    callback(new Error('提取缩略图失败: ' + error.message), null);
                }
            });

            // 监听加载错误
            video.addEventListener('error', function() {
                URL.revokeObjectURL(objectUrl);
                callback(new Error('视频加载失败，请确保文件格式正确'), null);
            });

            // 加载视频
            video.src = objectUrl;
        },

        /**
         * 格式化时长（秒 -> HH:MM:SS 或 MM:SS）
         * @param {Number} seconds - 秒数
         * @returns {String} 格式化后的时长
         */
        formatDuration: function(seconds) {
            if (isNaN(seconds) || seconds < 0) {
                return '00:00';
            }

            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var secs = Math.floor(seconds % 60);

            var pad = function(num) {
                return num < 10 ? '0' + num : num;
            };

            if (hours > 0) {
                return pad(hours) + ':' + pad(minutes) + ':' + pad(secs);
            } else {
                return pad(minutes) + ':' + pad(secs);
            }
        },

        /**
         * 格式化文件大小（字节 -> KB/MB/GB）
         * @param {Number} bytes - 字节数
         * @returns {String} 格式化后的文件大小
         */
        formatFileSize: function(bytes) {
            if (isNaN(bytes) || bytes < 0) {
                return '0 B';
            }

            var units = ['B', 'KB', 'MB', 'GB'];
            var unitIndex = 0;
            var size = bytes;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return size.toFixed(2) + ' ' + units[unitIndex];
        }
    };

    // 导出到 window 对象
    window.VideoThumbnail = VideoThumbnail;

})();
