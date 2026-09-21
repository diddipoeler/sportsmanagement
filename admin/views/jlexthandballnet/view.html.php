<?php
/**
 * Legacy Joomla 5/6 handball.net administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** SportsManagement handball.net administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlexthandballnet extends sportsmanagementView
{
    public function init()
    {
    }

    protected function addToolbar()
    {
        if ($this->document instanceof HtmlDocument) {
            $this->document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.jlexthandballnet',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
        parent::addToolbar();
    }
}
