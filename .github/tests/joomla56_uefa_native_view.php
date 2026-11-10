<?php
/**
 * Joomla 5/6 native UEFA view, filtered seasons and HTML safety regression.
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
         * Keep language keys deterministic without Joomla translation files.
         *
         * @param string $key The Joomla translation key.
         * @return string Same untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class UefawertungModel
    {
        public string $coefficientyear = '';
        public array $calls = [];

        /**
         * Return available coefficient years, including hostile labels.
         *
         * @return array<int,object> Available year ID/name records.
         */
        public function getcoefficientyears(): array
        {
            ++$this->calls['years'];
            return [
                (object) ['id' => '2025', 'name' => '2025 <svg onload=alert(1)>'],
                (object) ['id' => '2024', 'name' => '2024'],
                (object) ['id' => 'x"><script>', 'name' => 'Invalid ID'],
                (object) ['id' => [], 'name' => 'Malformed array ID'],
            ];
        }

        /**
         * Mock country rows returned by the legitimate coefficient year.
         *
         * @param string $year Filtered and verified year.
         * @return array<int,object> Coefficient rows.
         */
        public function getcoefficientyearspoints(string $year): array
        {
            $this->calls['points'][] = $year;
            return [(object) [
                'team' => '<img src=x onerror=alert(2)>',
                '2023' => '1.250',
                '2024' => '3.500',
                'total' => '4.750',
            ]];
        }

        /**
         * Supply escaped-column season headings for the selected year.
         *
         * @param string $year Valid UEFA coefficient year.
         * @return array<int,string> Season column labels.
         */
        public function getSeasonNames(string $year): array
        {
            $this->calls['seasons'][] = $year;
            return ['2023', '2024'];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementProjectHtmlView
    {
        public object $model;
        public object $document;
        public object $app;
        public object $uri;
        public array $config = [];
        public string $divclasscontainer = 'container-fluid';
        public string $headertitle = '';

        /**
         * Return the isolated native model.
         *
         * @return object An injected UEFA model.
         */
        public function getModel(): object { return $this->model; }

        /**
         * Return the isolated Joomla HTML document.
         *
         * @return object Document accepting setTitle().
         */
        public function getDocument(): object { return $this->document; }

        /**
         * Include the real Joomla native UEFA template with view scope.
         *
         * @param string $file Absolute local template source file.
         * @return string Captured safe markup.
         */
        public function render(string $file): string
        {
            ob_start();
            try {
                include $file;
                return (string) ob_get_clean();
            } catch (\Throwable $error) {
                ob_end_clean();
                throw $error;
            }
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/View/Uefawertung/HtmlView.php';
    $klass = \Diddipoeler\Component\SportsManagement\Site\View\Uefawertung\HtmlView::class;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel();
    $view = new $klass();
    $view->model = $model;
    $view->app = new class {
        /**
         * Return an active menu item with a malicious HTML-looking title.
         *
         * @return object Joomla menu lookup fixture.
         */
        public function getMenu(): object
        {
            return new class {
                /**
                 * Return the active menu title without HTML sanitization.
                 *
                 * @return object Active Joomla menu record.
                 */
                public function getActive(): object
                {
                    return (object) ['title' => 'UEFA <script>alert(3)</script>'];
                }
            };
        }
    };
    $view->document = new class {
        public string $title = '';

        /**
         * Record Joomla document's chosen coefficient page title.
         *
         * @param string $title Display title.
         * @return void
         */
        public function setTitle(string $title): void { $this->title = $title; }
    };
    $view->uri = new class {
        /**
         * Return a local Joomla URL containing a query ampersand.
         *
         * @return string Escapable site URL.
         */
        public function toString(): string
        {
            return '/index.php?view=uefawertung&Itemid=99';
        }
    };

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $prepare = new \ReflectionMethod($view, 'prepareView');
        $path = dirname(__DIR__, 2) . '/site/tmpl/uefawertung/default.php';

        $model->coefficientyear = '2025';
        $prepare->invoke($view);
        $html = $view->render($path);

        if ($view->selectedYear !== '2025'
            || $model->calls['points'] !== ['2025']
            || $model->calls['seasons'] !== ['2025']
            || count($view->availableYears) !== 2
            || !str_contains($html, '&lt;script&gt;alert(3)&lt;/script&gt;')
            || !str_contains($html, '2025 &lt;svg onload=alert(1)&gt;')
            || !str_contains($html, '&lt;img src=x onerror=alert(2)&gt;')
            || !str_contains($html, '1.250')
            || !str_contains($html, '4.750')
            || !str_contains($html, 'view=uefawertung&amp;Itemid=99')
            || !str_contains($html, 'value="2025"')
            || str_contains($html, '<script>alert')
            || str_contains($html, '<svg onload')
            || str_contains($html, '<img src=x onerror')) {
            throw new \RuntimeException('Native UEFA presentation lost totals or escaped output.');
        }

        $count = count($model->calls['points']);
        $model->coefficientyear = 'x"><script>alert(4)</script>';
        $prepare->invoke($view);
        $emptyHtml = $view->render($path);

        if ($view->selectedYear !== '' || count($model->calls['points']) !== $count
            || count($model->calls['seasons']) !== $count
            || str_contains($emptyHtml, 'value="x"')
            || !str_contains($emptyHtml, 'JGLOBAL_NO_MATCHING_RESULTS')
            || str_contains($emptyHtml, '<tbody>')) {
            throw new \RuntimeException('Invalid UEFA year reached database lookup or markup.');
        }

        $wrong = new $klass();
        $wrong->model = new \stdClass();
        try {
            $prepare->invoke($wrong);
            throw new \RuntimeException('Native UEFA view accepted an invalid model.');
        } catch (\RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'requires UefawertungModel')) {
                throw $e;
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 UEFA native view, HTML and season validation OK\n";
}
