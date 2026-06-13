@echo off
chcp 65001 >nul
echo ========================================
echo    创建宝塔增量更新包
echo ========================================
echo.

set SOURCE_DIR=D:\phpStudy\PHPTutorial\WWW
set PACKAGE_DIR=update_package_bt

echo [1/3] 创建更新包目录...
if exist "%PACKAGE_DIR%" (
    rmdir /S /Q "%PACKAGE_DIR%"
)
mkdir "%PACKAGE_DIR%"
mkdir "%PACKAGE_DIR%\Style\js\loveAlbum"

echo [2/3] 复制核心优化文件...

REM 前端核心文件
copy "%SOURCE_DIR%\head.php" "%PACKAGE_DIR%\" >nul
copy "%SOURCE_DIR%\footer.php" "%PACKAGE_DIR%\" >nul
copy "%SOURCE_DIR%\loveImg.php" "%PACKAGE_DIR%\" >nul
copy "%SOURCE_DIR%\Style\js\loveAlbum\album.js" "%PACKAGE_DIR%\Style\js\loveAlbum\" >nul
copy "%SOURCE_DIR%\Style\js\loveAlbum\lazyload.js" "%PACKAGE_DIR%\Style\js\loveAlbum\" >nul

REM 后端接口文件
copy "%SOURCE_DIR%\getPhotos.php" "%PACKAGE_DIR%\" >nul
copy "%SOURCE_DIR%\getAlbums.php" "%PACKAGE_DIR%\" >nul

REM 复制更新指南
copy "宝塔增量更新指南.md" "%PACKAGE_DIR%\" >nul

echo.
echo [3/3] 文件清单：
echo.
echo 前端优化文件：
echo   - head.php                        (DNS Prefetch + Preconnect)
echo   - loveImg.php                     (引入懒加载脚本)
echo   - Style\js\loveAlbum\album.js     (相册核心逻辑)
echo   - Style\js\loveAlbum\lazyload.js  (新增：图片懒加载)
echo.
echo 后端优化文件：
echo   - getPhotos.php                   (API缓存优化)
echo   - getAlbums.php                   (API缓存优化)
echo.
echo 文档：
echo   - 宝塔增量更新指南.md
echo.

echo ========================================
echo    更新包已创建完成！
echo ========================================
echo.
echo 更新包位置: %cd%\%PACKAGE_DIR%
echo.
echo 后续操作：
echo   1. 将 %PACKAGE_DIR% 文件夹压缩为 zip
echo   2. 上传到宝塔面板你的站点目录
echo   3. 在宝塔面板解压并覆盖文件
echo   4. 按照 宝塔增量更新指南.md 配置 Nginx
echo   5. 清除浏览器缓存测试
echo.
echo 详细步骤请查看: 宝塔增量更新指南.md
echo ========================================
pause
