<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $clubs = [];

    protected function prepareView(): void
    {
        /** @var ClubsModel $model */
        $model = $this->getModel();
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
