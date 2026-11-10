<?php
/**
 * Joomla 5/6 native tournament HTML view and template output regression.
 *
 * Tests the namespaced MVC view, Web Asset Manager registration and
 * non-executable JSON markup without starting Joomla or querying a database.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
        public array $assets = [];
        public string $title = '';

        /**
         * Supply a minimal Joomla Web Asset Manager for registration tests.
         *
         * @return self Stub web-asset manager collecting requested resources.
         */
        public function getWebAssetManager(): self { return $this; }

        /**
         * Record a requested core script such as Joomla jQuery.
         *
         * @param string $name Joomla asset identifier.
         * @return self Fluent manager stub.
         */
        public function useScript(string $name): self
        {
            $this->assets[$name] = $name;
            return $this;
        }

        /**
         * Capture the native bracket JavaScript asset and its dependencies.
         *
         * @param string $name Unique asset name.
         * @param string $uri Local component asset URL.
         * @param array $options Asset options such as automatic versioning.
         * @param array $attributes Script loading attributes.
         * @param array $dependencies Required Joomla assets.
         * @return self Fluent manager stub.
         */
        public function registerAndUseScript(
            string $name,
            string $uri,
            array $options = [],
            array $attributes = [],
            array $dependencies = []
        ): self {
            $this->assets[$name] = [$uri, $options, $attributes, $dependencies];
            return $this;
        }

        /**
         * Capture stylesheet registration on the Joomla document.
         *
         * @param string $name Unique stylesheet name.
         * @param string $uri Local stylesheet path.
         * @param array $options Joomla web asset metadata.
         * @return self Fluent manager stub.
         */
        public function registerAndUseStyle(string $name, string $uri, array $options = []): self
        {
            $this->assets[$name] = $uri;
            return $this;
        }

        /**
         * Capture the title calculated for a tournament page.
         *
         * @param string $title Human-readable page title.
         * @return void
         */
        public function setTitle(string $title): void { $this->title = $title; }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return Joomla's requested language key for deterministic assertions.
         *
         * @param string $key Translated label.
         * @return string Unchanged Joomla language key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementProjectHtmlView
    {
        public ?object $project = null;
        public object $model;
        public \Joomla\CMS\Document\HtmlDocument $document;

        /**
         * Return the configured fake model to the native subclass.
         *
         * @return object Native tournament model fixture.
         */
        public function getModel(): object { return $this->model; }

        /**
         * Return the Joomla HTML document asset manager fixture.
         *
         * @return \Joomla\CMS\Document\HtmlDocument Test page document.
         */
        public function getDocument(): \Joomla\CMS\Document\HtmlDocument
        {
            return $this->document;
        }

        /**
         * Include a real Joomla PHP template with this view as $this.
         *
         * @param string $file Absolute on-disk Joomla layout path.
         * @return string HTML output captured from the layout.
         */
        public function render(string $file): string
        {
            ob_start();
            try {
                include $file;
                return (string) ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    final class JltournamenttreeModel
    {
        public array $rounds = [];
        public array $bracket = [];
        public int $readCalls = 0;
        public int $projectId = 42;
        public string $colorFrom = '#123456';
        public string $colorTo = '#abcdef';
        public int $roundWidth = 6000;
        public int $fontSize = 99;

        /**
         * Return the current SportsManagement project for view guard tests.
         *
         * @return int Positive project ID or zero for an invalid selection.
         */
        public function getProjectId(): int { return $this->projectId; }

        /**
         * Supply a raw tournament gradient setting, valid or malformed.
         *
         * @return string Configured starting CSS gradient color.
         */
        public function getColorFrom(): string { return $this->colorFrom; }

        /**
         * Supply the other gradient color from the saved project.
         *
         * @return string Configured ending CSS gradient color.
         */
        public function getColorTo(): string { return $this->colorTo; }

        /**
         * Supply an intentionally oversized legacy tournament column width.
         *
         * @return int Tournament width requested by the model.
         */
        public function getTreeBracketRoundWidth(): int { return $this->roundWidth; }

        /**
         * Supply a historical font size requiring CSS bounds.
         *
         * @return int Font size configured by the legacy model.
         */
        public function getFontSize(): int { return $this->fontSize; }

        /**
         * Return the bracket's named tournament rounds without a database.
         *
         * @return array<int,object> Fake tournament rounds.
         */
        public function getTournamentRounds(): array { return $this->rounds; }

        /**
         * Simulate the legacy model's bracket-building side effect.
         *
         * The return value deliberately resembles an unsafe script fragment;
         * the new view must ignore it and use structured match objects only.
         *
         * @param array $rounds Tournament rounds to arrange.
         * @return string Unsafe historical JavaScript fragment.
         */
        public function getTournamentMatches(array $rounds): string
        {
            ++$this->readCalls;
            return '"><script>alert("legacy-js")</script>';
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/TournamentTreePayload.php';
    require_once $root . '/site/src/View/Jltournamenttree/HtmlView.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\View\Jltournamenttree\HtmlView::class;
    $modelClass = \Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel::class;
    $model = new $modelClass();
    $model->rounds = [(object) ['roundcode' => 1, 'name' => 'Final']];
    $model->bracket = [1 => [(object) [
        'firstname' => 'North <img onerror=alert(1)>',
        'secondname' => 'South & West',
        'team1_result' => 2,
        'team2_result' => '1',
    ]]];

    $view = new $class();
    $view->project = (object) [
        'project_type' => 'TOURNAMENT_MODE',
        'name' => '<script>alert("project")</script>',
    ];
    $view->model = $model;
    $view->document = new \Joomla\CMS\Document\HtmlDocument();

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $prepare = new \ReflectionMethod($view, 'prepareView');
        $prepare->invoke($view);
        $html = $view->render($root . '/site/tmpl/jltournamenttree/default.php');

        if ($model->readCalls !== 1
            || $view->roundWidth !== 800
            || $view->fontSize !== 28
            || $view->treePayload['teams'][0][0] !== 'North <img onerror=alert(1)>') {
            throw new \RuntimeException('Native bracket MVC preparation did not load or normalize tournament records.');
        }

        if (!str_contains($html, '&lt;script&gt;alert')
            || str_contains($html, '<script>alert')
            || str_contains($html, '<img onerror')
            || str_contains($html, 'legacy-js')
            || !str_contains($html, '\\u003Cimg')
            || !str_contains($html, 'data-jsm-tournament-tree')
            || !str_contains($html, 'type="application/json"')) {
            throw new \RuntimeException('Native tournament HTML exposed executable model/club data.');
        }

        $assets = $view->document->assets;
        foreach ([
            'jquery',
            'com_sportsmanagement.tournament-bracket',
            'com_sportsmanagement.native-tournament-tree',
        ] as $key) {
            if (!isset($assets[$key])) {
                throw new \RuntimeException('Native tournament asset was not registered: ' . $key);
            }
        }

        if (!str_contains($assets['com_sportsmanagement.native-tournament-tree'][0], 'sportsmanagement-tournament-tree.js')
            || ($assets['com_sportsmanagement.native-tournament-tree'][3][0] ?? null) !== 'com_sportsmanagement.tournament-bracket') {
            throw new \RuntimeException('Native tournament renderer lost its bracket script dependency.');
        }

        $model->colorFrom = 'red; background:url(javascript:alert(1))';
        $model->colorTo = 'invalid';
        $prepare->invoke($view);

        if ($view->colorFrom !== '#FFFFFF' || $view->colorTo !== '#0000FF') {
            throw new \RuntimeException('Malformed legacy tournament CSS values were accepted.');
        }

        $view->project->project_type = 'LEAGUE';
        $model->readCalls = 0;
        $view->treePayload = ['teams' => [], 'results' => [], 'rounds' => []];
        $prepare->invoke($view);

        if ($model->readCalls !== 0 || $view->treePayload['teams'] !== []) {
            throw new \RuntimeException('Unsupported project type triggered bracket database loading.');
        }

        $script = (string) file_get_contents($root . '/site/assets/js/sportsmanagement-tournament-tree.js');

        if (!str_contains($script, 'JSON.parse(')
            || !str_contains($script, "container.text(")
            || str_contains($script, 'container.html(')) {
            throw new \RuntimeException('Tournament JS renderer does not use text-only JSON presentation.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 native tournament view, assets and HTML injection guards OK\n";
}
