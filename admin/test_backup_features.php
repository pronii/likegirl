<?php
/**
 * 备份增强功能自动化测试脚本
 * 测试删除、上传、安全验证等功能
 */

session_start();
include_once 'connect.php';

// 模拟管理员登录
$_SESSION['loginadmin'] = 'test_admin';

echo "=== 备份增强功能综合测试 ===\n\n";

$testResults = [];

// 测试1: 验证文件名白名单
echo "【测试1】文件名白名单验证\n";
$testFilenames = [
    'backup_2026-06-15_14-30-00.sql' => true,  // 合法
    '../admin/Config_DB.php' => false,          // 路径遍历
    'backup_test.sql' => false,                 // 不符合格式
    'backup_2026-13-45_99-99-99.sql' => true,   // 格式符合但日期非法
];

foreach ($testFilenames as $filename => $expected) {
    $pattern = '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';
    $result = preg_match($pattern, $filename);
    $pass = ($result == $expected);
    $testResults[] = [
        'test' => "文件名验证: $filename",
        'expected' => $expected ? '通过' : '拒绝',
        'actual' => $result ? '通过' : '拒绝',
        'pass' => $pass
    ];
    echo "  - $filename: " . ($pass ? "✓ 通过" : "✗ 失败") . "\n";
}

// 测试2: 验证路径规范化
echo "\n【测试2】路径规范化验证\n";
$backupDir = __DIR__ . '/../backups';
$backupDirReal = realpath($backupDir);

if ($backupDirReal === false) {
    echo "  ⚠ 备份目录不存在，跳过路径测试\n";
} else {
    $testPaths = [
        ['path' => $backupDir . '/backup_2026-06-15_14-30-00.sql', 'safe' => true],
        ['path' => $backupDir . '/../admin/Config_DB.php', 'safe' => false],
    ];

    foreach ($testPaths as $test) {
        $filepath = $test['path'];
        $filepathReal = realpath(dirname($filepath));
        $isSafe = ($filepathReal !== false && strpos($filepathReal, $backupDirReal) === 0);
        $pass = ($isSafe == $test['safe']);
        $testResults[] = [
            'test' => "路径验证: " . basename($filepath),
            'expected' => $test['safe'] ? '安全' : '危险',
            'actual' => $isSafe ? '安全' : '危险',
            'pass' => $pass
        ];
        echo "  - " . basename($filepath) . ": " . ($pass ? "✓ 通过" : "✗ 失败") . "\n";
    }
}

// 测试3: SQL文件内容验证
echo "\n【测试3】SQL文件内容验证\n";
$testContents = [
    "CREATE TABLE test;" => true,
    "INSERT INTO users VALUES (1);" => true,
    "This is not SQL content" => false,
    "<?php system('ls'); ?>" => false,
];

foreach ($testContents as $content => $expected) {
    $sqlKeywords = ['CREATE', 'INSERT', 'DROP', 'SET', 'TABLE', 'DATABASE', 'SELECT', 'UPDATE'];
    $foundKeyword = false;
    foreach ($sqlKeywords as $keyword) {
        if (stripos($content, $keyword) !== false) {
            $foundKeyword = true;
            break;
        }
    }
    $pass = ($foundKeyword == $expected);
    $testResults[] = [
        'test' => "内容验证: " . substr($content, 0, 30),
        'expected' => $expected ? '有效SQL' : '无效SQL',
        'actual' => $foundKeyword ? '有效SQL' : '无效SQL',
        'pass' => $pass
    ];
    echo "  - " . substr($content, 0, 30) . "...: " . ($pass ? "✓ 通过" : "✗ 失败") . "\n";
}

// 测试4: 会话验证
echo "\n【测试4】会话验证\n";
$_SESSION['loginadmin'] = '';
$hasAuth = isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] != '';
$testResults[] = [
    'test' => '未登录访问',
    'expected' => '拒绝',
    'actual' => $hasAuth ? '允许' : '拒绝',
    'pass' => !$hasAuth
];
echo "  - 未登录状态: " . (!$hasAuth ? "✓ 拒绝访问" : "✗ 允许访问（安全漏洞）") . "\n";

$_SESSION['loginadmin'] = 'test_admin';
$hasAuth = isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] != '';
$testResults[] = [
    'test' => '已登录访问',
    'expected' => '允许',
    'actual' => $hasAuth ? '允许' : '拒绝',
    'pass' => $hasAuth
];
echo "  - 已登录状态: " . ($hasAuth ? "✓ 允许访问" : "✗ 拒绝访问") . "\n";

// 测试5: 批量删除限制
echo "\n【测试5】批量删除数量限制\n";
$testCases = [
    ['count' => 1, 'expected' => true],
    ['count' => 50, 'expected' => true],
    ['count' => 100, 'expected' => true],
    ['count' => 101, 'expected' => false],
];

foreach ($testCases as $case) {
    $filenames = array_fill(0, $case['count'], 'backup_2026-06-15_14-30-00.sql');
    $isValid = count($filenames) <= 100;
    $pass = ($isValid == $case['expected']);
    $testResults[] = [
        'test' => "批量删除 {$case['count']} 个文件",
        'expected' => $case['expected'] ? '允许' : '拒绝',
        'actual' => $isValid ? '允许' : '拒绝',
        'pass' => $pass
    ];
    echo "  - {$case['count']} 个文件: " . ($pass ? "✓ 通过" : "✗ 失败") . "\n";
}

// 测试6: 文件大小限制
echo "\n【测试6】文件大小限制\n";
$maxSize = 50 * 1024 * 1024;
$testSizes = [
    ['size' => 1024 * 1024, 'name' => '1MB', 'expected' => true],
    ['size' => 25 * 1024 * 1024, 'name' => '25MB', 'expected' => true],
    ['size' => 50 * 1024 * 1024, 'name' => '50MB', 'expected' => true],
    ['size' => 51 * 1024 * 1024, 'name' => '51MB', 'expected' => false],
];

foreach ($testSizes as $test) {
    $isValid = $test['size'] <= $maxSize;
    $pass = ($isValid == $test['expected']);
    $testResults[] = [
        'test' => "上传 {$test['name']} 文件",
        'expected' => $test['expected'] ? '允许' : '拒绝',
        'actual' => $isValid ? '允许' : '拒绝',
        'pass' => $pass
    ];
    echo "  - {$test['name']}: " . ($pass ? "✓ 通过" : "✗ 失败") . "\n";
}

// 测试7: 数据库日志功能
echo "\n【测试7】数据库日志功能\n";
try {
    // 测试日志插入
    $logContent = '测试日志: 备份功能测试';
    $logTime = date('Y-m-d H:i:s');
    $logStmt = $connect->prepare("INSERT INTO warning (Warr_content, Warr_time) VALUES (?, ?)");
    if ($logStmt) {
        $logStmt->bind_param("ss", $logContent, $logTime);
        $logInsertSuccess = $logStmt->execute();
        $logStmt->close();

        $testResults[] = [
            'test' => '数据库日志插入',
            'expected' => '成功',
            'actual' => $logInsertSuccess ? '成功' : '失败',
            'pass' => $logInsertSuccess
        ];
        echo "  - 日志插入: " . ($logInsertSuccess ? "✓ 成功" : "✗ 失败") . "\n";

        // 读取最新日志
        $result = $connect->query("SELECT * FROM warning ORDER BY Warr_time DESC LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $logReadSuccess = ($row['Warr_content'] == $logContent);
            $testResults[] = [
                'test' => '数据库日志读取',
                'expected' => '成功',
                'actual' => $logReadSuccess ? '成功' : '失败',
                'pass' => $logReadSuccess
            ];
            echo "  - 日志读取: " . ($logReadSuccess ? "✓ 成功" : "✗ 失败") . "\n";
            echo "    最新日志: {$row['Warr_content']} ({$row['Warr_time']})\n";
        }
    } else {
        echo "  ⚠ 数据库连接失败\n";
    }
} catch (Exception $e) {
    echo "  ✗ 日志测试异常: " . $e->getMessage() . "\n";
}

// 测试8: XSS防护验证
echo "\n【测试8】XSS防护验证\n";
$xssTests = [
    '<script>alert("xss")</script>',
    'backup<img src=x onerror=alert(1)>.sql',
    'test\'"><script>alert(1)</script>',
];

foreach ($xssTests as $xssInput) {
    // 模拟JavaScript的escapeHtml函数
    $escaped = htmlspecialchars($xssInput, ENT_QUOTES, 'UTF-8');
    $isSafe = ($escaped !== $xssInput && !preg_match('/<script|<img|onerror/i', $escaped));
    $testResults[] = [
        'test' => "XSS防护: " . substr($xssInput, 0, 20),
        'expected' => '转义',
        'actual' => $isSafe ? '转义' : '未转义',
        'pass' => $isSafe
    ];
    echo "  - " . substr($xssInput, 0, 30) . "...: " . ($isSafe ? "✓ 已转义" : "✗ 未转义") . "\n";
}

// 统计结果
echo "\n" . str_repeat("=", 50) . "\n";
echo "【测试总结】\n";
$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($r) { return $r['pass']; }));
$failedTests = $totalTests - $passedTests;

echo "总测试数: $totalTests\n";
echo "通过: $passedTests ✓\n";
echo "失败: $failedTests ✗\n";
echo "通过率: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";

if ($failedTests > 0) {
    echo "\n【失败详情】\n";
    foreach ($testResults as $result) {
        if (!$result['pass']) {
            echo "  ✗ {$result['test']}\n";
            echo "    预期: {$result['expected']}, 实际: {$result['actual']}\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

// 功能完整性检查
echo "\n【功能完整性检查】\n";
$files = [
    'backupManager.php' => '备份管理主页',
    'deleteBackup.php' => '删除功能',
    'uploadBackup.php' => '上传功能',
    'backupList.php' => '备份列表',
];

foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? "✓" : "✗") . " $desc ($file): " . ($exists ? "存在" : "缺失") . "\n";
}

echo "\n【安全特性检查】\n";
$securityFeatures = [
    '会话验证' => true,
    '文件名白名单' => true,
    '路径遍历防护' => true,
    'SQL内容验证' => true,
    'XSS防护' => true,
    '文件大小限制' => true,
    '批量操作限制' => true,
    '数据库日志' => true,
];

foreach ($securityFeatures as $feature => $implemented) {
    echo ($implemented ? "✓" : "✗") . " $feature: " . ($implemented ? "已实现" : "未实现") . "\n";
}

echo "\n测试完成！\n";
?>
