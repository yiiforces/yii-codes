<?php
namespace base;

use Yii;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use Helpers;

class View extends \yii\web\View
{
	protected $H1;
	protected $H2;
	protected $H3;
	protected $viewHint;
	protected $menuContext = [];
	protected $iconClass;
	private   $breadcrumbs = [];

	#//@todo..
	#protected $logoHeader;
	#protected $logoMain;
	#protected $defaultAvatar;
	#protected $favicon;

	private $propertiesTypeStrings = [
		'H1', 'H2', 'H3', 'viewHint', 'title', 'iconClass', 'logoHeader', 'logoMain', 'defaultAvatar', 'favicon'
	];

	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->title = Yii::$app->name;
		$this->H1    = Yii::$app->name;
	}

	public function __set($property, $value)
	{
		if(in_array($property, $this->propertiesTypeStrings))
		{
			$this->{$property} = (is_string($value)) ? $value : null;
			return;
		}

		return parent::__set($property, $value);
	}

	public function __get($property)
	{
		if(in_array($property, $this->propertiesTypeStrings))
			return (!is_null($this->{$property})) ? Html::encode($this->{$property}) : null;

		return parent::__get($property);
	}

	public function getIsAjaxOnly()
	{
		return Helper::getIsAjaxOnly();
	}

	public function setBreadcrumbs(array $value)
	{
		$this->breadcrumbs = $value;
	}

	public function getBreadcrumbs()
	{
		return Breadcrumbs::widget([
			'homeLink' => null,
			'links'    => (!empty($this->breadcrumbs)) ?  $this->breadcrumbs : [['label' => '', 'link'=>'', 'template' => '']],
		]);
	}

	public function configure($params = [])
	{
		foreach ($params as $key => $value)
		{
			if($this->hasProperty($key) and $this->canSetProperty($key))
				$this->{$key} = $value;
		}
	}
}
