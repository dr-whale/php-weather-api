<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Test';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        This is the Sparta!
    </p>

    <code><?= __FILE__ ?></code>
</div>
