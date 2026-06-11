<?php if ($login['user'] == $adminuser): ?>
    <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Warning - </strong> 当前账号为默认账号 请尽快修改！
    </div>
<?php endif; ?>

<?php if ($login['pw'] == md5($adminpw)): ?>
    <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Warning - </strong> 当前密码为默认密码 请尽快修改！
    </div>
<?php endif; ?>


<div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary">
                    <a id="documentLink" target="_blank" style="color: #fff;" href="#">查看详情</a>
                </button>
            </div>
        </div>
    </div>
</div>


<style>
    .modal-content{
        border-radius: 1rem;
        font-family: 'Noto Serif SC', serif;
    }

    .modal-body img.versionImage {
        width: 100%;
        border-radius: 1rem;
    }

    .modal-body ul li {
        line-height: 2rem;
    }
    
    .modal-body{
        max-height: 70vh;
        overflow: auto;
    }
    
    .modal-body .warning{
        color: #ff6c2f;
        font-weight: bold;
    }
    
    .latestVersion{
        background: #0fbcff;
        color: #fff;
        padding: .2rem .6rem;
        border-radius: 1rem;
    }
</style>