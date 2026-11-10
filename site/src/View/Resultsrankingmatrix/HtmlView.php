<?php
/**
 * Native Joomla 5/6 triple results, ranking and matrix HTML view.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Resultsrankingmatrix;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingmatrixModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

/**
 * Render three read-only panels with one native project-scoped dataset.
 *
 * Match results, calculated sport-specific standings and the cross-table
 * share the validated project, round and division. No legacy global view
 * bootstrap or untrusted JavaScript fragments are needed for presentation.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    /** @var array<int,array{home:string,away:string,score:string,round:string}> */
    public array $fixtures = [];

    /** @var array<int,array{division:int,rows:array}> */
    public array $standings = [];

    /** @var array<int,object> Native project-owned matrix team records. */
    public array $teams = [];

    /** @var array<int,object> Native project-owned matrix match records. */
    public array $results = [];

    /** @var array<int,object> Published division records belonging to this project. */
    public array $divisions = [];

    public int $roundid = 0;
    public int $divisionid = 0;
    public int $databaseSelector = 0;

    /**
     * Rely on namespaced Joomla views and the shared ranking calculation adapter.
     *
     * The calculation adapter manages any remaining sport-specific legacy
     * engine itself; old global presentation models are not required.
     *
     * @return bool False to skip legacy frontend presentation bootstrap.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Use accessible static HTML anchors instead of legacy tabbed JavaScript.
     *
     * @return bool False, because all three panels are server-side HTML.
     */
    protected function requiresJquery(): bool
    {
        return false;
    }

    /**
     * Avoid JCE media scripts for the read-only three-panel layout.
     *
     * @return bool False, because no popup images are rendered.
     */
    protected function requiresJceMediaBox(): bool
    {
        return false;
    }

    /**
     * Prepare the three displays from exactly one validated combined model.
     *
     * Verifies Joomla provided the corresponding namespaced model and that
     * the selected project is available before loading the ranking adapter
     * or any match records. Exposes the original scoped team/match objects
     * only for the existing HTML-escaping native matrix template.
     *
     * @return void
     * @throws \RuntimeException When Joomla injects a different MVC model.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof ResultsrankingmatrixModel) {
            throw new \RuntimeException('Combined results/ranking/matrix requires ResultsrankingmatrixModel.', 500);
        }

        if ($this->project === null) {
            return;
        }

        $data = $model->getTripleData($this->project, $this->config);
        $this->roundid = $data['roundid'];
        $this->divisionid = $data['divisionid'];
        $this->fixtures = $data['fixtures'];
        $this->standings = $data['standings'];
        $this->teams = $data['teams'];
        $this->results = $data['results'];
        $this->divisions = $data['divisions'];
        $this->databaseSelector = ResultsrankingmatrixModel::$cfg_which_database === 1 ? 1 : 0;

        $name = $this->project->name ?? null;
        $title = Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_RANKING_LAYOUT_TITLE')
            . ' / ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX');

        $this->getDocument()->setTitle(
            $title . (is_string($name) && $name !== '' ? ': ' . $name : '')
        );
    }
}
