<?php
/**
 * Joomla 5/6 native resultsranking view, menu order and safe HTML tests.
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
         * Return deterministic Joomla translation keys in template assertions.
         *
         * @param string $key Translation key for the combined view.
         * @return string Original key without requiring a Joomla language pack.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsrankingModel
    {
        public int $reads = 0;

        /**
         * Provide representative data from the combined native model.
         *
         * @param object $project Selected project record.
         * @param array $config Joomla site view configuration.
         * @return array{roundid:int,divisionid:int,fixtures:array,standings:array} Safe display rows.
         */
        public function getCombinedData(object $project, array $config = []): array
        {
            if (($project->id ?? null) !== 42) {
                throw new \RuntimeException('Unscoped combined model request.');
            }

            ++$this->reads;

            return [
                'roundid' => 5,
                'divisionid' => 4,
                'fixtures' => [[
                    'home' => '<img onerror=alert(2)>',
                    'away' => 'FC Köln',
                    'score' => '2:–',
                    'round' => 'Round <script>alert(3)</script>',
                ]],
                'standings' => [[
                    'division' => 4,
                    'rows' => [
                        [
                            'rank' => 1,
                            'name' => '<script>alert(4)</script>',
                            'played' => '5',
                            'wins' => '3',
                            'draws' => '1',
                            'losses' => '1',
                            'points' => '10',
                        ],
                    ],
                ]],
            ];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementProjectHtmlView
    {
        public ?object $project = null;
        public array $config = [];
        public string $divclasscontainer = 'container-fluid';
        public object $model;
        public object $params;
        public object $document;

        /**
         * Return the Joomla model injected into this isolated view.
         *
         * @return object Native combined ranking model test double.
         */
        public function getModel(): object { return $this->model; }

        /**
         * Return the Joomla HTML document to capture its page title.
         *
         * @return object Stub document supporting setTitle().
         */
        public function getDocument(): object { return $this->document; }

        /**
         * Render a real PHP layout in the native Joomla view's $this scope.
         *
         * @param string $file Absolute template filename from the repository.
         * @return string Captured HTML for view security assertions.
         */
        public function render(string $file): string
        {
            ob_start();
            try {
                include $file;
                return (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }
        }

        /**
         * Resolve only the fixed, known combined results and ranking layouts.
         *
         * @param string $template One of the two native sublayout identifiers.
         * @return string Rendered and escaped results/standings fragment.
         */
        public function loadTemplate(string $template): string
        {
            if (!in_array($template, ['results', 'ranking'], true)) {
                throw new \RuntimeException('Invalid combined Joomla subtemplate.');
            }

            return $this->render(dirname(__DIR__, 2)
                . '/site/tmpl/resultsranking/default_' . $template . '.php');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require $root . '/site/src/View/Resultsranking/HtmlView.php';

    $viewClass = \Diddipoeler\Component\SportsManagement\Site\View\Resultsranking\HtmlView::class;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel();
    $view = new $viewClass();
    $view->model = $model;
    $view->project = (object) ['id' => 42, 'name' => 'Project <svg onload=alert(1)>'];
    $view->document = new class {
        public string $title = '';

        /**
         * Capture the Joomla site page title without a document service.
         *
         * @param string $title Native translated combined page heading.
         * @return void
         */
        public function setTitle(string $title): void { $this->title = $title; }
    };

    $settings = new class {
        public int $first = 0;

        /**
         * Emulate saved menu ordering choices for old Joomla installations.
         *
         * @param string $key Requested module/menu property.
         * @param mixed $default Default property value.
         * @return int Saved display order for the combined view.
         */
        public function get(string $key, mixed $default = null): int
        {
            return $key === 'what_to_show_first' ? $this->first : 0;
        }
    };
    $view->params = $settings;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $prepare = new \ReflectionMethod($view, 'prepareView');
        $prepare->invoke($view);

        if ($view->roundid !== 5 || $view->divisionid !== 4 || $model->reads !== 1
            || !str_contains($view->document->title, 'RESULTS_RANKING_LAYOUT_TITLE')) {
            throw new \RuntimeException('Native resultsranking failed to prepare selected project data.');
        }

        $template = $root . '/site/tmpl/resultsranking/default.php';
        $html = $view->render($template);
        $rankingPos = strpos($html, 'id="resultsranking-ranking"');
        $resultsPos = strpos($html, 'id="resultsranking-results"');

        if ($rankingPos === false || $resultsPos === false || $rankingPos >= $resultsPos
            || !str_contains($html, '2:–')
            || !str_contains($html, '&lt;script&gt;alert(4)&lt;/script&gt;')
            || !str_contains($html, 'Round &lt;script&gt;alert(3)&lt;/script&gt;')
            || !str_contains($html, 'Project &lt;svg onload=alert(1)&gt;')
            || !str_contains($html, '&lt;img onerror=alert(2)&gt;')
            || str_contains($html, '<script>alert')
            || str_contains($html, '<svg onload')
            || str_contains($html, '<img onerror')) {
            throw new \RuntimeException('Native resultsranking default view rendered unsafe or incomplete HTML.');
        }

        $settings->first = 1;
        $flipped = $view->render($template);
        $resultsPos = strpos($flipped, 'id="resultsranking-results"');
        $rankingPos = strpos($flipped, 'id="resultsranking-ranking"');
        if ($resultsPos === false || $rankingPos === false || $resultsPos >= $rankingPos) {
            throw new \RuntimeException('Existing Joomla results-first menu setting was ignored.');
        }

        $view->project = null;
        $readCount = $model->reads;
        $prepare->invoke($view);
        if ($model->reads !== $readCount) {
            throw new \RuntimeException('No-project resultsranking initiated data access.');
        }

        $view->project = (object) ['id' => 42];
        $view->model = new \stdClass();
        try {
            $prepare->invoke($view);
            throw new \RuntimeException('Native combined view accepted an invalid model.');
        } catch (\RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'requires ResultsrankingModel')) {
                throw $exception;
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 combined resultsranking view, ordering and escaped HTML OK\n";
}
