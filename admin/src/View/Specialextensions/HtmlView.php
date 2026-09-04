<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Specialextensions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SpecialextensionsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for SportsManagement special extensions. */
final class HtmlView extends BaseHtmlView
{
    public array $extensions = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof SpecialextensionsModel) {
            throw new \RuntimeException('SpecialextensionsModel could not be loaded.', 500);
        }

        if (in_array($this->getLayout(), ['default_3', 'default_4', 'default_5'], true)) {
            $this->setLayout('default');
        }

        $this->extensions = $model->getSpecialExtensions();
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS'), 'puzzle');

        parent::display($tpl);
    }
}
