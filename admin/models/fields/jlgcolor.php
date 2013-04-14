<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Color Form Field class for the joomleague
 */
class JFormFieldJLGColor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	protected $type = 'JLGColor';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	protected function getInput()
	{
		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/301a.js');
		
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$classes = (string) $this->element['class'];
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$class = $classes ? ' class="' . trim($classes) . '"' : '';
		
		$document->addScriptDeclaration("
				window.addEvent('domready', function() {
					$$('.pickerbutton').addEvent('click', function(){
						var field = this.id.substr(12);
						showColorGrid3(field,'sample_'+field);
						return overlib(document.id('colorpicker301').innerHTML, 
						               CAPTION,'Farbe:', BELOW, RIGHT,FOLLOWMOUSE=false, 
						               CLOSEBTN=true, CLOSEBTNCOLORS=['white', 'white', 'white', 'white'], 
				                   STICKY=false);
					});
					document.id('sample_".$this->id."').style.backgroundColor = document.id('".$this->id."').value;
				});
		");
		
		$html = array();		
		$html[] = '<input class="inputbox" type="text" id="sample_'.$this->id.'" size="1" value="">&nbsp;';		
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . '/>';
		$html[] = '<input	type="button" id="buttonpicker'.$this->id.'" class="inputbox pickerbutton" value="..."/>';
		$html[] = '<div	id="colorpicker301" class="colorpicker301" style="position:absolute;top:0px;left:0px;z-index:1000;display:none;"></div>';

		return implode("\n",$html);
	}
}
