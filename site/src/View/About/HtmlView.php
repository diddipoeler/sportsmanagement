<?php
/**
 * Native Joomla 5/6 frontend SportsManagement About view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\About;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AboutModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementHtmlView
{
    public object $about;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AboutModel) {
            throw new \RuntimeException('About view requires AboutModel.', 500);
        }

        $this->about = $model->getAbout();
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ABOUT_PAGE_TITLE'));
        parent::display($tpl);
    }
}
