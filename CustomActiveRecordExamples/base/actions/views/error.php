<?php
    use yii\helpers\Html;
    use yii\helpers\Url;
    $this->H1    = Yii::t('yii', 'Error {code}' , ['code' => $statusCode ]);
    $this->title =  $this->H1  .' | '. Yii::$app->name;
?>
<br>
<br>
<br>
<div class="container-fluid mv-30">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="row margin-v-30 text-center ">
            <h1 class="text-danger" style="font-size: 4.5vw ">
            <?php echo Yii::t('yii', 'Error'); ?>
            <?php echo $statusCode; ?>
        </h1>
            <small style="font-size: 16px"><?php echo nl2br($message) ?></small>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>


