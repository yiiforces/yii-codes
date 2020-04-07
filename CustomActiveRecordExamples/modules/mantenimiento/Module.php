<?php
namespace modules\mantenimiento;

use Yii;
use yii\base\BootstrapInterface;
use yii\web\UrlRule;
use yii\helpers\Url;

class Module extends \yii\base\Module implements BootstrapInterface
{
	public $controllerNamespace = __NAMESPACE__ . '\controllers';

	public function bootstrap($app)
	{
		if($app instanceof \yii\console\Application)
		{
			$this->controllerNamespace = __NAMESPACE__ . '\cmd';
			return;
		}

        // webApplication
		$app->getUrlManager()->addRules([

		], false);
	}
}
