<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
	use app\widgets\activeForm\ActiveForm;

	$route = '/' . $this->context->currentController . $this->context->action->id;

	$form = ActiveForm::begin([
		'registerDefaultJs' => true,
		'action'            => Url::to([$route, 'id'=> $model->id ])
	]);

	$btnId = uniqid('btn_');
	$this->registerJs(
		'
			(function(){
				$("#'.$btnId.'").on("click", function(e){
					e.preventDefault();
					var nodeDv = $(this).closest("[data-runtime=dv-content]");
					if(nodeDv.length != 0)
					{
						dv = dinamicView.getInstance(nodeDv.data("dinamic-view"));
						dv.close();
					}

					return false;
				});
			})();
		',
		static::POS_END,
		'cancel-delete'
	);
?>
<div class="container-fluid mb-5">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-12">
					<div class="title-labels-form">
						<h2><?= Yii::t('app', 'Confirmar acción') ?></h2>
						<hr>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--actions-->
	<div class="row">
		<div class="col-md-12">
			<?php
				if($model->hasErrors() == false)
				{
					$msn = Yii::t('app', '¿Esta seguro que desea eliminar este registro? <br> Una vez completada esta acción no podrá recuperar dicho registro.');
					echo Html::tag('p',  $msn , ['class'=>'text-justify text-danger']);
					echo Html::tag(
						'div',
						Html::button('Cancelar', ['id' => $btnId, 'class' => 'btn btn-primary ', 'style' => 'margin:15px 0px; margin-right:10px ']) .
						Html::submitButton('Aceptar', ['class' => 'btn btn-danger  ', 'style' => 'margin:15px 0px; ']) ,
						['class' => 'mv-15 ']
					);
				}
				else
					echo Html::tag('p',  $model->getFirstError('confirmDelete') , 'text-justify text-danger');
			?>
		</div>
	</div>

	<!--view model-->
	<div class="row">
		<div class="col-md-12">
		<?php
			try{
				echo $this->render('@controllerViewPath/' . $this->context->action->viewFile, [
					'model' => $modelView
				]);
			}
			catch(\Exception $e){
			}
		?>
		</div>
	</div>

</div>
<?php $form::end(); ?>
