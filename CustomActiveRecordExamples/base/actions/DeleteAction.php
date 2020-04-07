<?php
namespace base\actions;
use Yii;

class DeleteAction extends \yii\base\Action
{
	public $modelClass;
	public $modelViewClass;

	public $key = 'id';
	public $messagesFlash;
	public $messagesHttpErrors;
	public $modelScenario;
	public $title;

	public $viewFile = 'view';

	private $model;
	private $modelView;

	public function init()
	{
		parent::init();

		$id                       = (int) Yii::$app->request->getQueryParam($this->key, 0);
		$this->messagesFlash      = Yii::$app->params['messages']['models'];
		$this->messagesHttpErrors = Yii::$app->params['messages']['httpErrors'];
		$this->model              = $this->modelClass::findOne($id);

		if(!is_null($this->modelViewClass))
			$this->modelView = $this->modelViewClass::findOne($id);
		else
			$this->modelView = $this->model;

		if(is_null($this->title))
			$this->title = Yii::t('app', 'Eliminar registro');


		Yii::setAlias('@actionsViewPath'    , __DIR__ .'/views');
		Yii::setAlias('@controllerViewPath' , $this->controller->getViewPath());

		if(is_null($this->model))
			return;

		$this->model->scenario = ($this->modelScenario) ?? $this->model::SCENARIO_DELETE;
		$this->model->formName = 'delete-' . $this->model::getShortName();
	}

	public function run()
	{

		if(Yii::$app->request->isPost)
		{

			if(is_null($this->model))
			{
				return $this->controller->asJson([
					'status'     => true,
					'statusCode' => 'MODEL_SUCCESS_DELETE',
					'content'    => $this->controller->renderPartial('@actionsViewPath/success-delete'),
				]);
			}

			$this->model->load(Yii::$app->request->post());

			if($this->model->validate()== false)
			{
				return $this->controller->asJson([
					'status'     => false,
					'statusCode' => 'MODEL_FAIL_DELETE',
					'statusText' => $this->model->getFirstError('confirmDelete')
				]);
			}

			if($this->model->delete() )
			{
				return $this->controller->asJson([
					'status'     => true,
					'statusCode' => 'MODEL_SUCCESS_DELETE',
					'content'    => $this->controller->renderPartial('@actionsViewPath/success-delete'),
				]);
			}

			return $this->controller->asJson([
				'status'     => false,
				'statusCode' => 'MODEL_FAIL_DELETE',
				'statusText' => $this->messagesFlash['errorDeleteFk']
			]);
		}

		if(is_null($this->model) )
		{
			return $this->controller->render('@actionsViewPath/error', [
				'message'    => $this->messagesHttpErrors[404],
				'statusCode' => 404,
			]);
		}

        $this->controller->view->configure([
            'H1' => $this->title,
        ]);

		return $this->controller->render('@actionsViewPath/delete-action', [
			'model'     => $this->model,
			'modelView' => $this->modelView
		]);
	}
}
