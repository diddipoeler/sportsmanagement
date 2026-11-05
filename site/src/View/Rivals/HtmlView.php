<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Rivals view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Rivals;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ProjectTitleHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\RivalsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Document\HtmlDocument;
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

    /**
     * Disable the historical presentation bootstrap for the rivals view.
     *
     * The Joomla 5/6 implementation uses a namespaced model, project title
     * helper and registered web assets instead of legacy global helpers.
     *
     * @return bool False because legacy presentation is not required.
     */
    protected function usesLegacyPresentation(): bool
    {
        return false;
    }

    /**
     * Prepare the selected team's opponents and browser page title.
     *
     * Checks the native RivalsModel, loads division, team and opponent
     * records, builds the configured project title and registers the
     * component's JavaScript through Joomla's Web Asset Manager.
     *
     * @return void
     * @throws \RuntimeException If the native rivals model is missing.
     */
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

        $titleInfo = ProjectTitleHelper::createInfo(
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

        $this->pagetitle = ProjectTitleHelper::format(
            $titleInfo,
            (string) ($this->config['page_title_format'] ?? '')
        );
        if ($this->pagetitle === '') {
            $this->pagetitle = Text::_('COM_SPORTSMANAGEMENT_RIVALS_PAGE_TITLE');
        }

        $this->headertitle = $this->pagetitle;
        $this->document->setTitle($this->pagetitle);

        if ($this->document instanceof HtmlDocument) {
            $this->document->getWebAssetManager()->registerAndUseScript(
                'com_sportsmanagement.rivals',
                Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
                ['version' => 'auto']
            );
        }
    }

    /**
     * Render a team's club icon with the configured modal dimensions.
     *
     * Delegates image and link presentation to the native TeamLogoHelper
     * while retaining the legacy team image element identifier.
     *
     * @param object $team Team record providing the team identifier.
     * @param string $clubIcon Requested team logo variant or path.
     * @return string Rendered team icon markup.
     */
    public function renderClubIcon(object $team, string $clubIcon): string
    {
        $teamId = (int) ($team->team_id ?? $team->id ?? 0);

        return TeamLogoHelper::renderVariant(
            $team,
            $clubIcon,
            'team' . $teamId,
            20,
            $this->modalwidth,
            $this->modalheight,
            (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
        );
    }
}
