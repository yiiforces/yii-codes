<?php
namespace base;
use PDO;
use Yii;
use yii\base\InvalidCallException;
use yii\db\Query;
use yii\db\Schema;
use yii\db\PdoValue;
use yii\data\Sort;
use yii\data\ActiveDataProvider;

use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

abstract class ActiveRecord extends \yii\db\ActiveRecord
{
	use traits\TraitModel;

	public $confirmDelete;

	const ID_USER_SYS_APP = 1;
	const ID_USER_ROOT    = 2;

	#const status
	const STATUS_ACCESO_ACTIVE   = 1;
	const STATUS_ACCESO_BAN      = 3;
	const STATUS_ACCESO_BAN_TMP  = 4;
	const STATUS_ACCESO_INACTIVE = 5;

	const SCENARIO_SEARCH = 'search';
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';
	const SCENARIO_DELETE = 'delete';


    public function init()
    {
        parent::init();

        $rf = new \ReflectionClass(__CLASS__);

        if($rf->isAbstract() == false)
            $this->loadDefaultValues();
    }

    public static function attributesNull()
    {
    	$attributes = [];
    	$columns    = static::getTableSchema(true)->columns;
    	foreach($columns as $column)
    	{
    		if($column->allowNull == true)
    			array_push($attributes, $column->name);
    	}

    	return $attributes;
    }

    public function unsetAttributes()
    {
    	foreach($this->activeAttributes() as $attr)
    		$this->{$attr} = null;
    }


    // retornar array con  activeDataProvider + model filter
    public static function search($params)
    {
		$model = new static();
		$model->scenario =  static::SCENARIO_SEARCH;
		$model->unsetAttributes();
		$model->load($params);
		$query = $model->find();

		$columns    = static::getTableSchema(true)->columns;
		$attributes = array_intersect(array_keys($columns), $model->activeAttributes());
		$flagStatus = true;

		$columnsTypeNumeric = [
			Schema::TYPE_PK,
			Schema::TYPE_UPK,
			Schema::TYPE_BIGPK,
			Schema::TYPE_UBIGPK,
			Schema::TYPE_TINYINT,
			Schema::TYPE_SMALLINT,
			Schema::TYPE_INTEGER,
			Schema::TYPE_BIGINT,
			Schema::TYPE_FLOAT,
			Schema::TYPE_DOUBLE,
			Schema::TYPE_DECIMAL,
		];

		foreach($attributes as $key => $attribute)
		{
			$column  = $columns[$attribute];
			$value   = $model->{$attribute};

			if($value != 0 and empty($value))
				continue;

			if(in_array($column->type, $columnsTypeNumeric))
			{
				if($value == 0){
					$query->andFilterWhere(['=', $attribute, $model->{$attribute}]);
					continue;
				}

				$flagStatus = (preg_match('<^-?(\d+)+((\.|\d{1,})?)$>', $value));
				if($flagStatus == false)
				{
					$query->andWhere('1=0');
					break;
				}

				if(in_array($column->type , [ Schema::TYPE_INTEGER, Schema::TYPE_PK, Schema::TYPE_SMALLINT]))
					$query->andFilterWhere(['=', $attribute, $model->{$attribute}]);
				else
					$query->andFilterWhere(['like', $attribute, $model->{$attribute}]);
				continue;
			}


        	$keywords = str_replace( ["\r\n", "\r", "\n", "\t"], '', $value);
        	$keywords = trim(preg_replace('<\s{2,}>', ' ', $keywords));
        	$keywords = \yii\helpers\StringHelper::truncate($keywords, 100);
        	$keywords = explode(' ', $keywords);
        	$keywords = array_unique($keywords);

			foreach($keywords as $word)
			{
				if(empty($word))
					continue;

				$query->andFilterWhere(['like', $attribute,  new PdoValue('%' . $word . '%', PDO::PARAM_STR) ]);
			}
		}
		$sortAttr = ($model->hasProperty('fecha_actualizacion')) ? 'fecha_actualizacion' : 'id';

		return [
			'model'  => $model,
			'dataProvider' => new ActiveDataProvider([
				'query' => $query,
				'sort'  =>[
					'defaultOrder' => [
						$sortAttr => 'desc',
					]
				]
			])
		];
    }

  	public function attributeSearch()
  	{
  		return $this->attributes();
  	}

	public function delete()
	{
		if($this->isNewRecord)
			return true;

		try{
			return parent::delete();
		}
		catch(\Exception $e){
			Yii::error('error al eliminar en ' .  static::className(). '::delete()',  static::className() );
			return false;
		}
	}
}
