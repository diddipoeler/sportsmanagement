<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Clubs view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $clubs = [];

    /**
     * Use only namespaced Joomla 5/6 presentation services for the club list.
     * The native model and template supply the data without global helper loading.
     *
     * @return bool False because legacy presentation dependencies are unnecessary.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Load and prepare the published clubs of the active project and division.
     * Verifies the native model, reads its division and club records, and updates
     * the translated page heading and Joomla document title.
     *
     * @return void
     * @throws \RuntimeException When the MVC model is not a ClubsModel.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof ClubsModel) {
            throw new \RuntimeException('Clubs view requires ClubsModel.', 500);
        }

        $this->division = $model->getDivision();
        $this->clubs = $model->getClubs();

        $title = Text::_('COM_SPORTSMANAGEMENT_CLUBS_PAGE_TITLE');
        if ($this->project) {
            $title .= ' - ' . $this->project->name;
            if ($this->division) {
                $title .= ' : ' . $this->division->name;
            }
        }
        $this->headertitle = $title;
        $this->getDocument()->setTitle($title);
    }
}
