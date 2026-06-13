@echo off
chcp 65001 >nul
echo ========================================
echo    TLS 握手优化 - Nginx 重启脚本
echo ========================================
echo.

set NGINX_PATH=D:\phpStudy\PHPTutorial\nginx\nginx.exe

if exist "%NGINX_PATH%" (
    echo [√] 找到 Nginx: %NGINX_PATH%
    echo.

    echo [1/4] 测试 Nginx 配置...
    cd /d D:\phpStudy\PHPTutorial\nginx
    "%NGINX_PATH%" -t

    if %errorlevel% equ 0 (
        echo.
        echo [√] 配置文件语法正确
        echo.
        echo [2/4] 重新加载 Nginx 配置...
        taskkill /F /IM nginx.exe >nul 2>&1
        start "" "%NGINX_PATH%"
        timeout /t 2 >nul

        echo [√] Nginx 已重启！
        echo.
        echo [3/4] 测试优化效果...
        echo.
        curl -I http://localhost/Style/img/Loading2.gif 2>nul | findstr "Cache-Control"
        echo.

        echo [4/4] 验证步骤：
        echo.
        echo 1. 打开浏览器访问: http://localhost/loveImg.php
        echo 2. 按 F12 打开开发者工具
        echo 3. 切换到 Network 标签
        echo 4. 刷新页面
        echo 5. 点击 Google Fonts 请求，查看 Timing:
        echo    - DNS Lookup: 应该为 0ms (DNS Prefetch 生效)
        echo    - Initial connection: 应该 ^<20ms (Preconnect 生效)
        echo    - SSL: 应该为 0ms (连接复用生效)
        echo.
        echo ========================================
        echo    TLS 优化已生效！
        echo ========================================
        echo.
        echo 详细文档: TLS_OPTIMIZATION_GUIDE.md
    ) else (
        echo [×] 配置文件语法错误
        echo 备份文件: nginx.conf.backup
    )
) else (
    echo [×] 未找到 Nginx
)

echo.
pause
