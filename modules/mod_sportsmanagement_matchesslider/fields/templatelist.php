<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.00
 * @file      templatelist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage mod_sportsmanagement_matchesslider
 */ 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper; 
 
jimport('joomla.form.formfield');
 
defined('JPATH_BASE') or die();

/**
 * JFormFieldTemplatelist
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JFormFieldTemplatelist extends JFormField
{
	protected $type = 'Templatelist';
	
	/**
	 * JFormFieldTemplatelist::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		jimport( 'joomla.filesystem.folder' );
		
		// path to images directory
		$path		= JPATH_ROOT.DS.$this->element['directory'];
		$filter		= $this->element['filter'];
		$exclude	= $this->element['exclude'];
		$folders	= JFolder::folders($path, $filter);
		
		$options = array ();
		foreach ($folders as $folder)
		{
			if ($exclude)
			{
				if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $folder )) {
					continue;
				}
			}
			$options[] = HTMLHelper::_('select.option', $folder, $folder);
		}
		
		$lang = JFactory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);
		if (!$this->element['hide_none'])
		{
			array_unshift($options, HTMLHelper::_('select.option', '-1', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE')));
		}
		
		if (!$this->element['hide_default'])
		{
			array_unshift($options, HTMLHelper::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_USE_DEFAULT')));
		}
		
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('
			function getPosition(element)
			{
				var pos = { y: 0, x: 0 };
		
				if(element)
				{
					var elem=element;
					while(elem && elem.tagName.toUpperCase() != \'BODY\')
					{
						pos.y += elem.offsetTop;
						pos.x += elem.offsetLeft;
						elem = elem.offsetParent;
					}
				}
				return pos;
			}
		
			function scrollToPosition(elementId)
			{
				var a,element,dynPos;
				element = $(elementId);
				a = getPosition(element);
				dynPos = a.y;
				window.scroll(a.x,dynPos);
		
			}
			');
		
		$mainframe = JFactory::getApplication();

		$select = '<table>'
				. '<tr>'
				. '<td>'
				. HTMLHelper::_('select.genericlist',  $options, $this->name,
						   'class="inputbox" onchange="$(\'TemplateImage\').src=\''
				           .$mainframe->getCfg('live_site')
						   .'/modules/mod_sportsmanagement_matches/tmpl/\'+this.options[this.selectedIndex].value+\'/template.png\';"', 
						   'value', 'text', $this->value, $this->id)
				. '<br /><br />'
				. JText::_($this->element['details'])
				. '</td>'
				. '</tr>'
				. '<tr>'
				. '<td style="text-align:right;background-color:grey;padding:4px;margin:20px;width:200px;height:150px;">'
				. HTMLHelper::_('image','modules/mod_sportsmanagement_matches/tmpl/'.$this->value.'/template.png', 
						   'TemplateImage', 'id="TemplateImage" width="200"')
			    . '</td>'
			    . '</tr>'
		        . '</table>';

		return $select;
	}
}
 