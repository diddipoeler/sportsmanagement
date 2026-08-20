<?php
namespace Diddipoeler\Module\SportsManagementClubicons\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $params = $data['params'];
        $template = (string) $params->get('template', 'default');
        $params->set('layout', $template);

        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $result = $this->getHelperFactory()->getHelper('ClubiconsHelper')->getData(
            $params,
            $data['module'],
            $app
        );

        $data['project'] = $result['project'];
        $data['ranking'] = $result['ranking'];
        $data['teams'] = $result['teams'];
        $data['count'] = count($result['teams']);

        if ($template === 'default' && $data['count'] > 0) {
            $document = $app->getDocument();
            $wam = $document->getWebAssetManager();
            $wam->registerAndUseStyle(
                'mod_sportsmanagement_clubicons.default',
                'modules/' . $data['module']->module . '/css/default.css'
            );

            $percent = (float) $params->get('max_width_after_mouse_over', 10);
            $scale = max(0.1, (100 + $percent) / 100);
            $height = max(1, (int) $params->get('picture_height', 50));
            $wam->addInlineStyle(
                '.mod-sportsmanagement-clubicons .img-zoom{' .
                'width:auto;height:' . $height . 'px;transition:transform .2s ease-in-out}' .
                '.mod-sportsmanagement-clubicons .img-zoom:hover{' .
                'transform:scale(' . rtrim(rtrim(number_format($scale, 4, '.', ''), '0'), '.') . ')}'
            );
        }

        return $data;
    }
}
