<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sccm_report".
 *
 * @property int $id
 * @property int $service_type
 * @property int $sub_service_type
 * @property int $managed_unmanaged 0-Unmanaged 1- Managed
 * @property int $termination_point
 * @property int $spur_dual_homed
 * @property int $with_mds 0- No 1-Yes
 * @property int $with_eds
 * @property int $tagged_type
 * @property int $single_dual_pe 0 - Single 1- dual 2- Both
 * @property int $routing_protocol
 * @property string $concat_data
 * @property int $conf_uploaded 0 - Not uploaded 1- Uploaded
 * @property int $created_by
 * @property string $created_on
 * @property string $payload
 * @property string $dryrun
 * @property string $l2_document
 */
class SccmReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sccm_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_type', 'sub_service_type', 'managed_unmanaged', 'termination_point', 'spur_dual_homed', 'with_mds', 'with_eds', 'tagged_type', 'single_dual_pe', 'routing_protocol', 'conf_uploaded', 'created_by'], 'integer'],
            [['created_on'], 'safe'],
            [['concat_data', 'payload', 'dryrun', 'l2_document'], 'string', 'max' => 150],
        ];
    }
    
    public function getServiceName($serviceType_id)
    {
                    $serviceModel = new Services();
                    return $serviceModel->getServiceName($serviceType_id);
    }
    public function SubServices($subservice_id)
    {
                    $serviceModel = new SubServices();
                    return $serviceModel->getSubServiceRef($subservice_id);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_type' => 'Service Type',
            'sub_service_type' => 'Sub Service Type',
            'managed_unmanaged' => 'Managed Unmanaged',
            'termination_point' => 'Termination Point',
            'spur_dual_homed' => 'Spur Dual Homed',
            'with_mds' => 'With Mds',
            'with_eds' => 'With Eds',
            'tagged_type' => 'Tagged Type',
            'single_dual_pe' => 'Single Dual Pe',
            'routing_protocol' => 'Routing Protocol',
            'concat_data' => 'Concat Data',
            'conf_uploaded' => 'Conf Uploaded',
            'created_by' => 'Created By',
            'created_on' => 'Created On',
            'payload' => 'Payload',
            'dryrun' => 'Dryrun',
            'l2_document' => 'L2 Document',
        ];
    }
}
