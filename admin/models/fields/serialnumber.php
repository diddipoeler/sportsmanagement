<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      serialnumber.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */
 
defined('_JEXEC') or die ;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\FormField;

$classpath = JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. JSM_PATH .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");

/**
 * JFormFieldserialnumber
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class JFormFieldserialnumber extends FormField {
		
	public $type = 'serialnumber';

	/**
	 * JFormFieldserialnumber::getInput()
	 * 
	 * @return
	 */
	protected function getInput() {
	$app = Factory::getApplication();
	if ( !$this->value )
	{
	$this->value = sportsmanagementHelper::jsmsernum();
	}

$html = '<input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';		
		return $html;
	
	}

	

}
?>
