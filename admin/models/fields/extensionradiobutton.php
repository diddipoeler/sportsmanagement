<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version 1.0.58
* @file 
* @author diddipoeler, stony, svdoldie (diddipoeler@gmx.de)
* @copyright Copyright: 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of SportsManagement.
* 
* https://docs.joomla.org/Creating_a_custom_form_field_type
* https://hotexamples.com/examples/-/JFormFieldRadio/-/php-jformfieldradio-class-examples.html
 */

// no direct access
defined('_JEXEC') or die ;

/**
* welche joomla version ?
*/
if( version_compare(substr(JVERSION,0,1),'4','eq') ) 
{
jimport('joomla.form.formfield');    
class JSMFormField extends JFormFieldRadio {}	   
}
else
{
require_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';    
class JSMFormField extends JFormFieldRadio {}        
}    

/**
 * JFormFieldExtensionRadioButton
 * 
 * @package 
 * @author Dieter Pl�ger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JFormFieldExtensionRadioButton extends JSMFormField {
		
	public $type = 'ExtensionRadioButton';

	/**
	 * JFormFieldExtensionRadioButton::getLabel()
	 * 
	 * @return void
	 */
	protected function getLabel() 
	{

    return parent::getLabel();
	}


	/**
	 * JFormFieldExtensionRadioButton::getInput()
	 * 
	 * @return void
	 */
	protected function getInput() 
	{
	/**
     * welche joomla version ?
     */
    if( version_compare(substr(JVERSION,0,1),'4','eq') ) 
    {
	$this->class = "switcher";   
    }
    else
    {
    $this->class = "radio btn-group btn-group-yesno";    
    }	
		

	return parent::getInput();
	}
    
    

}
?>
