<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_joomla_version.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

if (version_compare(JSM_JVERSION, '4', 'eq'))
{
	echo $this->loadTemplate('joomla4');
	$no_items = '';
}
elseif (version_compare(JSM_JVERSION, '3', 'eq'))
{
	echo $this->loadTemplate('joomla3');
	$no_items = '';
}

if ($this->items)
{
    switch ($this->view)
    {
    case 'projectteams':
    case 'templates':
    case 'treetos':
    break;
    case 'jlextdfbkeyimport':
    switch ($this->layout)
    {
    case 'default_createdays';
    $this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3');
    $this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4');
    echo $this->loadTemplate('jsm_notes');
	echo $this->loadTemplate('jsm_tips');
    break;    
    }
    break;
    case 'projects':
    switch ($this->return)
    {
    case 'jlextdfbkeyimporterror6':
    $this->tips[] = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_6', $this->jinput->getCmd('dfbteams'), JSMCountries::getCountryFlag($this->jinput->getCmd('dfbcountry')), $this->jinput->getCmd('dfbcountry'));
    break;
    
    
    }
    echo $this->loadTemplate('jsm_notes');
	echo $this->loadTemplate('jsm_tips');
    echo $this->loadTemplate('data');
    break;
    default:    
	echo $this->loadTemplate('data');
    break;
    }
}
else
{
    switch ($this->view)
    {
        case 'updates':
        case 'databasetools':
        echo $this->loadTemplate('data');
        break;
		case 'githubinstall':    
		    case 'templates':  
		    break;
        default:
        echo '<div class="' . $no_items . '">';
	$this->warnings[] = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_MATCHING_RESULTS');	    
    echo $this->loadTemplate('jsm_warnings');
    echo '</div>';
        break;
    }
	
	if (ComponentHelper::getParams($this->option)->get('show_jsm_tips'))
		{
	switch ($this->view)
    {
        case 'projectreferees':
	$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NO_REFEREES');	    
        break;
	case 'rounds':
	$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NO_ROUNDS');	    
        break;
	case 'divisions':
	$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NO_GROUPS');	    
        break;
			
	}
}
	echo $this->loadTemplate('jsm_notes');
	echo $this->loadTemplate('jsm_tips');
	
}

