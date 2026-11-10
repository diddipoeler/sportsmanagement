<?php
/**
 * Native Joomla 5/6 HTML view for combined match results and matrix.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Resultsmatrix;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatrixPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsmatrixModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

/**
 * Display a read-only results list and team matrix in the native Joomla MVC.
 *
 * Both presentations share the same parameter-bound matrix result set,
 * replacing the legacy combined view's global helpers and mixed template
 * paths. The parent project view provides Joomla request/menu context.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    /** @var array<int,object> Indexed project team records. */
    public array $teams = [];

    /** @var array<int,object> Selected-project match result records. */
    public array $results = [];

    /** @var array<int,object> Published divisions for an unfiltered project. */
    public array $divisions = [];

    /** @var array<int,array{home:string,away:string,score:string,round:string}> */
    public array $fixtures = [];

    public int $divisionid = 0;
    public int $roundid = 0;

    /**
     * Avoid loading legacy global model and presentation dependencies.
     *
     * Joomla's native MatrixModel and template/helper classes provide all
     * required data for this read-only combined page. Avoids deprecated
     * global Joomla factory calls from the historical resultsmatrix view.
     *
     * @return bool False: native dependencies are sufficient.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Render the combined results and matrix without a jQuery requirement.
     *
     * Both parts use static semantic HTML with no legacy tabs JavaScript.
     *
     * @return bool False because the native view uses HTML-only navigation.
     */
    protected function requiresJquery(): bool
    {
        return false;
    }

    /**
     * Avoid obsolete media lightboxes on the read-only combined page.
     *
     * @return bool False: no modal image markup is generated.
     */
    protected function requiresJceMediaBox(): bool
    {
        return false;
    }

    /**
     * Prepare project-owned combined results and matrix presentation data.
     *
     * Requires the namespaced ResultsmatrixModel, loads a single scoped
     * matrix dataset, and derives the result list from the same matches and
     * bounded team index. No old global view models are instantiated.
     * Missing projects yield an empty page instead of SQL access.
     *
     * @return void
     * @throws \RuntimeException If Joomla injected the wrong MVC model.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof ResultsmatrixModel) {
            throw new \RuntimeException('Combined results/matrix requires ResultsmatrixModel.', 500);
        }

        if ($this->project === null) {
            return;
        }

        $data = $model->getCombinedData();
        $this->teams = $data['teams'];
        $this->results = $data['results'];
        $this->divisions = $data['divisions'];
        $this->divisionid = $data['divisionid'];
        $this->roundid = $data['roundid'];
        $this->fixtures = MatrixPresentationHelper::fixtures(
            $this->results,
            MatrixPresentationHelper::teams($this->teams)
        );

        $projectName = $this->project->name ?? '';
        $this->getDocument()->setTitle(
            Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_MATRIX_LAYOUT_TITLE')
            . (is_string($projectName) && $projectName !== '' ? ': ' . $projectName : '')
        );
    }
}
