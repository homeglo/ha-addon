<?php

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;

$this->title = 'View Scene';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>'; ?>



</div>
