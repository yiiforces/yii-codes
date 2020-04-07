<?php
namespace modules\mantenimiento\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use base\Controller;
use yii\helpers\Url;
use yii\helpers\Html;

use  modules\mantenimiento\models\TipoDocumentoLegal;

class DocumentoLegalController extends \base\Controller
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
                'modelClass' => TipoDocumentoLegal::className(),
                'title'      => Yii::t('app','Eliminar Doc. Legal'),
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->configure([
            'iconClass'   => 'list',
            'H1'          => Yii::t('app', 'Mantenimiento - Documento legal'),
            'H2'          => Yii::t('app', 'Mantenimiento - Documento legal'),
            'title'       => Yii::t('app', 'Mantenimiento - Documento legal') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        $data = TipoDocumentoLegal::search(Yii::$app->request->get());
        return $this->render('index', $data);
    }

    public function actionCreate()
    {
        $model = new TipoDocumentoLegal();
        $model->formName = 'create-tipo-documento-legal';
        $model->scenario = $model::SCENARIO_CREATE;

        if(Yii::$app->request->isPost)
        {
            $data                      = Yii::$app->request->post($model->formName);
            $messages['successUpdate'] = Yii::t('app', '¡Nuevo registro creado exitosamente!');
            $statusSave                = $this->processForm($model, $data, 'refresh', null, $messages);
            return $this->asJson($statusSave);
        }

        $this->view->configure([
            'iconClass'   => 'plus',
            'H1'          => Yii::t('app', 'Mantenimiento - Documento legal'),
            'H2'          => Yii::t('app', 'Mantenimiento - Nuevo documento legal'),
            'title'       => Yii::t('app', 'Mantenimiento - Nuevo documento legal') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = TipoDocumentoLegal::findOne($id);
        if(is_null($model))
            Yii::$app->helper->httpError(404);
        else
            $model->scenario = TipoDocumentoLegal::SCENARIO_UPDATE;

        $model->formName = 'update-tipo-documento-legal';

        if(Yii::$app->request->isPost)
        {
            $data       = Yii::$app->request->post($model->formName);
            $statusSave = $this->processForm($model, $data, 'refresh');
            return $this->asJson($statusSave);
        }

        $this->view->configure([
            'iconClass'   => 'list',
            'H1'          => Yii::t('app', 'Actualizar Tipo documento legal {0}', [ Html::encode($model->codigo) ]),
            'H2'          => Yii::t('app', 'Mantenimiento - actualizar tipo doc. legal'),
            'title'       => Yii::t('app', 'Mantenimiento - actualizar tipo doc. legal ') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}
