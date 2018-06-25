<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.58
* @file 
* @author diddipoeler, stony, svdoldie (diddipoeler@gmx.de)
* @copyright Copyright: 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of SportsManagement.
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

/**
 * JFormFieldExtensionAuthor
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JFormFieldExtensionAuthor extends JFormField {
		
	public $type = 'ExtensionAuthor';
	
	/**
	 * JFormFieldExtensionAuthor::getLabel()
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
		$html .= '<div style="clear: both;">'.JText::_('COM_SPORTSMANAGEMENT_AUTHOR_LABEL').'</div>';
		return $html;
	}

	/**
	 * JFormFieldExtensionAuthor::getInput()
	 * 
	 * @return
	 */
	protected function getInput() 
	{
		$html = '<div style="padding-top: 5px; overflow: inherit">';
		$html .= 'Dieter Pl&ouml;ger @ <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>';
		$html .= '</div>';
		return $html;
	}

}
?>
