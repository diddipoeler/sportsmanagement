<?php
/**
 * Joomla 5/6 native combined resultsmatrix view and HTML safety contract.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text {
        /**
         * Return stable Joomla translation keys for isolated template tests.
         *
         * @param string $key Requested language key.
         * @return string The same untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper {
        /**
         * Capture a native project/team route with an HTML-escapable ampersand.
         *
         * @param string $view Destination Joomla view.
         * @param array<string,mixed> $params Route selectors.
         * @return string Synthetic frontend URL.
         */
        public static function view(string $view, array $params = []): string {
            return '/index.php?view=' . urlencode($view)
                . '&p=' . urlencode((string) ($params['p'] ?? 0));
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        protected int $projectId = 42;
        protected int $divisionId = 0;
        /** @return int Current project identifier. */
        public function getProjectId(): int { return $this->projectId; }
        /** @return int Current division selection. */
        public function getDivisionId(): int { return $this->divisionId; }
    }
    class MatrixModel {
        public static int $roundid = 5;
        private int $projectId = 0;

        /** @param int $project Active project identifier. @return void */
        public function setProjectId(int $project): void { $this->projectId = $project; }

        /** @param int $division Selected division ID. @return array<int,object> Matrix teams. */
        public function getProjectTeamsIndexed(int $division): array {
            return [
                10 => (object) ['projectteamid'=>10, 'name'=>'<img onerror=alert(1)>', 'team_slug'=>'10:home'],
                11 => (object) ['projectteamid'=>11, 'name'=>'FC Köln', 'team_slug'=>'11:away'],
            ];
        }

        /** @param int $project Selected project ID. @return array<int,object> Scoped fixtures. */
        public function getMatrixResults(int $project): array {
            if ($project !== $this->projectId) {
                throw new \RuntimeException('Unscoped matrix fixture read.');
            }
            return [(object) [
                'projectteam1_id'=>10, 'projectteam2_id'=>11,
                'e1'=>'2', 'e2'=>'<script>alert(2)</script>',
                'roundname'=>'Final <b>unsafe</b>',
            ]];
        }
        /** @return array<int,object> Project divisions. */
        public function getDivisions(): array { return []; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementProjectHtmlView {
        public ?object $project = null;
        public object $model;
        public object $params;
        public int $databaseSelector = 0;
        public string $divclasscontainer = 'container-fluid';
        public array $config = ['show_matrix'=>true, 'link_teams'=>true];
        public object $document;

        /** @return object Joomla MVC model fixture. */
        public function getModel(): object { return $this->model; }

        /** @return object Joomla HTML document fixture. */
        public function getDocument(): object { return $this->document; }

        /**
         * Capture the actual native Joomla PHP layout with this view scope.
         *
         * @param string $path Absolute local template path.
         * @return string Rendered HTML.
         */
        public function render(string $path): string {
            ob_start();
            try {
                include $path;
                return (string) ob_get_clean();
            } catch (\Throwable $error) {
                ob_end_clean();
                throw $error;
            }
        }

        /**
         * Resolve the bounded results sublayout used by this combined view.
         *
         * @param string $part Registered native result sublayout name.
         * @return string Rendered Joomla sublayout HTML.
         */
        public function loadTemplate(string $part): string {
            if ($part !== 'results') {
                throw new \RuntimeException('Unknown combined template fragment.');
            }
            return $this->render(dirname(__DIR__, 2) . '/site/tmpl/resultsmatrix/default_results.php');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require $root . '/site/src/Helper/MatrixPresentationHelper.php';
    require $root . '/site/src/Model/ResultsmatrixModel.php';
    require $root . '/site/src/View/Resultsmatrix/HtmlView.php';

    $viewClass = \Diddipoeler\Component\SportsManagement\Site\View\Resultsmatrix\HtmlView::class;
    $view = new $viewClass();
    $view->project = (object) ['id'=>42, 'name'=>'Project <script>alert(3)</script>'];
    $view->model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsmatrixModel();
    $view->params = new class {
        /**
         * Specify matrix-first saved menu presentation ordering.
         *
         * @param string $name Requested Joomla parameter.
         * @param mixed $default Default when absent.
         * @return int Saved ordering option.
         */
        public function get(string $name, mixed $default = null): int {
            return $name === 'what_to_show_first' ? 1 : 0;
        }
    };
    $view->document = new class {
        public string $title = '';
        /**
         * Capture Joomla's translated combined-results page title.
         *
         * @param string $title Current native document heading.
         * @return void
         */
        public function setTitle(string $title): void { $this->title = $title; }
    };
    $temp = sys_get_temp_dir() . '/jsm56-combined-' . bin2hex(random_bytes(4));
    $matrixDir = $temp . '/components/com_sportsmanagement/tmpl/matrix';
    if (!mkdir($matrixDir, 0700, true)) {
        throw new \RuntimeException('Cannot prepare native template mount.');
    }
    $matrixCopy = $matrixDir . '/default.php';
    if (!copy($root . '/site/tmpl/matrix/default.php', $matrixCopy)) {
        throw new \RuntimeException('Cannot copy native matrix layout fixture.');
    }
    define('JPATH_SITE', $temp);

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $prepare = new \ReflectionMethod($view, 'prepareView');
        $prepare->invoke($view);
        $html = $view->render($root . '/site/tmpl/resultsmatrix/default.php');

        $m = strpos($html, 'id="resultsmatrix-matrix"');
        $r = strpos($html, 'id="resultsmatrix-results"');

        if ($m === false || $r === false || $m >= $r
            || $view->roundid !== 5 || count($view->fixtures) !== 1
            || $view->fixtures[0]['score'] !== '2:–'
            || !str_contains($html, '2:–')
            || !str_contains($html, '&lt;img onerror=alert(1)&gt;')
            || !str_contains($html, 'Final &lt;b&gt;unsafe&lt;/b&gt;')
            || !str_contains($html, 'Project &lt;script&gt;alert(3)&lt;/script&gt;')
            || str_contains($html, '<img onerror')
            || str_contains($html, '<script>alert')
            || !str_contains($html, 'view=roster&amp;p=42')) {
            throw new \RuntimeException('Combined Joomla 5/6 HTML rendered unsafe or incomplete output.');
        }

        if (!str_contains($view->document->title, 'COM_SPORTSMANAGEMENT_XML_RESULTS_MATRIX_LAYOUT_TITLE')) {
            throw new \RuntimeException('Combined frontend view did not set a Joomla title.');
        }

        $missing = new $viewClass();
        $missing->project = null;
        $missing->model = $view->model;
        $missing->document = $view->document;
        $prepare->invoke($missing);
        if ($missing->results !== [] || $missing->fixtures !== []) {
            throw new \RuntimeException('Missing project rendered cross-project results.');
        }

        $invalid = new $viewClass();
        $invalid->project = $view->project;
        $invalid->model = new \stdClass();
        try {
            $prepare->invoke($invalid);
            throw new \RuntimeException('Combined view accepted a wrong model type.');
        } catch (\RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'requires ResultsmatrixModel')) {
                throw $exception;
            }
        }
    } finally {
        restore_error_handler();
        @unlink($matrixCopy);
        @rmdir($matrixDir);
        @rmdir(dirname($matrixDir));
        @rmdir(dirname(dirname($matrixDir)));
        @rmdir($temp . '/components');
        @rmdir($temp);
    }

    echo "Joomla 5/6 native combined resultsmatrix HTML, model and escaping OK\n";
}
