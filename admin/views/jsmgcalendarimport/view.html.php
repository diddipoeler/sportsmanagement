<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** SportsManagement administrator Google calendar import view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjsmgcalendarImport extends sportsmanagementView
{
    public function init($tpl = null)
    {
        $this->setLayout('login');
    }

    protected function addToolbar()
    {
        ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
        parent::addToolbar();
    }
}
