<?php
/**
 * Native Joomla 5/6 administrator listheader dispatcher.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

$this->jsmstartzeit = microtime(true);

// Joomla 5 and 6 share the modern administrator presentation path.
echo $this->loadTemplate('joomla4');

if (!empty($this->items)) {
    switch ((string) $this->view) {
        case 'projectteams':
        case 'templates':
        case 'treetos':
            break;

        case 'projects':
            if ((string) ($this->return ?? '') === 'jlextdfbkeyimporterror6') {
                $countryCode = $this->jinput->getCmd('dfbcountry');
                $databaseSelector = $this->jinput->getInt('cfg_which_database', 0);
                $database = (new SportsManagementDatabaseResolver())->resolve($databaseSelector);
                $countryFlag = CountryOptionsHelper::getFlag($database, $countryCode);
                $this->tips[] = Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_6',
                    $this->jinput->getCmd('dfbteams'),
                    $countryFlag,
                    $countryCode
                );
            }

            echo $this->loadTemplate('jsm_notes');
            echo $this->loadTemplate('jsm_tips');
            echo $this->loadTemplate('data');
            break;

        default:
            echo $this->loadTemplate('data');
            break;
    }

    return;
}

switch ((string) $this->view) {
    case 'updates':
    case 'databasetools':
    case 'templates':
        echo $this->loadTemplate('data');
        break;

    case 'githubinstall':
        break;

    default:
        $this->warnings[] = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_MATCHING_RESULTS');
        echo $this->loadTemplate('jsm_warnings');
        break;
}

if ((bool) ComponentHelper::getParams((string) $this->option)->get('show_jsm_tips')) {
    switch ((string) $this->view) {
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
