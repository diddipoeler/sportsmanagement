<?php
/**
* @version		$Id: templatelist.php 470 2010-01-31 19:38:29Z And_One $
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
 
jimport('joomla.form.formfield');
 
defined('JPATH_BASE') or die();

class JFormFieldTemplatelist extends JFormField
{
	protected $type = 'Templatelist';
	
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
			array_unshift($options, HTMLHelper::_('select.option', '-1', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE')));
		}
		
		if (!$this->element['hide_default'])
		{
			array_unshift($options, HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_USE_DEFAULT')));
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
				. Text::_($this->element['details'])
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
 