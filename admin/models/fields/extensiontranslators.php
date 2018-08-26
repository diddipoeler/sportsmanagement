<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.58
* @file 
* @author diddipoeler, stony, svdoldie (diddipoeler@gmx.de)
* @copyright Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of SportsManagement.
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

/**
 * FormFieldExtensionTranslators
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class FormFieldExtensionTranslators extends FormField {
		
	public $type = 'ExtensionTranslators';
	
	/**
	 * FormFieldExtensionTranslators::getLabel()
	 * 
	 * @return
	 */
	protected function getLabel() 
	{		
		$lang = JFactory::getLanguage();
        $extension = 'com_sportsmanagement';
        $base_dir = JPATH_ADMINISTRATOR;
        $language_tag = $lang->getTag();
        $reload = true;
        $lang->load($extension, $base_dir, $language_tag, $reload);
		
		$html = '';
		
		$html .= '<div style="clear: both;">';
		if (!empty($this->translators)) {
			$html .= JText::_('COM_SPORTSMANAGEMENT_TRANSLATORS_LABEL');
		}
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * FormFieldExtensionTranslators::getInput()
	 * 
	 * @return
	 */
	protected function getInput() 
	{		
		$html = '';
		
		if (!empty($this->translators)) {
			$html .= '<div style="padding-top: 5px; overflow: inherit">';
			$html .= JText::_($this->translators);
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * FormFieldExtensionTranslators::setup()
	 * 
	 * @param mixed $element
	 * @param mixed $value
	 * @param mixed $group
	 * @return
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
	
		if ($return) {
			$this->translators = isset($this->element['translators']) ? JText::_($this->element['translators']) : NULL;
		}
	
		return $return;
	}

}
?>
