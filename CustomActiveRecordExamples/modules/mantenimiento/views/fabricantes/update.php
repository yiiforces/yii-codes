<?php
	use yii\helpers\ArrayHelper;
	use yii\helpers\Html;
	use yii\helpers\Url;

	use app\widgets\activeForm\ActiveForm;
	use app\widgets\select2\Select2Widget;

	$form = ActiveForm::begin([
		'registerDefaultJs' => true,
		'action'            => Url::to(['update', 'id'=> $model->id ])
	]);
?>

<div class="container-fluid mb-5">
	<div class="row">
		<div class="col-md-12">
			<div class="title-labels-form">
				<h2><?= Yii::t('app', 'Fabricante') ?></h2>
				<hr>
			</div>
		</div>
	</div>

	<div class="row mt-30">
		<div class="col-md-3"></div>
		<div class="col-md-6">
			<?php
				echo $form->field($model, 'nombre', [ 'errorOptions' => ['encode' => false ] ])->textInput([
					'autofocus'     => true,
					'autocomplete'  => 'off',
					'placeholder'   => true,
				]);
			?>
		</div>
	</div>
</div>
	<!--submit-->
<div class="container-fluid mb-5">
	<div class="row">
		<div class="col-md-3"></div>
		<div class="mv-15 col-md-6 text-right">
			<?= Html::submitButton('Aceptar', ['class' => 'btn btn-primary btn-block-sm']) ?>
		</div>
	</div>
</div>

<?php $form::end();
