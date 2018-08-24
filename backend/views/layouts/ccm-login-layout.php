<?php
/* @var $this \yii\web\View */
/* @var $content string */
use backend\assets\SCCMAppAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
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
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="login-bg">
    <?php $this->beginBody() ?>
    <div>
      <div class="container">
        <div class="row">
          <div class="col-md-6 col-md-offset-3">
            <div class="login-box">
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-container">
                    <img id="" class="profile-img-card" src="<?php echo $baseUrl?>img/login-logo.png">
                    <p id="profile-name" class="profile-name-card">
                      SCCM
                      <span>Version 0.1</span>
                    </p>
                    <?= $content ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>