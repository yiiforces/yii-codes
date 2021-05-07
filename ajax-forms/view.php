<div>
	<?php $form  = ActiveForm::begin(['id' => 'my-form']); ?>
	<?= $form->field($model, 'attribute1')->textInput(); ?>
	<?= $form->field($model, 'attribute2')->textInput(); ?>
	<?= $form->field($model, 'attribute3')->textInput(); ?>
	....
	<?= $form->field($model, 'attribute-n+1')->textInput(); ?>
	<?php $form::end(); ?>
</div>

<?php
	$this->registerJs('(function(){

		const form = $("#my-form"); // register event in my activeForm->id
			  form.on("beforeSubmit",sendAjax);

		const ajaxParams = {
			cache : false,
			type  : form.attr("method"),
			data  : form.serialize(),
			url   : form.attr("action"),
		};

		var xhr = null;

		return false;

		function sendAjax(){
			// ajax send in process. prevent duplicate POST
			if(xhr != null) return;

			xhr = $.ajax(ajaxParams);
			xhr.always(function(){
				var code         = xhr.status;
				var responseText = xhr.responseText;
				var responseJSON = xhr.responseJSON;
				xhr = null; // ajax request end, unhold form

				switch(code)
				{
					case 0:
						window.location.reload(); // not network connection
						break;

					// create or update success
					case 200:
					case 201:
						alert(responseJSON.message);
						return;

					// error validation.  dimamic update errors mesages in input labels
					case 422:
						form.yiiActiveForm("updateMessages", responseJSON.errors || []);
						alert(responseJSON.message);
						return;

					// error save db:
					case 500:
						alert(responseJSON.message);
						return;

					default:
						console.error(code);
						console.error(responseText);
						return;
				}
			});
		}

	})();', static::POS_END, md5(__FILE__) ); // md5(__FILE__) is hash unique on your system


