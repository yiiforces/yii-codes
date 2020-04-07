<?php
namespace base;

use Yii;
use yii\data\Pagination;

class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    public function setPagination($value)
    {
    	if((is_array($value)) || ($value instanceof Pagination) || ($value === false))
    		parent::setPagination($value);

    	$pagination           = new Pagination();
    	$pagination->pageSize = Yii::$app->params['dataProvider']['pageSize'] ?? null;
    	parent::setPagination($pagination);
    }
}
