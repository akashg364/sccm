<?php
/* @var $this \yii\web\View */
/* @var $content string */
use backend\assets\SCCMAppAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use yii\helpers\Url;
use yii\bootstrap\Nav;

SCCMAppAsset::register($this);
$baseUrl = SCCMAppAsset::$themeBaseUrl."/";
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="ccm-dashboard">
  <head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= ($this->title)?Html::encode($this->title):":: SCCM ::"; ?></title>
    <?php $this->head() ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">var baseUrl = '<?php echo Yii::$app->homeUrl;?>';</script>>
  </head>
  <body class="dashboard-body">
    <?php $this->beginBody() ?>
    <main>
      <header class="header navbar-fixed-top">
        <div class="container-fluid">
          <div class="">
            <div class="col-lg-8">
              <div class="logo-info">
                <div class="logo"><img src="<?=$baseUrl;?>img/logo.png" /><span class="pro-name">SCCM - Service Configuration & Compliance Manager</span></div>
              </div>
            </div>
              <?php if(Yii::$app->user->id){ ?>
            <div class="col-lg-4">              
              <div class="user-setting-div pull-right">
			         <ul class="nav navbar-top-links">
                                        <!-- /.dropdown -->
                                        <li class="dropdown">
                                            <a class="dropdown-toggle user-details" data-toggle="dropdown" href="#" aria-expanded="true">
                                                <i class="fa fa-user fa-fw"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-user">
                                                <li><a href="#"><i class="fa fa-user fa-fw"></i> <?php echo Yii::$app->user->identity->username; ?></a>
                                                </li>
                                                <li><a href="<?= Url::to(["/admin/user/change-password"]) ?>"><i class="fa fa-lock"></i> Change Password</a>
                                                </li>
                                                <li><a href="<?= Url::to(["/site/logout"]) ?>"  class="logout"><i class="fa fa-sign-out-alt"></i> Logout</a>
                                                </li>
                                            </ul>
                                            <!-- /.dropdown-user -->
                                        </li>
                                        <!-- /.dropdown -->
                                    </ul>
        </div>
              </div><?php } ?>
      <div class="col-lg-12">
        <div>
          <?=$this->render("sccm-menu")?>
        </div>
      </div>
    </div>
  </div>
</header>

<article class="body-wrapper">
  <section class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="filter-wrapper">
        </div>
      </div>
    </div>
  </section>
  <section class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
         <?= Alert::widget() ?>
        <div class="container-panel">
          <div class="panel-title clearfix">
            <div class="row">
              <div class="col-lg-6">
                <h5 class="p-title"><?=$this->title;?></h5>
              </div>
              <div class="col-lg-6">
                <div class="pull-right">
                </div>
              </div>
            </div>
          </div>
          
          <?=$content;?>
        </div>
      </div>
    </div>
  </section>
</article>
</main>
<?php echo $this->render("sccm-modal");?>
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>