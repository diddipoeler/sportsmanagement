<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Referees;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RefereesModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $rows = [];

    protected function prepareView(): void
    {
        /** @var RefereesModel $model */
        $model = $this->getModel();
        $this->rows = $model->getReferees();
        $this->config['show_referees'] ??= '1';

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_PAGE_TITLE');
        $title = $this->project ? Text::sprintf($pageTitle, $this->project->name) : $pageTitle;
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_TITLE');
        $this->getDocument()->setTitle($title);
    }
}
