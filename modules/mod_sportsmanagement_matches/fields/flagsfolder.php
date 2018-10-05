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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.form.formfield');
jimport( 'joomla.filesystem.folder' );

class JFormFieldFlagsFolder extends JFormField
{
	protected $type = 'FlagsFolder';

	function getInput()
	{
		$folderlist = array();
		$folderlist1 = JFolder::folders(JPATH_ROOT.DS.'images', '', true, true, array(0 => 'system'));
	    $folderlist2 = JFolder::folders(JPATH_ROOT.DS.'media' , '', true, true, array(0 => 'system'));
	    foreach ($folderlist1 AS $key => $val)
	    {
	    	$folderlist[] = str_replace(JPATH_ROOT.DS, '', $val);
	    }
	    foreach ($folderlist2 AS $key => $val)
	    {
	    	$folderlist[] = str_replace(JPATH_ROOT.DS, '', $val);
	    }

		$lang = Factory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);
		$items = array(HTMLHelper::_('select.option',  '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE')));

		foreach ( $folderlist as $folder )
		{
			$items[] = HTMLHelper::_('select.option',  $folder, '&nbsp;'.$folder );
		}

		$output= HTMLHelper::_('select.genericlist',  $items, $this->name,
						  'class="inputbox"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
 