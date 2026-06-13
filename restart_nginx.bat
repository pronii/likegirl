@echo off
chcp 65001 >nul
echo ========================================
echo    图片加载优化 - Nginx 重启脚本
echo ========================================
echo.

REM 查找 Nginx 安装路径
set NGINX_PATH=D:\phpStudy\PHPTutorial\nginx\nginx.exe

if exist "%NGINX_PATH%" (
    echo [√] 找到 Nginx: %NGINX_PATH%
    echo.

    echo [1/3] 测试 Nginx 配置...
    "%NGINX_PATH%" -t

    if %errorlevel% equ 0 (
        echo.
        echo [√] 配置文件语法正确
        echo.
        echo [2/3] 重新加载 Nginx 配置...
        "%NGINX_PATH%" -s reload

        if %errorlevel% equ 0 (
            echo [√] Nginx 配置重载成功！
            echo.
            echo [3/3] 验证优化是否生效...
            echo.
            echo 请在浏览器中按 F12 打开开发者工具
            echo 切换到 Network 面板，查看图片请求的 Response Headers：
            echo   - Cache-Control: public, immutable
            echo   - Expires: (未来一年的日期)
            echo.
            echo ========================================
            echo    优化已生效！建议清除浏览器缓存测试
            echo ========================================
        ) else (
            echo [×] Nginx 重载失败，请检查配置文件
        )
    ) else (
        echo [×] 配置文件语法错误，请检查 nginx.conf
        echo 备份文件: nginx.conf.backup
    )
) else (
    echo [×] 未找到 Nginx，请确认 phpStudy 安装路径
    echo 默认路径: D:\phpStudy\PHPTutorial\nginx\nginx.exe
)

echo.
pause
