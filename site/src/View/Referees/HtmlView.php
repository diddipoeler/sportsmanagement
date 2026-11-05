<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Referees view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Referees;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RefereesModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $rows = [];

    /**
     * Avoid the legacy presentation bootstrap for the native referee list.
     *
     * The namespaced Joomla 5/6 view and RefereesModel provide the
     * data required by the template without global helper dependencies.
     *
     * @return bool False to use only native presentation services.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Prepare referee rows and translated heading for the current project.
     *
     * Validates the native RefereesModel, loads the project referee list,
     * supplies the default show_referees option and derives the document
     * title from the current project when one is selected.
     *
     * @return void
     * @throws \RuntimeException If the referee model is unavailable.
     */
    protected function prepareView(): void
    {
        /** @var RefereesModel $model */
        $model = $this->getModel();

        if (!$model instanceof RefereesModel) {
            throw new \RuntimeException('Referees view requires RefereesModel.', 500);
        }
        $this->rows = $model->getReferees();
        $this->config['show_referees'] ??= '1';

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_PAGE_TITLE');
        $title = $this->project ? Text::sprintf($pageTitle, $this->project->name) : $pageTitle;
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_TITLE');
        $this->getDocument()->setTitle($title);
    }
}
