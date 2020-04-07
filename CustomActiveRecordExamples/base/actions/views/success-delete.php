<?php
	use yii\helpers\Html;

	$btnId = uniqid('btn_');
	echo Html::script('
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

	');
?>
<br>
<br>
<br>
<br>
<br>
<br>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3"></div>
		<div class="col-md-6" style="color:#6F0769">
				<div class="text-center mv-15">
					<br>
					<i class="fa fa-check-circle" style="font-size:120px"></i>
					<h3>
						<?= Yii::t('app', 'Acción completada exitosamente!'); ?>
					</h3>
					<br>
					<?= Html::button('Aceptar', ['id' => $btnId, 'class' => 'btn btn-lg ', 'style' => 'background:#6F0769; color:#FFF; ']) ?>
				</div>
			</div>
		</div>
	</div>
</div>
