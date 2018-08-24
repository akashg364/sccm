<?php

use yii\db\Migration;
use yii\db\Schema;

class m170921_160117_service extends Migration
{
    public function up()
    {
        $this->createTable('services' ,[
            'id' => Schema::TYPE_PK,
            'services' => Schema::TYPE_STRING,
            'description' => Schema::TYPE_TEXT
        ]);

    }

    public function down()
    {
        $this->dropTable('services');
    }
}
