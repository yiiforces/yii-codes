<?php
namespace app;
use Yii;
use yii\db\Connection;
use yii\db\SchemaBuilderTrait;
use yii\db\TableSchema;
use yii\di\Instance;

abstract class UsageQueryBuilderWithOutMigration extends \yii\base\BaseObject
{
	use SchemaBuilderTrait;

	public  $db        = 'db';
	public  $tableName = '{{%config}}';

	public function init()
	{
		$this->db  = Instance::ensure($this->db, Connection::className());
		$rawTbName = $this->db->getSchema()->getRawTableName($this->tableName);
		$this->run();
	}

	protected function getDb()
	{
		return $this->db;
	}

	protected function run()
	{
		$hasTableName = $this->db->schema->getTableSchema($this->tableName, true);
		// exist table end!
		if($hasTableName instanceof TableSchema)
			return;

		$sql          = [];
		$queryBuilder = $this->db->getQueryBuilder();
		$rawTbName    = $this->db->getSchema()->getRawTableName($this->tableName);

		// add schema
		$sql[] = $queryBuilder->createTable($rawTbName, [
			'id'                => $this->primaryKey(),
			'key_config'        => $this->string(255)->notNull(),
			'description'       => $this->text()->defaultValue(null),
			'default_value'     => $this->text()->defaultValue(null),
			'current_value'     => $this->text()->defaultValue(null),
			'is_json'           => $this->boolean()->notNull()->defaultValue(false),
		]);
		$sql[] = $queryBuilder->addCommentOnTable($rawTbName, 'Tabla para configuraciones parametrizables de aplicación');

		// add index
		$sql[] = $queryBuilder->createIndex($rawTbName . '_index01', $rawTbName ,'id',            true);
		$sql[] = $queryBuilder->createIndex($rawTbName . '_index02', $rawTbName ,'key_config',    true);
		$sql[] = $queryBuilder->createIndex($rawTbName . '_index03', $rawTbName ,'is_json',      false);

		// comment columns
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'id',            Yii::t('app.config', 'ID') );
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'key_config',    Yii::t('app.config', 'Opción') );
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'is_json',       Yii::t('app.config', '¿Formato JSON?') );
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'current_value', Yii::t('app.config', 'Valor Actual') );
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'default_value', Yii::t('app.config', 'Valor predeterminado') );
		$sql[] = $queryBuilder->addCommentOnColumn($rawTbName, 'description',   Yii::t('app.config', 'Información adicional') );

		$transaction = $this->db->beginTransaction();

		foreach($sql as $command)
		{
            $this->db->createCommand()
                ->setRawSql($command)
                ->execute();
		}

		$transaction->commit();
		return true;
	}
}
