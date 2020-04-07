<?php
namespace base\traits;
use \ReflectionClass;

use Yii;
use yii\helpers\Inflector;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;


trait TraitModel
{
	protected $formName;

	public static function getShortName()
	{
		$rf = new \ReflectionClass(static::className());
		return $rf->getShortName();
	}

    public function __get($name)
    {
        $keysFields = array_keys($this->fields());

        if($this->canGetProperty($name) || !in_array($name, $keysFields) )
            return parent::__get($name);

        $fieldIndex = $this->toArray([$name], [$name], true);

        if(!is_array($fieldIndex[$name]))
            return $fieldIndex[$name];

        // convert array key field to object recursive:
        $fn2Object = function ($node) use (&$fn2Object) {

            $stdObj = new \stdClass();

            foreach ($node as $key => $value)
            {
                if (is_array($value) and \yii\helpers\ArrayHelper::isAssociative($value) )
                    $value = $fn2Object($value);

                $stdObj->$key = $value;
            }

            return $stdObj;
        };

        return $fn2Object($fieldIndex[$name]);
    }

	public function getClientErrors($indexMultiple = null)
	{
		$errors = [];

		if($this->hasErrors() === false)
			return $errors;

		foreach($this->getErrors() as $attribute => $listError)
		{
			if(!is_null($indexMultiple))
				$attribute = "[$indexMultiple]" . $attribute;

			$errors[Html::getInputId($this, $attribute)] = $listError;
		}

		return $errors;
	}

	public function setFormName($name = null)
	{
		if($name == null)
		{
			$rf   = new ReflectionClass($this);
			$name = $rf->getShortName();
		}

		$this->formName = Inflector::camelize($name);
	}

	public function formName()
	{
		if(is_null($this->formName))
			$this->setFormName();

		return $this->formName;
	}

	// alias formName()
	public function getFormName()
	{
		return $this->formName();
	}

	public function save($runValidation = true, $attributeNames = NULL)
	{
		if($this instanceof \yii\db\ActiveRecord)
			return parent::save($runValidation, $attributeNames);

		// overload save to \yii\base\model
		return false;
	}
}


