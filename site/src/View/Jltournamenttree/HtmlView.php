<?php
/**
 * Native Joomla 5/6 SportsManagement tournament tree HTML view.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Jltournamenttree;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\TournamentTreePayload;
use Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;

/**
 * Render the historical tournament-tree URL through Joomla's native MVC.
 *
 * The old global view and script-concatenating template remain on disk for
 * existing installations, but requests to view=jltournamenttree now reach
 * this namespaced view with a JSON-safe read-only bracket presentation.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    /** @var array{teams:array,results:array,rounds:array} JSON-safe tournament bracket data. */
    public array $treePayload = ['teams' => [], 'results' => [], 'rounds' => []];
    public string $projectName = '';
    public string $colorFrom = '#FFFFFF';
    public string $colorTo = '#0000FF';
    public int $roundWidth = 300;
    public int $fontSize = 14;

    /**
     * Prepare the tournament's native bracket data and register its assets.
     *
     * Resolves Joomla's namespaced JltournamenttreeModel, verifies the
     * selected project and supported tournament modes, loads the existing
     * bracket relations and converts the model's data objects to bounded
     * arrays. Never forwards model-generated JavaScript snippets into the
     * template. Invalid legacy colors and sizes get harmless CSS defaults.
     * Registers the bundled jQuery Bracket library and the read-only site
     * renderer through the Joomla Web Asset Manager.
     *
     * @return void
     * @throws \RuntimeException When the expected native model is missing.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof JltournamenttreeModel) {
            throw new \RuntimeException('Tournament tree requires JltournamenttreeModel.', 500);
        }

        $project = $this->project;

        if ($project === null
            || !in_array((string) ($project->project_type ?? ''), ['TOURNAMENT_MODE', 'DIVISIONS_LEAGUE'], true)
            || $model->getProjectId() <= 0) {
            return;
        }

        $name = $project->name ?? '';
        $this->projectName = is_string($name) ? $name : '';
        $this->colorFrom = self::safeColor($model->getColorFrom(), '#FFFFFF');
        $this->colorTo = self::safeColor($model->getColorTo(), '#0000FF');
        $this->roundWidth = min(800, max(150, $model->getTreeBracketRoundWidth()));
        $this->fontSize = min(28, max(10, $model->getFontSize()));

        $rounds = $model->getTournamentRounds();

        if (!is_array($rounds) || $rounds === []) {
            return;
        }

        // The existing database model builds bracket positions, but its
        // legacy JavaScript string return value is intentionally ignored.
        $model->getTournamentMatches($rounds);
        $this->treePayload = TournamentTreePayload::build($rounds, $model->bracket);

        if ($this->treePayload['teams'] === []) {
            return;
        }

        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $assets = $document->getWebAssetManager();
        $assets->useScript('jquery');
        $assets->registerAndUseScript(
            'com_sportsmanagement.tournament-bracket',
            'components/com_sportsmanagement/assets/js/jquery.bracket.min.js',
            ['version' => 'auto'],
            [],
            ['jquery']
        );
        $assets->registerAndUseStyle(
            'com_sportsmanagement.tournament-bracket',
            'components/com_sportsmanagement/assets/css/jquery.bracket.min.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseStyle(
            'com_sportsmanagement.native-tournament-tree',
            'components/com_sportsmanagement/assets/css/sportsmanagement-tournament-tree.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'com_sportsmanagement.native-tournament-tree',
            'components/com_sportsmanagement/assets/js/sportsmanagement-tournament-tree.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['com_sportsmanagement.tournament-bracket']
        );

        $document->setTitle(
            Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE')
            . ($this->projectName !== '' ? ' - ' . $this->projectName : '')
        );
    }

    /**
     * Accept only CSS hexadecimal colors from historical tournament settings.
     *
     * Rejects CSS escapes, functions, semicolons and arbitrary styles before
     * the native template inserts the colors into a style attribute.
     *
     * @param mixed $value Color returned by the project model.
     * @param string $fallback Known-safe six-digit hexadecimal color.
     * @return string Accepted hexadecimal color or the safe fallback.
     */
    private static function safeColor(mixed $value, string $fallback): string
    {
        return is_string($value)
            && preg_match('/^#[a-fA-F0-9]{6}$/D', $value) === 1
            ? $value
            : $fallback;
    }
}
