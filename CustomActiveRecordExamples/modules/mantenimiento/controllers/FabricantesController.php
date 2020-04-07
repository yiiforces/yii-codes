<?php
namespace modules\mantenimiento\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use base\Controller;
use yii\helpers\Url;
use yii\helpers\Html;

use  modules\mantenimiento\models\Fabricante;

class FabricantesController extends \base\Controller
{
    public function behaviors()
    {
        return [
            'check-access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'delete' => [
                'class'      => 'base\actions\DeleteAction',
                'modelClass' => Fabricante::className(),
                'title'      => Yii::t('app','Eliminar Fabricante'),
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->configure([
            'iconClass'   => 'list',
            'H1'          => Yii::t('app', 'Mantenimiento - Fabricante'),
            'H2'          => Yii::t('app', 'Mantenimiento - Fabricante'),
            'title'       => Yii::t('app', 'Mantenimiento - Fabricante') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        $data = Fabricante::search(Yii::$app->request->get());
        return $this->render('index', $data);
    }

    public function actionCreate()
    {
        $model = new Fabricante();
        $model->formName = 'create-fabricante';
        $model->scenario = $model::SCENARIO_CREATE;

        if(Yii::$app->request->isPost)
        {
            $data       = Yii::$app->request->post($model->formName);
            $statusSave = $this->processForm($model, $data, 'refresh', null, []);
            return $this->asJson($statusSave);
        }

        $this->view->configure([
            'iconClass'   => 'plus',
            'H1'          => Yii::t('app', 'Mantenimiento - Fabricante'),
            'H2'          => Yii::t('app', 'Mantenimiento - Nuevo fabricante'),
            'title'       => Yii::t('app', 'Mantenimiento - Nuevo fabricante') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = Fabricante::findOne($id);
        if(is_null($model))
            Yii::$app->helper->httpError(404);
        else
            $model->scenario = Fabricante::SCENARIO_UPDATE;

        $model->formName = 'update-fabricante';

        if(Yii::$app->request->isPost)
        {
            $data       = Yii::$app->request->post($model->formName);
            $statusSave = $this->processForm($model, $data, 'refresh');
            return $this->asJson($statusSave);
        }

        $this->view->configure([
            'iconClass'   => 'list',
            'H1'          => Yii::t('app', 'Actualizar Fabricante {0}', [ Html::encode($model->nombre) ]),
            'H2'          => Yii::t('app', 'Mantenimiento - actualizar fabricante'),
            'title'       => Yii::t('app', 'Mantenimiento - actualizar fabricante ') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}
