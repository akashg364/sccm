<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_components".
 *
 * @property int $id
 * @property string $name
 * @property string $system_name
 * @property string $description
 * @property string $blocks
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 */
class Component extends \common\models\BaseModel {
    /**
     * @inheritdoc
     */
    public $component_blocks;
    
    public static function tableName()
    {
        return 'tbl_components';
    }

	public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'system_name'], 'required'],
            [['description', 'blocks'], 'safe'],
            [['created_by', 'updated_by','created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['name', 'system_name'], 'string', 'max' => 30],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'system_name' => 'System Name',
            'description' => 'Description',
            'blocks' => 'Blocks',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
        ];
    }

	// Relation
    public function getCreatedBy(){
        return $this->hasOne(User::className(),["id"=>"created_by"]);
    } 

    // Relation
    public function getUpdatedBy(){
        return $this->hasOne(User::className(),["id"=>"modified_by"]);
    } 
    
    public static function getComponantBlockList(){
        $componantBlock = [
            'CSS_PRIMARY-CSS_SECONDARY' => 'CSS_PRIMARY-CSS_SECONDARY',
            'CSS_SECONDARY-CSS_PRIMARY' => 'CSS_SECONDARY-CSS_PRIMARY', 
            'CSS_PRIMARY-DEMARC' => 'CSS_PRIMARY-DEMARC',
            'CSS_SECONDARY-DEMARC' => 'CSS_SECONDARY-DEMARC',
            'CSS_SECONDARY'=>'CSS_SECONDARY',
             'CSS_PRIMARY'=>'CSS_PRIMARY',
            'AG1_PRIMARY' => 'AG1_PRIMARY',
            'AG1_SECONDARY' => 'AG1_SECONDARY',
            'AG2_PRIMARY' => 'AG2_PRIMARY',
            'AG2_SECONDARY' => 'AG2_SECONDARY',
            'DEMARC'=>'DEMARC'
            ];
        return $componantBlock;
    }
    
    //Relation
    public function getComponentBlocks(){
        return $this->hasMany(ComponentBlocks::className(),['component_id'=>'id']);
    }
	
    public function deleteComponentBlockById($component_id = ''){        
        \Yii::$app
        ->db
        ->createCommand()
        ->delete('tbl_component_blocks', ['component_id' => $component_id])
        ->execute();
    }
}
