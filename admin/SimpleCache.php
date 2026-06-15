<?php
/**
 * 简单的查询缓存类
 * 减少重复数据库查询
 */
class SimpleCache {
    private static $cache = [];
    private static $enabled = true;

    /**
     * 获取缓存
     */
    public static function get($key) {
        if (!self::$enabled) {
            return null;
        }
        return isset(self::$cache[$key]) ? self::$cache[$key] : null;
    }

    /**
     * 设置缓存
     */
    public static function set($key, $value, $ttl = 300) {
        if (!self::$enabled) {
            return false;
        }
        self::$cache[$key] = [
            'data' => $value,
            'expire' => time() + $ttl
        ];
        return true;
    }

    /**
     * 检查缓存是否存在且未过期
     */
    public static function has($key) {
        if (!self::$enabled || !isset(self::$cache[$key])) {
            return false;
        }

        if (self::$cache[$key]['expire'] < time()) {
            unset(self::$cache[$key]);
            return false;
        }

        return true;
    }

    /**
     * 清除指定缓存
     */
    public static function delete($key) {
        unset(self::$cache[$key]);
    }

    /**
     * 清除所有缓存
     */
    public static function clear() {
        self::$cache = [];
    }

    /**
     * 启用/禁用缓存
     */
    public static function setEnabled($enabled) {
        self::$enabled = $enabled;
    }

    /**
     * 获取缓存数据（支持自动更新）
     */
    public static function remember($key, $ttl, $callback) {
        if (self::has($key)) {
            return self::$cache[$key]['data'];
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}
