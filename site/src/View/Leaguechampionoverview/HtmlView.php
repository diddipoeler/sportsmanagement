<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Leaguechampionoverview;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\LeaguechampionoverviewModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $leaguechampions = [];
    public array $leaguechampions_detail = [];
    public array $teamseason = [];
    public array $leagueteamchampions = [];
    public array $projectids = [];
    public array $projectnames = [];
    public array $teamstotal = [];
    public $document;

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        $this->document = $this->getDocument();

        /** @var LeaguechampionoverviewModel $model */
        $model = $this->getModel();
        if (!$model instanceof LeaguechampionoverviewModel) {
            throw new \RuntimeException('Leaguechampionoverview view requires LeaguechampionoverviewModel.', 500);
        }

        if (!$this->project) {
            $this->project = $model->getOverviewContextProject();
        }

        $data = $model->getOverviewData();
        $this->projectids = $data['projectids'];
        $this->projectnames = $data['projectnames'];
        $this->leaguechampions = $data['leaguechampions'];
        $this->leaguechampions_detail = $data['leaguechampions_detail'];
        $this->teamseason = $data['teamseason'];
        $this->leagueteamchampions = $data['leagueteamchampions'];
        $this->teamstotal = $data['teamstotal'];
        $this->notes = array_merge($this->notes, $data['notes']);
        $this->tips = array_merge($this->tips, $data['tips']);
        $this->warnings = array_merge($this->warnings, $data['warnings']);

        $this->document->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.league-champion-overview',
            Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js',
            ['version' => 'auto']
        );

        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_LEAGUECHAMPIONOVERVIEW_PAGE_TITLE');
        $this->document->setTitle($this->headertitle);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
