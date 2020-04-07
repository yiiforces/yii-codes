<?php
namespace modules\mantenimiento\models;

class Fabricante extends \base\ActiveRecord
{

    public function scenarios()
    {
    	return [
			static::SCENARIO_CREATE => ['nombre',],
			static::SCENARIO_UPDATE => ['nombre'],
            static::SCENARIO_DELETE => ['confirmDelete'],
    		static::SCENARIO_SEARCH => array_values($this->attributeSearch()),
    	];
    }

	public function rules()
	{
		if($this->scenario == static::SCENARIO_SEARCH)
			return [[array_values($this->attributeSearch()), 'safe' ]];

		return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 20],
            [['nombre'], 'unique'],
		];
	}
}
