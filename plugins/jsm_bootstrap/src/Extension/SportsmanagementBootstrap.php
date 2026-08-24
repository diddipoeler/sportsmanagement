<?php
namespace Diddipoeler\Plugin\System\SportsmanagementBootstrap\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Application\AfterDispatchEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Event\SubscriberInterface;

/**
 * Joomla 5/6 system plugin for SportsManagement frontend assets.
 *
 * The legacy plugin replaced Joomla's internal script list and injected old
 * jQuery/Bootstrap releases. Joomla 5/6 owns those core assets, so this plugin
 * now uses the Web Asset Manager and only adds the optional third-party assets.
 */
final class SportsmanagementBootstrap extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDispatch' => 'onAfterDispatch',
        ];
    }

    public function onAfterDispatch(AfterDispatchEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $document = $app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $wa = $document->getWebAssetManager();

        $this->useBootstrap($wa);

        if ((int) $this->params->get('load_datatables', 0) === 1) {
            $this->useDataTables($wa);
        }

        if ((int) $this->params->get('load_k2css', 1) === 1 && ComponentHelper::isEnabled('com_k2')) {
            $wa->registerAndUseStyle(
                'plg.system.jsm_bootstrap.k2',
                'plugins/system/jsm_bootstrap/css/customk2.css'
            );
        }
    }

    private function useBootstrap(WebAssetManager $wa): void
    {
        if ((int) $this->params->get('load_bootstrap_css', 1) === 1) {
            $wa->useStyle('bootstrap.css');
        }

        if ((int) $this->params->get('load_bootstrap', 1) === 1) {
            foreach ([
                'alert',
                'button',
                'carousel',
                'collapse',
                'dropdown',
                'modal',
                'offcanvas',
                'popover',
                'scrollspy',
                'tab',
                'toast',
            ] as $component) {
                $wa->useScript('bootstrap.' . $component);
            }

            return;
        }

        if ((int) $this->params->get('load_bootstrap_carousel', 0) === 1) {
            $wa->useScript('bootstrap.carousel');
        }

        if ((int) $this->params->get('load_bootstrap_modal', 0) === 1) {
            $wa->useScript('bootstrap.modal');
        }

        if ((int) $this->params->get('load_bootstrap_tab', 0) === 1) {
            $wa->useScript('bootstrap.tab');
        }
    }

    private function useDataTables(WebAssetManager $wa): void
    {
        $wa->useScript('jquery');

        $flavour = (string) $this->params->get('load_for_which_bootstrap', '5');
        $integration = match ($flavour) {
            '3' => 'bootstrap',
            '4' => 'bootstrap4',
            '5' => 'bootstrap5',
            default => '',
        };

        $dataTables = 'plg.system.jsm_bootstrap.datatables';

        $wa->registerAndUseStyle(
            $dataTables,
            'https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css'
        );
        $wa->registerAndUseScript(
            $dataTables,
            'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
            [],
            ['defer' => true],
            ['jquery']
        );

        if ($integration !== '') {
            $wa->registerAndUseStyle(
                $dataTables . '.' . $integration,
                'https://cdn.datatables.net/1.13.8/css/dataTables.' . $integration . '.min.css',
                [],
                [],
                [$dataTables]
            );
            $wa->registerAndUseScript(
                $dataTables . '.' . $integration,
                'https://cdn.datatables.net/1.13.8/js/dataTables.' . $integration . '.min.js',
                [],
                ['defer' => true],
                [$dataTables]
            );
        }

        if ((int) $this->params->get('load_responsive', 0) === 1) {
            $this->useDataTablesExtension(
                $wa,
                'responsive',
                '2.5.0',
                'dataTables.responsive.min.js',
                'responsive.dataTables.min.css',
                $dataTables
            );
        }

        if ((int) $this->params->get('load_fixedcolumns', 0) === 1) {
            $this->useDataTablesExtension(
                $wa,
                'fixedcolumns',
                '4.3.0',
                'dataTables.fixedColumns.min.js',
                'fixedColumns.dataTables.min.css',
                $dataTables
            );
        }

        if ((int) $this->params->get('load_fixedheader', 0) === 1) {
            $this->useDataTablesExtension(
                $wa,
                'fixedheader',
                '3.4.0',
                'dataTables.fixedHeader.min.js',
                'fixedHeader.dataTables.min.css',
                $dataTables
            );
        }

        if ((int) $this->params->get('load_buttons', 0) === 1) {
            $this->useButtons($wa, $dataTables);
        }
    }

    private function useDataTablesExtension(
        WebAssetManager $wa,
        string $extension,
        string $version,
        string $script,
        string $style,
        string $dataTables
    ): void {
        $name = 'plg.system.jsm_bootstrap.datatables.' . $extension;
        $base = 'https://cdn.datatables.net/' . $extension . '/' . $version . '/';

        $wa->registerAndUseStyle($name, $base . 'css/' . $style, [], [], [$dataTables]);
        $wa->registerAndUseScript($name, $base . 'js/' . $script, [], ['defer' => true], [$dataTables]);
    }

    private function useButtons(WebAssetManager $wa, string $dataTables): void
    {
        $wa->registerAndUseScript(
            'plg.system.jsm_bootstrap.jszip',
            'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            [],
            ['defer' => true]
        );
        $wa->registerAndUseScript(
            'plg.system.jsm_bootstrap.pdfmake',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.9/pdfmake.min.js',
            [],
            ['defer' => true]
        );
        $wa->registerAndUseScript(
            'plg.system.jsm_bootstrap.pdfmake.fonts',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.9/vfs_fonts.js',
            [],
            ['defer' => true],
            ['plg.system.jsm_bootstrap.pdfmake']
        );

        $name = 'plg.system.jsm_bootstrap.datatables.buttons';
        $base = 'https://cdn.datatables.net/buttons/2.4.2/';

        $wa->registerAndUseStyle(
            $name,
            $base . 'css/buttons.dataTables.min.css',
            [],
            [],
            [$dataTables]
        );
        $wa->registerAndUseScript(
            $name,
            $base . 'js/dataTables.buttons.min.js',
            [],
            ['defer' => true],
            [$dataTables]
        );

        foreach (['buttons.colVis.min.js', 'buttons.html5.min.js', 'buttons.print.min.js'] as $script) {
            $asset = $name . '.' . str_replace(['.min.js', '.'], ['', '-'], $script);
            $dependencies = [$name];

            if ($script === 'buttons.html5.min.js') {
                $dependencies[] = 'plg.system.jsm_bootstrap.jszip';
                $dependencies[] = 'plg.system.jsm_bootstrap.pdfmake.fonts';
            }

            $wa->registerAndUseScript(
                $asset,
                $base . 'js/' . $script,
                [],
                ['defer' => true],
                $dependencies
            );
        }
    }
}
