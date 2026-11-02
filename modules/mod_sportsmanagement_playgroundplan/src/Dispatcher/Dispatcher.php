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
     * Loads the component language, normalises helper data, registers the stylesheet and enables the ticker script only in the configured mode.
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
            $wam = $document->getWebAssetManager();
            $wam->registerAndUseStyle(
                'mod_sportsmanagement_playgroundplan',
                'modules/mod_sportsmanagement_playgroundplan/css/mod_sportsmanagement_playgroundplan.css'
            );

            $rawMode = $data['params']->get('mode', 0);
            if ((is_int($rawMode) && $rawMode === 0)
                || (is_string($rawMode) && preg_match('/^0+$/D', $rawMode))) {
                $wam->registerAndUseScript(
                    'mod_sportsmanagement_playgroundplan.ticker',
                    'modules/mod_sportsmanagement_playgroundplan/js/ticker.js'
                );
            }
        }

        return $data;
    }
}
