<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Rivals;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RivalsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Native Joomla 5/6 view for a team's project rivals.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?RivalsModel $model = null;
    public $document;
    public array $opos = [];
    public ?object $team = null;
    public string $pagetitle = '';

    protected function prepareView(): void
    {
        $this->document = $this->getDocument();

        $model = $this->getModel();
        if (!$model instanceof RivalsModel) {
            throw new \RuntimeException('Rivals view requires RivalsModel.', 500);
        }

        $this->model = $model;
        $this->division = $model->getDivision();
        $this->opos = $model->getOpponents();
        $this->team = $model->getTeam();

        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = '-';
        }
        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }

        $titleInfo = \sportsmanagementHelper::createTitleInfo(
            Text::_('COM_SPORTSMANAGEMENT_RIVALS_PAGE_TITLE')
        );

        if ($this->team) {
            $titleInfo->team1Name = (string) ($this->team->name ?? '');
        }
        if ($this->project) {
            $titleInfo->projectName = (string) ($this->project->name ?? '');
            $titleInfo->leagueName = (string) ($this->project->league_name ?? '');
            $titleInfo->seasonName = (string) ($this->project->season_name ?? '');
        }
        if ($this->division && (int) ($this->division->id ?? 0) > 0) {
            $titleInfo->divisionName = (string) ($this->division->name ?? '');
        }

        $this->pagetitle = (string) \sportsmanagementHelper::formatTitle(
            $titleInfo,
            (string) ($this->config['page_title_format'] ?? '')
        );
        if ($this->pagetitle === '') {
            $this->pagetitle = Text::_('COM_SPORTSMANAGEMENT_RIVALS_PAGE_TITLE');
        }

        $this->headertitle = $this->pagetitle;
        $this->document->setTitle($this->pagetitle);

        $this->document->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.rivals',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
            ['version' => 'auto']
        );
    }

    public function renderClubIcon(object $team, string $clubIcon): string
    {
        $image = (string) ($team->{$clubIcon} ?? '');

        if (!\sportsmanagementHelper::existPicture($image)) {
            $image = (string) \sportsmanagementHelper::getDefaultPlaceholder($clubIcon);
        }

        $teamId = (int) ($team->team_id ?? $team->id ?? 0);

        return (string) \sportsmanagementHelperHtml::getBootstrapModalImage(
            'team' . $teamId,
            $image,
            (string) ($team->name ?? ''),
            '20',
            '',
            $this->modalwidth,
            $this->modalheight,
            (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
        );
    }
}
