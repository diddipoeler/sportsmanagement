<?php
namespace Diddipoeler\Module\SportsManagementNewProject\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $helper = $this->getHelperFactory()->getHelper('NewProjectHelper');
        $data['params']->set('layout', 'native');
        $data['list'] = $helper->getData($data['params'], $app);
        $data['canCreateArticles'] = $helper->canCreateArticles($data['params'], $app);

        $assets = $app->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_new_project',
            'modules/mod_sportsmanagement_new_project/css/mod_sportsmanagement_new_project.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'mod_sportsmanagement_new_project.native',
            'modules/mod_sportsmanagement_new_project/js/native.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        return $data;
    }
}
