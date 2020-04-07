<?php
namespace modules\mantenimiento\models;

class TipoDocumentoLegal extends \base\ActiveRecord
{

    public function scenarios()
    {
    	return [
			static::SCENARIO_CREATE => ['codigo', 'es_juridico'],
			static::SCENARIO_UPDATE => ['codigo'],
            static::SCENARIO_DELETE => ['confirmDelete'],
    		static::SCENARIO_SEARCH => array_values($this->attributeSearch()),
    	];
    }

	public function rules()
	{
		if($this->scenario == static::SCENARIO_SEARCH)
			return [[array_values($this->attributeSearch()), 'safe' ]];

		return [
            [['codigo'], 'required'],
            [['es_juridico'], 'boolean'],
            [['codigo'], 'string', 'max' => 20],
            [['codigo'], 'unique'],
		];
	}
}
