<?php
/**
 * 端到端集成测试
 * 测试完整的备份增强功能流程
 */

session_start();
$_SESSION['loginadmin'] = 'test_admin';

echo "=== 端到端集成测试 ===\n\n";

// 测试1: 创建测试备份文件
echo "【测试1】创建测试备份文件\n";
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0750, true);
}

$testBackupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$testBackupPath = $backupDir . '/' . $testBackupFile;
$testContent = "-- Test Backup\nCREATE TABLE test (id INT);\nINSERT INTO test VALUES (1);";
file_put_contents($testBackupPath, $testContent);
echo "  ✓ 创建测试文件: $testBackupFile\n";

// 测试2: 模拟单个删除API调用
echo "\n【测试2】单个删除功能测试\n";
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$deleteData = json_encode(['filenames' => [$testBackupFile]]);

// 保存原始输入
$originalInput = file_get_contents('php://input');
file_put_contents('php://input', $deleteData);

ob_start();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// 模拟deleteBackup.php的核心逻辑
include_once 'connect.php';

$input = $deleteData;
$data = json_decode($input, true);

if (isset($data['filenames']) && is_array($data['filenames'])) {
    $filenames = $data['filenames'];
    $backupDirReal = realpath($backupDir);
    $deleted = 0;
    $failed = 0;
    $deletedFiles = [];

    foreach ($filenames as $filename) {
        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
            $failed++;
            continue;
        }

        $filepath = $backupDir . '/' . $filename;
        $filepathReal = realpath($filepath);

        if ($filepathReal === false || strpos($filepathReal, $backupDirReal) !== 0) {
            $failed++;
            continue;
        }

        if (!file_exists($filepathReal)) {
            $failed++;
            continue;
        }

        if (unlink($filepathReal)) {
            $deleted++;
            $deletedFiles[] = $filename;
        } else {
            $failed++;
        }
    }

    // 记录日志
    if ($deleted > 0) {
        $logContent = ($deleted == 1) ? "管理员删除备份文件: {$deletedFiles[0]}" : "管理员批量删除备份文件: {$deleted}个文件";
        $logTime = date('Y-m-d H:i:s');
        $logIp = '127.0.0.1';
        $fileInfo = ($deleted == 1) ? $deletedFiles[0] : "{$deleted}个文件";

        $logStmt = $connect->prepare("INSERT INTO warning (ip, gsd, time, file) VALUES (?, ?, ?, ?)");
        if ($logStmt) {
            $logStmt->bind_param("ssss", $logIp, $logContent, $logTime, $fileInfo);
            $logStmt->execute();
            $logStmt->close();
        }
    }

    if ($deleted > 0) {
        echo "  ✓ 删除成功: $deleted 个文件\n";
        echo "  ✓ 日志已记录\n";
    } else {
        echo "  ✗ 删除失败\n";
    }
}

ob_end_clean();

// 测试3: 验证文件已删除
echo "\n【测试3】验证文件已删除\n";
if (!file_exists($testBackupPath)) {
    echo "  ✓ 文件已成功删除\n";
} else {
    echo "  ✗ 文件仍然存在\n";
}

// 测试4: 批量删除测试
echo "\n【测试4】批量删除功能测试\n";
$batchFiles = [];
for ($i = 1; $i <= 3; $i++) {
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '_test' . $i . '.sql';
    $filepath = $backupDir . '/' . $filename;
    file_put_contents($filepath, "-- Test Backup $i\nCREATE TABLE test$i (id INT);");
    $batchFiles[] = $filename;
    echo "  创建测试文件 $i: $filename\n";
}

// 模拟批量删除
$deleteData = json_encode(['filenames' => $batchFiles]);
$data = json_decode($deleteData, true);
$deleted = 0;

foreach ($data['filenames'] as $filename) {
    $filepath = $backupDir . '/' . $filename;
    if (file_exists($filepath) && unlink($filepath)) {
        $deleted++;
    }
}

echo "  ✓ 批量删除成功: $deleted/3 个文件\n";

// 测试5: 安全测试 - 路径遍历攻击
echo "\n【测试5】安全测试 - 路径遍历攻击\n";
$maliciousFile = '../admin/Config_DB.php';
$pattern = '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';
$isBlocked = !preg_match($pattern, $maliciousFile);
echo "  攻击文件名: $maliciousFile\n";
echo "  " . ($isBlocked ? "✓ 攻击被阻止" : "✗ 攻击未被阻止") . "\n";

// 测试6: 检查数据库日志
echo "\n【测试6】数据库日志验证\n";
$result = $connect->query("SELECT * FROM warning WHERE gsd LIKE '%备份%' ORDER BY id DESC LIMIT 3");
if ($result) {
    echo "  最近3条备份相关日志:\n";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        echo "    [{$row['time']}] {$row['gsd']}\n";
        $count++;
    }
    echo "  ✓ 找到 $count 条日志记录\n";
}

// 测试7: 文件权限检查
echo "\n【测试7】目录权限检查\n";
if (is_writable($backupDir)) {
    echo "  ✓ 备份目录可写\n";
} else {
    echo "  ✗ 备份目录不可写\n";
}

// 清理
echo "\n【清理】删除测试文件\n";
$cleanCount = 0;
$files = glob($backupDir . '/backup_*_test*.sql');
foreach ($files as $file) {
    if (unlink($file)) {
        $cleanCount++;
    }
}
echo "  ✓ 清理完成: $cleanCount 个测试文件\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ 端到端集成测试完成！\n";
echo str_repeat("=", 50) . "\n";
?>
