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
	//$app->enqueueMessage(__METHOD__.' '.__LINE__.' element <pre>'.print_r($this->element, true).'</pre><br>','');
	//$app->enqueueMessage(__METHOD__.' '.__LINE__.' name<pre>'.print_r($this->name, true).'</pre><br>','');
	//$app->enqueueMessage(__METHOD__.' '.__LINE__.' id<pre>'.print_r($this->id, true).'</pre><br>','');
	//$app->enqueueMessage(__METHOD__.' '.__LINE__.' value<pre>'.print_r($this->value, true).'</pre><br>','');
	//$app->enqueueMessage(__METHOD__.' '.__LINE__.' value<pre>'.print_r($this->form, true).'</pre><br>','');
	if ( !$this->value )
	{
	$this->value = sportsmanagementHelper::jsmsernum();
	}
/*
	$html = '<div style="padding-top: 5px; overflow: inherit">';
		$html .= '<span class="label">'.$version.'</span>';
		$html .= '</div>';
*/
$html = '<input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';		
		return $html;
	
	}

	

}
?>
