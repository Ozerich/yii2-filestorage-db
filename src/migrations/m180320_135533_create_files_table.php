<?php

namespace blakit\filestorage\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `files`
 */
class m180320_135533_create_files_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableSchema = \Yii::$app->db->schema->getTableSchema('{{%files}}');
        if ($tableSchema) {
            return true;
        }

        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'scenario' => $this->string()->notNull()->defaultValue('default'),
            'hash' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(),
            'ext' => $this->string()->notNull(),
            'size' => $this->integer()->notNull(),
            'mime' => $this->string()->notNull(),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'thumbnails' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%files}}');
    }
}
