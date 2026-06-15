<?php
session_start();
include_once 'Nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">数据备份与恢复</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">备份管理</h4>

                    <button type="button" class="btn btn-primary mb-3" onclick="createBackup()">
                        <i class="mdi mdi-database-export"></i> 创建新备份
                    </button>
                    <button type="button" class="btn btn-success mb-3" onclick="showUploadModal()">
                        <i class="mdi mdi-upload"></i> 上传备份
                    </button>
                    <button type="button" class="btn btn-danger mb-3" id="batchDeleteBtn" onclick="batchDeleteBackups()" disabled>
                        <i class="mdi mdi-delete"></i> 批量删除
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                    <th>备份文件</th>
                                    <th>文件大小</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="backupList">
                                <tr>
                                    <td colspan="5" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../Style/toastr/toastr.min.js"></script>
<script>
// HTML转义函数
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function(char) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
    });
}

function loadBackups() {
    $.get('backupList.php', function(res) {
        if (res.success) {
            let html = '';
            if (res.files.length === 0) {
                html = '<tr><td colspan="5" class="text-center">暂无备份</td></tr>';
            } else {
                res.files.forEach(file => {
                    const safeName = escapeHtml(file.name);
                    const sizeKB = file.size / 1024;
                    const sizeDisplay = sizeKB >= 1024
                        ? (sizeKB / 1024).toFixed(2) + ' MB'
                        : sizeKB.toFixed(2) + ' KB';
                    html += `<tr>
                        <td><input type="checkbox" class="backup-checkbox" value="${safeName}" onchange="updateBatchButton()"></td>
                        <td>${safeName}</td>
                        <td>${sizeDisplay}</td>
                        <td>${file.date}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="restoreBackup('${safeName}')" class="action-icon">
                                <i class="mdi mdi-database-import"></i> 恢复
                            </a>
                            <a href="downloadBackup.php?filename=${encodeURIComponent(file.name)}" class="action-icon ml-2">
                                <i class="mdi mdi-download"></i> 下载
                            </a>
                            <a href="javascript:void(0)" onclick="deleteBackup('${safeName}')" class="action-icon ml-2 text-danger">
                                <i class="mdi mdi-delete"></i> 删除
                            </a>
                        </td>
                    </tr>`;
                });
            }
            $('#backupList').html(html);
            updateBatchButton();
        } else {
            toastr.error(res.message || '加载失败');
        }
    }, 'json').fail(function() {
        toastr.error('网络错误，请稍后重试');
        $('#backupList').html('<tr><td colspan="5" class="text-center text-danger">加载失败</td></tr>');
    });
}

function createBackup() {
    if (!confirm('确定要创建数据备份吗？')) return;

    toastr.info('正在备份，请稍候...');
    $.post('backup.php', function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadBackups();
        } else {
            toastr.error(res.message);
        }
    }, 'json');
}

function restoreBackup(filename) {
    if (!confirm('确定要恢复此备份吗？\n⚠️ 当前数据将被覆盖！')) return;

    toastr.info('正在恢复，请稍候...');
    $.post('restore.php', {filename: filename}, function(res) {
        if (res.success) {
            toastr.success(res.message);
        } else {
            toastr.error(res.message);
        }
    }, 'json');
}

// 全选/取消全选
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.backup-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBatchButton();
}

// 更新批量删除按钮状态
function updateBatchButton() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    const btn = document.getElementById('batchDeleteBtn');
    const selectAll = document.getElementById('selectAll');

    if (checkboxes.length > 0) {
        btn.disabled = false;
        btn.innerHTML = `<i class="mdi mdi-delete"></i> 批量删除 (已选${checkboxes.length}项)`;
    } else {
        btn.disabled = true;
        btn.innerHTML = '<i class="mdi mdi-delete"></i> 批量删除';
    }

    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.backup-checkbox');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checkboxes.length === allCheckboxes.length;
    }
}

// 单个删除
function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件 "' + filename + '" 吗？')) return;

    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: [filename]}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('删除失败');
        }
    });
}

// 批量删除
function batchDeleteBackups() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    if (checkboxes.length === 0) {
        toastr.warning('请先选择要删除的文件');
        return;
    }

    const filenames = Array.from(checkboxes).map(cb => cb.value);
    const count = filenames.length;

    if (!confirm('确定要删除 ' + count + ' 个备份文件吗？\n此操作不可恢复！')) return;

    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: filenames}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                document.getElementById('selectAll').checked = false;
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('批量删除失败');
        }
    });
}

// 显示上传模态框
function showUploadModal() {
    $('#uploadModal').modal('show');
    document.getElementById('uploadForm').reset();
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
}

// 文件选择变化事件
$(function() {
    loadBackups();

    $('#backupFile').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#fileName').text(file.name);
            $('#fileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
            $('#fileInfo').show();

            if (file.size > 50 * 1024 * 1024) {
                toastr.error('文件大小不能超过50MB');
                $('#uploadBtn').prop('disabled', true);
            } else {
                $('#uploadBtn').prop('disabled', false);
            }
        } else {
            $('#fileInfo').hide();
            $('#uploadBtn').prop('disabled', true);
        }
    });
});

// 上传备份文件
function uploadBackup() {
    const fileInput = document.getElementById('backupFile');
    const file = fileInput.files[0];

    if (!file) {
        toastr.error('请选择文件');
        return;
    }

    const uploadBtn = $('#uploadBtn');
    uploadBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> 上传中...');

    const formData = new FormData();
    formData.append('file', file);

    toastr.info('正在上传，请稍候...');

    $.ajax({
        url: 'uploadBackup.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                $('#uploadModal').modal('hide');
                loadBackups();
            } else {
                toastr.error(res.message);
            }
            uploadBtn.prop('disabled', false).html('上传');
        },
        error: function(xhr) {
            toastr.error('上传失败');
            uploadBtn.prop('disabled', false).html('上传');
        }
    });
}
</script>

<!-- 上传备份文件模态框 -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">上传备份文件</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>选择文件</label>
                        <input type="file" class="form-control" id="backupFile" name="file" accept=".sql" required>
                        <small class="form-text text-muted">仅支持 .sql 格式，最大 50MB</small>
                    </div>
                    <div id="fileInfo" class="alert alert-info" style="display:none;">
                        <strong>已选文件：</strong><span id="fileName"></span><br>
                        <strong>文件大小：</strong><span id="fileSize"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="uploadBtn" onclick="uploadBackup()" disabled>上传</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'Footer.php'; ?>
