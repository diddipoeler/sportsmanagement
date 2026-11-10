<?php
/**
 * Native Joomla 5/6 combined results and ranking frontend HTML view.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Resultsranking;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

/**
 * Display native fixture data and calculated league standings on one page.
 *
 * The sport-specific table is calculated by the existing ranking adapter;
 * results are queried from the same project and validated matchday through
 * the native matrix model. No legacy global view/template is executed.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    /** @var array<int,array{home:string,away:string,score:string,round:string}> */
    public array $fixtures = [];

    /** @var array<int,array{division:int,rows:array}> */
    public array $standings = [];

    public int $roundid = 0;
    public int $divisionid = 0;

    /**
     * Do not bootstrap legacy presentation globals for the native layout.
     *
     * The dedicated ranking adapter independently manages its remaining
     * historical sport-specific calculation helper.
     *
     * @return bool False; this view has native presentation dependencies.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Use accessible static HTML section links rather than legacy jQuery tabs.
     *
     * @return bool False because the combined layout needs no jQuery scripts.
     */
    protected function requiresJquery(): bool
    {
        return false;
    }

    /**
     * Omit historical media lightbox scripts from the combined read-only page.
     *
     * @return bool False because the native tables contain no modal images.
     */
    protected function requiresJceMediaBox(): bool
    {
        return false;
    }

    /**
     * Prepare results and rankings with shared project and matchday context.
     *
     * Requires an injected native ResultsrankingModel and rejects missing
     * projects before invoking queries. The model validates its project and
     * round IDs, delegates league rules to RankingCalculationAdapter and
     * supplies already-normalized text-only rows to the HTML template.
     *
     * @return void
     * @throws \RuntimeException When Joomla provides an incompatible model.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof ResultsrankingModel) {
            throw new \RuntimeException('Combined results/ranking requires ResultsrankingModel.', 500);
        }

        if ($this->project === null) {
            return;
        }

        $data = $model->getCombinedData($this->project, $this->config);
        $this->fixtures = $data['fixtures'];
        $this->standings = $data['standings'];
        $this->roundid = $data['roundid'];
        $this->divisionid = $data['divisionid'];

        $name = $this->project->name ?? null;
        $this->getDocument()->setTitle(
            Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_RANKING_LAYOUT_TITLE')
            . (is_string($name) && $name !== '' ? ': ' . $name : '')
        );
    }
}
