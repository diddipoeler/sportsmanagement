<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.00
 * @file      flagsfolder.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage mod_sportsmanagement_matchesslider
 */ 


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

/**
 * JFormFieldFlagsFolder
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JFormFieldFlagsFolder extends FormField
{
	protected $type = 'FlagsFolder';

	/**
	 * JFormFieldFlagsFolder::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		$folderlist = array();
		$folderlist1 = Folder::folders(JPATH_ROOT.DIRECTORY_SEPARATOR.'images', '', true, true, array(0 => 'system'));
	    $folderlist2 = Folder::folders(JPATH_ROOT.DIRECTORY_SEPARATOR.'media' , '', true, true, array(0 => 'system'));
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
 