<?php
/**
 * Joomla 5/6 triple results/ranking/matrix native HTML security contract.
 *
 * Exercises actual reusable native layouts and Joomla view preparation with
 * isolated model/document/router stubs but without a database installation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return stable keys for untranslated Joomla template labels.
         *
         * @param string $key Joomla language resource key.
         * @return string Untranslated resource key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Capture the Joomla roster link and both bounded project selectors.
         *
         * @param string $view Native frontend destination.
         * @param array<string,mixed> $args Encoded project/team selectors.
         * @return string Query URL with HTML-escapable ampersands.
         */
        public static function view(string $view, array $args = []): string
        {
            return '/index.php?view=' . urlencode($view)
                . '&p=' . urlencode((string) ($args['p'] ?? ''))
                . '&db=' . urlencode((string) ($args['cfg_which_database'] ?? ''));
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsrankingmatrixModel
    {
        public static int $cfg_which_database = 1;
        public int $reads = 0;

        /**
         * Provide three presentations already scoped by a single real model.
         *
         * @param object $project Joomla current project ID.
         * @param array<string,mixed> $config Saved Joomla frontend options.
         * @return array{roundid:int,divisionid:int,fixtures:array,standings:array,teams:array,results:array,divisions:array} Test records.
         */
        public function getTripleData(object $project, array $config = []): array
        {
            if (($project->id ?? null) !== 42) {
                throw new \RuntimeException('The test view used another project.');
            }

            ++$this->reads;

            return [
                'roundid' => 5,
                'divisionid' => 4,
                'fixtures' => [[
                    'home' => '<script>alert(1)</script>',
                    'away' => 'FC Köln & Berlin',
                    'score' => '2:1',
                    'round' => '<svg onload=alert(2)>',
                ]],
                'standings' => [[
                    'division' => 4,
                    'rows' => [[
                        'rank' => 1,
                        'name' => '<img src=x onerror=alert(3)>',
                        'played' => '5',
                        'wins' => '3',
                        'draws' => '1',
                        'losses' => '1',
                        'points' => '10',
                    ]],
                ]],
                'teams' => [
                    (object) [
                        'projectteamid' => 11,
                        'name' => '<script>alert(1)</script>',
                        'team_slug' => '11:home',
                    ],
                    (object) [
                        'projectteamid' => 12,
                        'name' => 'FC Köln & Berlin',
                        'team_slug' => '12:away',
                    ],
                ],
                'results' => [
                    (object) [
                        'projectteam1_id' => 11,
                        'projectteam2_id' => 12,
                        'e1' => 2,
                        'e2' => '1',
                    ],
                ],
                'divisions' => [],
            ];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementProjectHtmlView
    {
        public ?object $project = null;
        public array $config = ['show_matrix' => true, 'link_teams' => true];
        public string $divclasscontainer = 'container-fluid';
        public object $params;
        public object $model;
        public object $document;

        /**
         * Return the native model injected by the isolated Joomla view.
         *
         * @return object Test combined results/ranking/matrix model.
         */
        public function getModel(): object { return $this->model; }

        /**
         * Return the Joomla HTML document with a page-title capture method.
         *
         * @return object Document fixture with setTitle().
         */
        public function getDocument(): object { return $this->document; }

        /**
         * Render a real Joomla PHP template in the current view's scope.
         *
         * @param string $path Absolute native template filename.
         * @return string Escaped HTML output from the actual source template.
         */
        public function render(string $path): string
        {
            ob_start();
            try {
                include $path;
                return (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }
        }

        /**
         * Resolve only known triple-view templates by a static whitelist.
         *
         * @param string $part Ranking, results or matrix sublayout.
         * @return string Rendered reusable native section output.
         */
        public function loadTemplate(string $part): string
        {
            if (!in_array($part, ['ranking', 'results', 'matrix'], true)) {
                throw new \RuntimeException('Unknown combined view subtemplate.');
            }

            return $this->render(dirname(__DIR__, 2)
                . '/site/tmpl/resultsrankingmatrix/default_' . $part . '.php');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require $root . '/site/src/Helper/MatrixPresentationHelper.php';
    require $root . '/site/src/View/Resultsrankingmatrix/HtmlView.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\View\Resultsrankingmatrix\HtmlView::class;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingmatrixModel();
    $view = new $class();
    $view->model = $model;
    $view->project = (object) ['id' => 42, 'name' => '<svg onload=alert(4)>'];
    $view->document = new class {
        public string $title = '';

        /**
         * Store the native Joomla page title without a browser.
         *
         * @param string $value Display title for the current project.
         * @return void
         */
        public function setTitle(string $value): void { $this->title = $value; }
    };
    $menu = new class {
        public int $first = 0;

        /**
         * Return the saved section-first menu choice in the real view.
         *
         * @param string $name Joomla menu parameter.
         * @param mixed $default Default parameter value.
         * @return int Saved choice or zero for another menu option.
         */
        public function get(string $name, mixed $default = null): int
        {
            return $name === 'what_to_show_first' ? $this->first : 0;
        }
    };
    $view->params = $menu;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $prepare = new \ReflectionMethod($view, 'prepareView');
        $prepare->invoke($view);

        if ($model->reads !== 1 || $view->roundid !== 5 || $view->divisionid !== 4
            || $view->databaseSelector !== 1
            || count($view->teams) !== 2 || count($view->results) !== 1
            || count($view->standings) !== 1 || count($view->fixtures) !== 1
            || !str_contains($view->document->title, 'COM_SPORTSMANAGEMENT_MATRIX')) {
            throw new \RuntimeException('Combined view lost the shared model or project data.');
        }

        $template = $root . '/site/tmpl/resultsrankingmatrix/default.php';
        foreach ([
            0 => ['ranking', 'results', 'matrix'],
            1 => ['results', 'ranking', 'matrix'],
            2 => ['matrix', 'results', 'ranking'],
        ] as $choice => $order) {
            $menu->first = $choice;
            $html = $view->render($template);
            $positions = [];

            foreach ($order as $section) {
                $positions[] = strpos($html, 'id="resultsrankingmatrix-' . $section . '"');
            }

            if (in_array(false, $positions, true)
                || $positions !== array_values($positions)
                || $positions !== array_values(array_unique($positions))
                || $positions[0] >= $positions[1] || $positions[1] >= $positions[2]
                || !str_contains($html, '&lt;svg onload=alert(4)&gt;')
                || !str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;')
                || !str_contains($html, '&lt;img src=x onerror=alert(3)&gt;')
                || !str_contains($html, 'FC Köln &amp; Berlin')
                || !str_contains($html, 'href="/index.php?view=roster&amp;p=42&amp;db=1"')
                || !str_contains($html, '2:1')
                || str_contains($html, '<svg onload')
                || str_contains($html, '<img src=x onerror')
                || str_contains($html, '<script>alert')) {
                throw new \RuntimeException('Triple view broke menu order, data or HTML escaping.');
            }
        }

        $missing = new $class();
        $missing->model = $model;
        $missing->project = null;
        $missing->document = $view->document;
        $prepare->invoke($missing);
        if ($model->reads !== 1 || $missing->standings !== [] || $missing->teams !== []) {
            throw new \RuntimeException('Missing project triggered triple view database access.');
        }

        $wrong = new $class();
        $wrong->model = new \stdClass();
        try {
            $prepare->invoke($wrong);
            throw new \RuntimeException('Triple view accepted the wrong native MVC model.');
        } catch (\RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'requires ResultsrankingmatrixModel')) {
                throw $exception;
            }
        }

        if (class_exists(\DOMDocument::class)) {
            $menuXml = new \DOMDocument();
            $loaded = $menuXml->load($root . '/site/tmpl/resultsrankingmatrix/default.xml');

            if (!$loaded) {
                throw new \RuntimeException('Combined Joomla menu layout XML is invalid.');
            }

            $xpath = new \DOMXPath($menuXml);
            $options = $xpath->query("//field[@name='what_to_show_first']/option");
            if ($options === false || $options->length !== 3) {
                throw new \RuntimeException('Missing the combined menu matrix-first option.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 native triple view, escaped panels, menu ordering and metadata OK\n";
}
