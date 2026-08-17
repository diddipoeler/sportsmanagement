<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\About;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementHtmlView
{
    public object $about;

    public function display($tpl = null)
    {
        $this->about = $this->getModel()->getAbout();
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ABOUT_PAGE_TITLE'));
        parent::display($tpl);
    }
}
