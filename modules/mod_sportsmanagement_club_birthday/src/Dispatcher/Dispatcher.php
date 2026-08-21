<?php
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Dispatcher;

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
        $this->getApplication()->getLanguage()->load(
            'com_sportsmanagement',
            JPATH_ADMINISTRATOR,
            null,
            true
        );
        $result = $this->getHelperFactory()->getHelper('ClubBirthdayHelper')->getData($data['params']);
        $data['clubs'] = $result['clubs'];
        $data['mode'] = $result['mode'];

        return $data;
    }
}
