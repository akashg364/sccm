<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\CcmAppAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use yii\helpers\Url;
use mdm\admin\components\MenuHelper;
use yii\bootstrap\Nav;

$items = MenuHelper::getAssignedMenu(Yii::$app->user->id);

CcmAppAsset::register($this);
$baseUrl = CcmAppAsset::$themeBaseUrl."/";
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="ccm-dashboard">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
</head>
<body class="dashboard-body">
<?php $this->beginBody() ?>

<main>
  <header class="header navbar-fixed-top">
    <div class="container-fluid">
      <div class="">
        <div class="col-lg-6">
          <div class="logo-info">
            <div class="logo"><img src="<?php echo $baseUrl;?>img/logo.png" /><span class="pro-name">SCCM - Configuration & Compliance Manager</span></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="user-setting-div pull-right">
          <ul class="nav navbar-top-links">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle user-details" data-toggle="dropdown" href="#" aria-expanded="true">
                      <i class="fa fa-user fa-fw"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li><a href="<?php echo Url::to(["/site/logout"]);?>" class="logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
        </div>
        </div>

        <div class="col-lg-12">
          <div>
            <nav class="navbar">
              <div class="row">
                <div class="content-fluid">
                  <div class="navbar-header">
                    <button class="navbar-toggle collapsed" aria-expanded="false" aria-controls="navbar" type="button" data-toggle="collapse" data-target="#navbar">
                      <span class="sr-only">Toggle navigation</span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                  </div>
                  <div class="navbar-collapse collapse" id="navbar">
                    <ul class="nav navbar-nav">
                        
                        <?php
                        foreach ($items as $value){ 
                            if(!empty($value['items'])) {?>
                                        <li class="dropdown"><a class="dropdown-toggle" href="#" data-toggle="dropdown"><?= $value['label'] ?> <b class="caret"></b></a>
                                           <ul id="w2" class="dropdown-menu">

                                            <?php 
                                            foreach($value['items'] as $value_sub){ 
                                            echo '<li><a href="'.Url::to([$value_sub['url'][0]]).'" tabindex="-1">'.$value_sub['label'].'</a></li>';
                                            }
                                        ?>
                                           </ul>
                                        </li>
                                  <?php }else { ?>
                                        <li><a href="<?php echo Url::to([$value['url'][0]]);?>"><?= $value['label'] ?></a>
                            <?php  } ?>
                        
                         <?php }
                        ?>
                      
                      
        
                      <!-- <li class=""><a href="#">Services</a></li> -->
                     <!--  <li><a href="#">Configuration</a></li>
                      <li><a href="#">Compliance</a></li> -->
                    </ul>
                  </div><!--/.nav-collapse --> 
                </div>
              </div>
              
            </nav>
          </div>
        </div>
      </div>
    </div>
  </header>
  <article class="body-wrapper">
    <section class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <div class="container-panel">
            <br/> <br/> <br/>
             <?= Alert::widget() ?>
            <?=$content;?>
          </div>
        </div>
      </div>
    </section>
  </article>
 <script>
     $(document).ready(function(){
        $('.dropdown-toggle').dropdown();
    });
</script>
</main>
<?php $this->endBody() ?>
    <script src="http://code.jquery.com/jquery-migrate-3.0.0.js"></script>
</body>
</html>
<?php $this->endPage() ?>
