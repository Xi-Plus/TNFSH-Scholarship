<header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="<?=$C["path"]?>/"><?=$C["sitename"]?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?=$C["path"]?>/"><i class="fa fa-home" aria-hidden="true"></i> 首頁</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="manage" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="true"><i class="fas fa-wrench"></i> 管理</a>
                    <div class="dropdown-menu" aria-labelledby="manage">
                        <a class="dropdown-item" href="<?=$C["path"]?>/manage/data/new"><i class="fas fa-database"></i> 新增資料</a>
                        <a class="dropdown-item" href="<?=$C["path"]?>/manage/upload/"><i class="fas fa-file-upload"></i> 上傳檔案</a>
                        <a class="dropdown-item" href="<?=$C["path"]?>/manage/qualification/"><i class="fas fa-check"></i> 申請資格</a>
                        <a class="dropdown-item" href="<?=$C["path"]?>/manage/apply/"><i class="fas fa-building"></i> 申請單位</a>
                        <a class="dropdown-item" href="<?=$C["path"]?>/manage/account/"><i class="fa fa-user"
                                aria-hidden="true"></i> 帳號</a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav mt-2 mt-md-0">
                <li class="nav-item">
                    <?php if ($U["islogin"]) {?>
                        <a class="nav-link" href="<?=$C["path"]?>/logout/"><?=htmlentities($U['data']['adm_account'])?>(<?=htmlentities($U['data']['adm_name'])?>) /
                        <i class="fas fa-sign-out-alt"></i> 登出</a>
                    <?php } else {?>
                        <a class="nav-link" href="<?=$C["path"]?>/login/"><i class="fas fa-sign-in-alt"></i>
                        登入</a>
                    <?php }?>
                </li>
            </ul>
        </div>
    </nav>
</header>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-118980789-5"></script>
<script>
window.dataLayer = window.dataLayer || [];

function gtag() {
    dataLayer.push(arguments);
}
gtag('js', new Date());

gtag('config', 'UA-118980789-5');
</script>
