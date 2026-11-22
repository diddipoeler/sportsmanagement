<?php
/**
 * Native Joomla 5/6 administrator view for SportsManagement special extensions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
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

    /**
     * Load and display installed SportsManagement special extensions.
     *
     * Require the native extensions model, map older Joomla 3/4/5 layout
     * names onto the current default layout, and expose the extension list
     * to its template after setting the translated administrator title.
     *
     * @param string|null $tpl Optional extension layout suffix.
     * @return void The administrator extension list is rendered.
     * @throws \RuntimeException If the expected MVC model is missing.
     */
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
