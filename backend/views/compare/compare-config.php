<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\web\View;

$this->registerCssFile('@web/css/file_compare_css/diffview.css',['depends' =>[\yii\bootstrap\BootstrapAsset::className()]],View::POS_LOAD);
$this->registerJsFile(Yii::$app->request->baseUrl."/js/file_compare_js/difflib.js",['depends' => [yii\web\JqueryAsset::className()]]);
$this->registerJsFile(Yii::$app->request->baseUrl."/js/file_compare_js/diffview.js",['depends' => [yii\web\JqueryAsset::className()]]);

/* @var $this yii\web\View */
/* @var $searchModel app\models\CompareShowrunSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Nip Vs Showrun Config';
//$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('.per-table, thead, tr, th {
    white-space: pre-wrap !important;
 
}'
);

/*
$nip_blockData = AuditAndRemediation::downloadFile($nip_showrun_data['nip_block']);
$base_text = $nip_blockData;

$shwrunData = AuditAndRemediation::downloadFile($nip_showrun_data['showrun_block']);
$new_text = $shwrunData;
**/

# for NIP
$base_text = file_get_contents('C:/xampp/htdocs/SCCM/backend/upload/showrun.txt');; 

# for Show run
$new_text = file_get_contents('C:/xampp/htdocs/SCCM/backend/upload/nip.txt');

$new_text = explode("\n", $new_text);
array_splice($new_text, 0, 9);
$new_text = implode("\n", $new_text);
?>
<div style="font-size: 10px; margin-left: 10px; float:right; margin-top: -2.3%;">

         <span style="color: green;">Green : Configuration available on the device but NOT in NIP </span><br>
         <span style="color:red;">Red : Configuration available in the NIP but NOT on the device</span><br>
         <span>Black : Configuration present in both NIP & SHOWRUN</span>
     </div>
<?php Pjax::begin(['timeout' => 10000, 'id' => 'container_pjax']); ?>

    <div class="">
        <div class="top">
            <!-- <strong>Context size (optional):</strong>  -->
            <input type="hidden" id="contextSize" value="" />
        </div>
        
        <div class="textInput" style="margin-top: 1%;">
            <h4>Nip</h4>
            <textarea class="form-control" rows="16" id="baseText" readonly><?php echo $base_text;?></textarea>
        </div>

        <div class="textInput spacer">
            <h4>Show run</h4>
            <textarea class="form-control" rows="16" id="newText" readonly><?php echo $new_text;?></textarea>
        </div>

        <div class="viewType">
            <input type="radio" class="radio-inline"  name="_viewtype" id="sidebyside" onclick="diffUsingJS(0);" /> <label for="sidebyside">Side by Side Diff</label>
            &nbsp; &nbsp;
            <input type="radio" class="radio-inline"  name="_viewtype" id="inline" onclick="diffUsingJS(1);" /> <label for="inline">Inline Diff</label>
        </div>
        
        <div class="row diffoutput">
            <div class="col-lg-12"  id="diffoutput">
            </div>
        </div>
        <?php Pjax::end(); ?>
    </div>


<script type="text/javascript">
	
    function diffUsingJS(viewType) {
	    "use strict";
        var byId = function (id) {
                return document.getElementById(id);
            },
            base = difflib.stringAsLines(byId("baseText")
                .value),
            newtxt = difflib.stringAsLines(byId("newText")
                .value),
            sm = new difflib.SequenceMatcher(base, newtxt),
            opcodes = sm.get_opcodes(),
            diffoutputdiv = byId("diffoutput"),
            contextSize = byId("contextSize").value;

        diffoutputdiv.innerHTML = "";
        contextSize = contextSize || null;

        diffoutputdiv.appendChild(diffview.buildView({
            baseTextLines: base,
            newTextLines: newtxt,
            opcodes: opcodes,
            baseTextName: "Nip Text",
            newTextName: "Show run Text",
            contextSize: contextSize,
            viewType: viewType
        }));
    }
</script>

<style type="text/css">
    body {
        font-size: 12px;
        font-family: Sans-Serif;
    }

    h2 {
        margin: 0.5em 0 0.1em;
        text-align: center;
    }

    .top {
        text-align: center;
    }

    .textInput {
        display: block;
        width: 49%;
        float: left;
    }

    textarea {
        width: 100%;
        height: 300px;
    }

    label:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    .spacer {
        margin-left: 10px;
    }

    .viewType {
        font-size: 16px;
        clear: both;
        text-align: center;
        padding: 1em;
    }

    #diffoutput {
        width: 100%;
    }
    .diffoutput{
        height: 300px;
        overflow: auto;
    }
    .textInput h4{
        /*background:#d8d7d7;*/
        text-align: center;
    }
</style>