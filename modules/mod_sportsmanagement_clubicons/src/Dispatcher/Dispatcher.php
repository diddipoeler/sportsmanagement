<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement clubicons module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementClubicons\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare the club icon layout and its display assets.
     *
     * Selects an allowed layout, loads club and team data, activates the
     * carousel only when requested and generates icon zoom CSS from strictly
     * bounded numeric settings. Rejects non-finite or excessively large
     * values before rendering inline styles.
     *
     * @return array|false Template context, or false when rendering is skipped.
     * @throws \RuntimeException When invoked outside Joomla's site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Clubicons requires the Joomla site application.', 500);
        }

        $params = $data['params'];
        $rawTemplate = $params->get('template', 'default');
        $template = is_string($rawTemplate) ? $rawTemplate : 'default';
        $template = in_array($template, ['default', 'default_carousel'], true) ? $template : 'default';
        $params->set('layout', $template);

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $result = $this->getHelperFactory()->getHelper('ClubiconsHelper')->getData(
            $params,
            $data['module'],
            $app
        );

        $result = is_array($result) ? $result : [];

        $data['project'] = is_object($result['project'] ?? null) ? $result['project'] : null;
        $data['ranking'] = is_array($result['ranking'] ?? null) ? $result['ranking'] : [];
        $data['teams'] = is_array($result['teams'] ?? null) ? $result['teams'] : [];
        $data['count'] = count($data['teams']);

        if ($data['count'] <= 0) {
            return $data;
        }

        $document = $app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return $data;
        }

        $wam = $document->getWebAssetManager();

        if ($template === 'default_carousel') {
            $wam->useScript('bootstrap.carousel');
            return $data;
        }

        $wam->registerAndUseStyle(
            'mod_sportsmanagement_clubicons.default',
            'modules/mod_sportsmanagement_clubicons/css/default.css',
            ['version' => 'auto']
        );

        $rawPercent = $params->get('max_width_after_mouse_over', 10);
        $percent = is_numeric($rawPercent) && is_scalar($rawPercent)
            && is_finite((float) $rawPercent) ? (float) $rawPercent : 10.0;
        $percent = min(400.0, max(-90.0, $percent));
        $scale = (100.0 + $percent) / 100.0;
        $rawHeight = $params->get('picture_height', 50);
        $height = is_numeric($rawHeight) && is_scalar($rawHeight)
            && is_finite((float) $rawHeight) ? (float) $rawHeight : 50.0;
        $height = (int) min(1000, max(1, $height));
        $wam->addInlineStyle(
            '.mod-sportsmanagement-clubicons .img-zoom{' .
            'width:auto;height:' . $height . 'px;transition:transform .2s ease-in-out}' .
            '.mod-sportsmanagement-clubicons .img-zoom:hover{' .
            'transform:scale(' . rtrim(rtrim(number_format($scale, 4, '.', ''), '0'), '.') . ')}'
        );

        return $data;
    }
}
