<?php
    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\grid\GridView;
    use app\widgets\dashboard\boxes\BoxCounter;
    use app\widgets\faicons\Fa;
    use yii\widgets\Pjax;
?>
<div class="container-fluid ">
    <div class="row">
        <?php
            echo BoxCounter::widget([
                'title'               => Yii::t('app', 'Usuarios activos'),
                'iconClass'           => 'check',
                'iconClassColor'      => 'purple',
                'iconClassColorStyle' => null,
                'visible'             => false,
                'link'                => '', //Url::toRoute([$this->context->currentController . 'activos' ]),
                'count'               => 0, //$model->countActives(),
            ]);

            echo BoxCounter::widget([
                'title'               => Yii::t('app', 'Usuarios inactivos'),
                'iconClass'           => 'user-times',
                'iconClassColor'      => 'red',
                'iconClassColorStyle' => null,
                'visible'             => false,
                'link'                => '', //Url::toRoute([$this->context->currentController . 'inactivos' ]),
                'count'               => 0, //$model->countInactives(),
            ]);

            echo BoxCounter::widget([
                'title'               => Yii::t('app', 'Baneados'),
                'iconClass'           => 'pause-circle-o',
               'iconClassColor'       => 'red',
                'iconClassColorStyle' => null,
                'visible'             => false,
                'link'                => '', //Url::toRoute([$this->context->currentController . 'baneados' ]),
                'count'               => 0, //$model->countBan(),
            ]);

            echo BoxCounter::widget([
                'title'               => Yii::t('app', 'Nuevo registro'),
                'iconClass'           => 'plus-circle',
                'iconClassColor'      => '',
                'iconClassColorStyle' => null,
                'visible'             => true,
                'count'               => '&nbsp;',
                'link'                => Url::toRoute([$this->context->currentController . 'create' ]),
                'linkOptions'         => ['data-runtime'=>'dv-route'],
                'linkLabel'           => Yii::t('app', 'Nuevo registro'),
            ]);
        ?>
    </div>
</div>
<?php
    Pjax::begin([
        'enablePushState'       => false,
        'timeout'               => false,
        'clientOptions' => [
            'skipOuterContainers' => true,
        ],
    ]);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box overflow-xhidden overflow-yauto">
                <div class="box-content">
                    <div class="icon">
                            <div class="box-header mb-5">
                                <div class="clearfix">
                                    <h2><i class="fa fa-<?= $this->iconClass ?> pr-5"></i>&nbsp;
                                    <?= $this->H2; ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body mv-15 ">
                        <!--
                        <div class="grid-actions">
                            <div class="content-btn-actions">
                                <ul class="list-inline">
                                    <li>
                                        <a class="btn btn-default "><i class="fa fa-times"></i></a>
                                    </li>
                                    <li>
                                        <a class="btn btn-default "><i class="fa fa-check text-default"></i></a>
                                    </li>
                                    <li>
                                        <a class="btn btn-default "><i class="fa fa-edit"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        -->
                        <div class="table-responsive" style="padding-top:15px; padding-bottom:70px; border:0px solid #eee">
                            <?php
                                echo GridView::widget([
                                    'filterModel'  => $model,
                                    'dataProvider' => $dataProvider,
                                    'tableOptions' => ['class' => 'table'],
                                    'columns' => [
                                        Yii::$app->helper->configDropdownColumn([
                                        ], false),
                                        [
                                            'attribute'      => 'id',
                                            'headerOptions'  => ['class' => 'text-center' , 'style'=>'width:80px'],
                                            'contentOptions' => ['class' => ''],
                                            'visible'        => true,
                                        ],
                                        [
                                            'attribute'      => 'codigo',
                                            'format'         => 'text',
                                            'headerOptions'  => ['class' => ''],
                                            'contentOptions' => ['class' => ''],
                                            'visible'        => true,
                                        ],
                                        [
                                            'attribute'      => 'es_juridico',
                                            'format'         => 'boolean',
                                            'headerOptions'  => ['class' => '', 'style'=>''],
                                            'contentOptions' => ['class' => ''],
                                            'visible'        => true,
                                        ],
                                    ],
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
