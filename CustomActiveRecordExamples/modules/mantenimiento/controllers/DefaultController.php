<?php
namespace modules\mantenimiento\controllers;
use Yii;
use yii\helpers\Url;

class DefaultController extends \base\Controller
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

    public function actionIndex()
    {
        $this->view->configure([
            'iconClass'   => 'list',
            'H1'          => Yii::t('app', 'Panel administrativo'),
            'H2'          => Yii::t('app', 'Panel administrativo - Mantenimiento'),
            'title'       => Yii::t('app', 'Panel administrativo - Mantenimiento') . ' - ' . Yii::$app->name,
            'breadcrumbs' => [
            ]
        ]);

        return $this->render('index');
    }
}
