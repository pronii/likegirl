// 全局配置文件
const LikeGirlConfig = {
    // 开发模式：true=显示所有日志，false=仅显示错误
    debug: false,

    // 版本号
    version: '5.2.1-Stable'
};

// 封装的日志函数
const LikeGirlLog = {
    log: function(...args) {
        if (LikeGirlConfig.debug) {
            console.log(...args);
        }
    },

    error: function(...args) {
        // 错误始终显示
        console.error(...args);
    },

    warn: function(...args) {
        if (LikeGirlConfig.debug) {
            console.warn(...args);
        }
    },

    info: function(...args) {
        if (LikeGirlConfig.debug) {
            console.info(...args);
        }
    }
};
