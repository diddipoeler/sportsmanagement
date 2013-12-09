<?php defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');

$model = $this->getModel('jlxmlimport');
echo $model->getXml;
?>