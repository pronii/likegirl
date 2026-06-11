<?php
session_start();
include_once 'Nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">音乐API配置</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-0">API列表</h4>
                        <button type="button" class="btn btn-primary" onclick="showAddModal()">
                            <i class="mdi mdi-plus-circle"></i> 添加API
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>API名称</th>
                                    <th>API地址</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                    <th>排序</th>
                                    <th style="width: 150px;">操作</th>
                                </tr>
                            </thead>
                            <tbody id="apiList">
                                <tr>
                                    <td colspan="7" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加/编辑API模态框 -->
<div class="modal fade" id="apiModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">添加API</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="apiForm">
                    <input type="hidden" id="apiId" name="id">

                    <div class="form-group">
                        <label for="name">API名称 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="例如：网易云API">
                    </div>

                    <div class="form-group">
                        <label for="api_url">API地址 <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="api_url" name="api_url" required placeholder="https://api.example.com">
                        <small class="form-text text-muted">API基础地址</small>
                    </div>

                    <div class="form-group">
                        <label for="api_type">API类型</label>
                        <select class="form-control" id="api_type" name="api_type">
                            <option value="both">搜索+播放</option>
                            <option value="search">仅搜索</option>
                            <option value="url">仅播放</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">排序</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" placeholder="数字越小优先级越高">
                        <small class="form-text text-muted">启用多个API时按优先级依次尝试</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" checked>
                            <label class="custom-control-label" for="is_enabled">启用此API</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveApi()">保存</button>
            </div>
        </div>
    </div>
</div>

<script src="../Style/toastr/toastr.min.js"></script>
<script>
$(document).ready(function() {
    loadApiList();
});

function loadApiList() {
    $.get('musicApiList.php', function(res) {
        if (res.success) {
            renderApiList(res.data);
        } else {
            toastr.error(res.message || '加载失败');
        }
    }, 'json').fail(function() {
        toastr.error('网络错误');
    });
}

function renderApiList(list) {
    if (list.length === 0) {
        $('#apiList').html('<tr><td colspan="7" class="text-center">暂无API配置</td></tr>');
        return;
    }

    let html = '';
    list.forEach(item => {
        const status = item.is_enabled == 1 ?
            '<span class="badge badge-success">启用</span>' :
            '<span class="badge badge-secondary">禁用</span>';

        const typeMap = {
            'both': '搜索+播放',
            'search': '仅搜索',
            'url': '仅播放'
        };
        const type = typeMap[item.api_type] || item.api_type;

        html += `<tr>
            <td>${item.id}</td>
            <td>${item.name}</td>
            <td><small>${item.api_url}</small></td>
            <td>${type}</td>
            <td>${status}</td>
            <td>${item.sort_order}</td>
            <td>
                <a href="javascript:void(0)" class="action-icon" onclick="editApi(${item.id})" title="编辑">
                    <i class="mdi mdi-pencil"></i>
                </a>
                <a href="javascript:void(0)" class="action-icon ml-2" onclick="toggleStatus(${item.id}, ${item.is_enabled})" title="${item.is_enabled == 1 ? '禁用' : '启用'}">
                    <i class="mdi mdi-${item.is_enabled == 1 ? 'eye-off' : 'eye'}"></i>
                </a>
                <a href="javascript:void(0)" class="action-icon ml-2" onclick="deleteApi(${item.id})" title="删除">
                    <i class="mdi mdi-delete text-danger"></i>
                </a>
            </td>
        </tr>`;
    });
    $('#apiList').html(html);
}

function showAddModal() {
    $('#modalTitle').text('添加API');
    $('#apiForm')[0].reset();
    $('#apiId').val('');
    $('#is_enabled').prop('checked', true);
    $('#apiModal').modal('show');
}

function editApi(id) {
    $.get('musicApiGet.php', {id: id}, function(res) {
        if (res.success) {
            $('#modalTitle').text('编辑API');
            $('#apiId').val(res.data.id);
            $('#name').val(res.data.name);
            $('#api_url').val(res.data.api_url);
            $('#api_type').val(res.data.api_type);
            $('#sort_order').val(res.data.sort_order);
            $('#is_enabled').prop('checked', res.data.is_enabled == 1);
            $('#apiModal').modal('show');
        } else {
            toastr.error(res.message || '加载失败');
        }
    }, 'json');
}

function saveApi() {
    const form = $('#apiForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const data = {
        id: $('#apiId').val(),
        name: $('#name').val(),
        api_url: $('#api_url').val(),
        api_type: $('#api_type').val(),
        sort_order: $('#sort_order').val(),
        is_enabled: $('#is_enabled').is(':checked') ? 1 : 0
    };

    $.post('musicApiSave.php', data, function(res) {
        if (res.success) {
            toastr.success(res.message);
            $('#apiModal').modal('hide');
            loadApiList();
        } else {
            toastr.error(res.message || '保存失败');
        }
    }, 'json').fail(function() {
        toastr.error('网络错误');
    });
}

function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    $.post('musicApiToggle.php', {id: id, is_enabled: newStatus}, function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadApiList();
        } else {
            toastr.error(res.message || '操作失败');
        }
    }, 'json');
}

function deleteApi(id) {
    if (!confirm('确定要删除这个API吗？')) return;

    $.post('musicApiDelete.php', {id: id}, function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadApiList();
        } else {
            toastr.error(res.message || '删除失败');
        }
    }, 'json');
}
</script>

<?php
include_once 'Footer.php';
?>
