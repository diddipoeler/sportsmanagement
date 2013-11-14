<?php defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');JHTML::_('behavior.modal');

$model = $this->getModel('jlxmlimport');
echo $model->getXml;
?>