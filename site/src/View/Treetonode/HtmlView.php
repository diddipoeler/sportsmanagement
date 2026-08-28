<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Treetonode;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ProjectTitleHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\TreetonodeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 tree-to-node frontend view. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $node = [];
    public array $roundname = [];
    public string $pagetitle = '';

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof TreetonodeModel) {
            throw new \RuntimeException('Treetonode view requires TreetonodeModel.', 500);
        }

        $nodes = $model->getTreetonode();
        $this->node = is_array($nodes) ? $nodes : [];
        $this->roundname = $model->getRoundName();
        $this->division = $model->getDivision();

        $titleInfo = ProjectTitleHelper::createInfo(Text::_('COM_SPORTSMANAGEMENT_TREETO_PAGE_TITLE'));
        if ($this->project) {
            $titleInfo->projectName = (string) ($this->project->name ?? '');
            $titleInfo->leagueName = (string) ($this->project->league_name ?? '');
            $titleInfo->seasonName = (string) ($this->project->season_name ?? '');
        }
        if (is_object($this->division) && (int) ($this->division->id ?? 0) > 0) {
            $titleInfo->divisionName = (string) ($this->division->name ?? '');
        }

        $this->pagetitle = ProjectTitleHelper::format(
            $titleInfo,
            (string) ($this->config['page_title_format'] ?? 0)
        );
        $this->headertitle = $this->pagetitle;
        $this->getDocument()->setTitle($this->pagetitle);
    }
}
