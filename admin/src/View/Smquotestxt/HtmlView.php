<?php
/**
 * Native Joomla 5/6 administrator list view for random-quote source files.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotestxt;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotestxtModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for random-quote source files. */
final class HtmlView extends BaseHtmlView
{
    public array $files = [];

    /**
     * Render validated quote-source text files in the administrator view.
     *
     * Requires the namespaced Joomla 5/6 source-list model, retrieves its
     * filtered filenames, checks model errors and adds the translated title
     * and back-navigation toolbar before rendering the selected layout.
     *
     * @param string|null $tpl Optional Joomla layout suffix.
     * @return void
     * @throws \RuntimeException If the expected list model is unavailable.
     */
    public function display($tpl = null): void
    {
        $model = $this->getModel();

        if (!$model instanceof SmquotestxtModel) {
            throw new \RuntimeException('Quote source list requires SmquotestxtModel.', 500);
        }

        $this->files = $model->getTXTFiles();

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS'), 'quote');
        ToolbarHelper::back(
            Text::_('JPREV'),
            Route::_('index.php?option=com_sportsmanagement&view=smquotes')
        );

        parent::display($tpl);
    }
}
