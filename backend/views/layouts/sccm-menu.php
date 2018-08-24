<?php
use mdm\admin\components\MenuHelper;
use yii\helpers\Url;
$items = MenuHelper::getAssignedMenu(Yii::$app->user->id);
?>
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
        

        <ul id="w1" class="nav navbar-nav">
          
          <?php
          foreach ($items as $value){

            if(!empty($value['items'])) { ?>

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
          <?php  } 
          }
        ?>
      </ul>
    </div>
  </div>
</div>
</nav>