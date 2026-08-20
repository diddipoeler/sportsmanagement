<?php
namespace Diddipoeler\Module\SportsManagementBirthday\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $result = $this->getHelperFactory()->getHelper('BirthdayHelper')->getData(
            $data['params'],
            $componentParams,
            $this->getApplication()
        );

        $data['persons'] = $result['persons'];
        $data['mode'] = $result['mode'];
        $data['pictureServer'] = $result['pictureServer'];

        return $data;
    }
}
