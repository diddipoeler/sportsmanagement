<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Billardlivegames;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 frontend view for the billiards live-games layout. */
final class HtmlView extends SportsManagementHtmlView
{
    public function display($tpl = null)
    {
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_BILLARDLIVEGAMES_PAGE_TITLE'));
        parent::display($tpl);
    }
}
