<?php
$extends_str = trim($generator->baseClass, '\\');
$last = strripos($extends_str,'\\');
$str_length = strlen($extends_str);
$extends_use = substr($extends_str,0,$last);
$extends = substr($extends_str,-($str_length-$last-1));
// echo $a;
?>
<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
?>
<?php

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

use work\modules\rbac\models\<?= substr(StringHelper::basename($generator->controllerClass),0,-10)?>;
use <?= $extends_str?>;

class <?= StringHelper::basename($generator->controllerClass) ?> extends <?= $extends. "\n" ?>
{
<?php foreach ($generator->getActionIDs() as $action): ?>

    /**@type  \work\modules\rbac\models\<?= substr(StringHelper::basename($generator->controllerClass),0,-10)?> $<?=strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', substr(StringHelper::basename($generator->controllerClass),0,-10)))?>_model */
    public $<?=strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', substr(StringHelper::basename($generator->controllerClass),0,-10)))?>_model;

    public function init()
    {
        parent::init();
        $this-><?= strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_',substr(StringHelper::basename($generator->controllerClass),0,-10)))?>_model = new <?= substr(StringHelper::basename($generator->controllerClass),0,-10)?>();
    }

    public function actionIndex()
    {
    }

    public function actionCreate()
    {
    }

    public function actionUpdate()
    {
    }

    public function actionDelete()
    {
    }

<?php endforeach; ?>
}
