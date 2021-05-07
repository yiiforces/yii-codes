<?php
use Yii;
use yii\web\MethodNotAllowedHttpException;
use yii\helpers\Html;


class AnyController extends \yii\web\Controller
{

	public function actionUpdate($id)
	{
		$model = $this->findModel($id);


		if(Yii::$app->request->isPost)
		{
			// only post+ajax, please configure behaviors width yii\filters\AjaxFilter
			// and remove this if condition
			if(Yii::$app->request->isAjax == false)
				throw new MethodNotAllowedHttpException();

			$model->load(Yii::$app->request->post());
			$model->naturalzaI = implode(',', $model->naturalzaI); // @see beforeSave event in your model
			$model->categoria  = implode(',', $model->naturalzaI); // @see beforeSave event in your model
			$model->validate();

			// when have error validation
	        if($this->hasErrors() === true)
	        {
				$errors = [];
				// get all errors list to update ActiveForm labels errors
	        	foreach($model->getErrors() as $attribute => $listError)
	        		$errors[Html::getInputId($model, $attribute)] = $listError;

	        	Yii::$app->response->setStatusCode(422); // header to error validate
				return $this->asJson([
					'errors'  => $errors,
					'message' => Yii::t('app', 'any msn error validate'),
				]);
	        }

	        // when have error save db..
	        if($model->save() === false)
	        {
	        	Yii::$app->response->setStatusCode(500); // error save header 500
		        return $this->asJson([
					'message' => Yii::t('app', 'any msn error save in db'),
		        ]);
	        }

	        // when success save model:
	        Yii::$app->response->setStatusCode(201); // 201 update, 200 created..
	        return $this->asJson([
	        	'attributes' => $model->toArray(),
	        	'message'    => Yii::t('app', 'Success update model'),
	        ]);
		}

		return $this->render('update', [
			'model' => $model
		]);
	}
}
