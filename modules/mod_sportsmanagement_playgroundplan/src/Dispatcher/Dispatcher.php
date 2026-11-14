<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Playground Plan module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare the playground schedule layout for the site module.
     *
     * Loads the component language, normalises helper data, and reuses
     * CSS on HTML pages while enabling ticker JavaScript only in ticker mode.
     *
     * @return array|false Render variables, or false when skipped.
     * @throws \RuntimeException When the application is not the site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement PlaygroundPlan requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        $plan = $this->getHelperFactory()
            ->getHelper('PlaygroundPlanHelper')
            ->getData($data['params'], $app, $data['module']);
        $data['list'] = is_array($plan) ? $plan : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $rawMode = $data['params']->get('mode', 0);
            $tickerMode = (is_int($rawMode) && $rawMode === 0)
                || (is_string($rawMode) && preg_match('/^0+$/D', $rawMode));

            $this->usePlanAssets($document, $tickerMode);
        }

        return $data;
    }
    /**
     * Activate Playground Plan CSS and optional ticker JavaScript.
     *
     * Reuse Joomla's shared HTML WebAssetManager across module instances
     * without repeating registrations. Preserve the original asset sources,
     * cache options, deferred attributes and required dependencies.
     *
     * @param HtmlDocument $document Current Joomla site HTML response.
     * The ticker is registered only when the module's validated zero or
     * zero-padded mode requires it; other plan instances use styles alone.
     *
     * @param bool $tickerMode Whether to enable scrolling ticker output.
     * @return void
     */
    private function usePlanAssets(HtmlDocument $document, bool $tickerMode): void
    {
        $assets = $document->getWebAssetManager();
        $style = 'mod_sportsmanagement_playgroundplan';
        if (!$assets->assetExists('style', $style)) {
            $assets->registerStyle($style,
                'modules/mod_sportsmanagement_playgroundplan/css/mod_sportsmanagement_playgroundplan.css', []
            );
        }
        $assets->useStyle($style);
        if (!$tickerMode) {
            return;
        }
        $name = 'mod_sportsmanagement_playgroundplan.ticker';
        if (!$assets->assetExists('script', $name)) {
            $assets->registerScript($name,
                'modules/mod_sportsmanagement_playgroundplan/js/ticker.js', [], [], []
            );
        }
        $assets->useScript($name);
    }


}
