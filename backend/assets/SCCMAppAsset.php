<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SCCMAppAsset extends AppAsset {
	public $basePath = '@webroot/themes/sccm/';
	public $baseUrl = '@web/themes/sccm/';
	public static $themeBaseUrl;

	public $css = [
		'bootstrap/datetimepicker/bootstrap-datetimepicker.css',
		'css/custom-skin.css',
		'css/cui-basic.min.css'
	];

	public $js = [
         "../../js/common.js",
         'fontawesome/svg-with-js/js/fontawesome-all.js',
         'bootstrap/datetimepicker/bootstrap-datetimepicker.js',
         'bootstrap/js/custom-js.js',
         'bootstrap/js/bootstrap-multiselect.js',        
	];

    /**
     * @inheritdoc
     */
    public function init() {

        $webPath  = Yii::getAlias("@web")."/";
        $controllerId = Yii::$app->controller->id;
        $actionId = Yii::$app->controller->action->id;
        if (isset($controllerId) && !empty($controllerId)) {
            if (isset($actionId) && !empty($actionId)) {
                // load theme/theme-name/js/controller-id/action-id.js file if exists
                if (file_exists(sprintf('js/%s/%s.js', $controllerId, $actionId))) {
                    $this->js[] = sprintf($webPath.'js/%s/%s.js', $controllerId, $actionId);
                }
            } else {
                 // load theme/theme-name/js/controller-id/index.js file if exists
                if (file_exists(sprintf('js/%s/%s.js', $controllerId, Yii::$app->controller->defaultAction))) {
                    $this->js[] = sprintf($webPath.'js/%s/%s.js', $controllerId, Yii::$app->controller->defaultAction);
                }
            }
           
            // load theme/theme-name/js/controller-id/common.js if exist
            if (file_exists(sprintf('js/%s/%s.js', $controllerId, 'common'))) {
                $this->js[] = sprintf($webPath.'js/%s/%s.js', $controllerId, 'common');
            }
        }
        $this->setThemeBaseUrl();
        parent::init();
    }

    private function setThemeBaseUrl(){
       return self::$themeBaseUrl = Yii::getAlias($this->baseUrl);
    }

	
}
