<?php


defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controlleradmin');

class sportsmanagementControllerjsmGCalendars extends JControllerAdmin {

	public function getModel($name = 'jsmGCalendar', $prefix = 'sportsmanagementModel') {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}