<?php 
namespace common\components\yii;

use yii;
use yii\helpers\Html;
Class ActionColumn extends \yii\grid\ActionColumn{

	protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        $iconName = "eye";
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        $iconName = "edit";
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        $iconName = "trash-alt";
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                    'class'=>($name=='delete')?'':'showModalButton',
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', '', ['class' => "fas fa-{$iconName} fa-lg"]);
                return Html::a($icon, $url, $options);
            };
        }
    }
}