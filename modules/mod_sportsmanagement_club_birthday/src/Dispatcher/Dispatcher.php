<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

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
        $app->getLanguage()->load(
            'com_sportsmanagement',
            JPATH_ADMINISTRATOR,
            null,
            true
        );

        /** @var DatabaseInterface $database */
        $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()
            ->getHelper('ClubBirthdayHelper')
            ->getData($data['params'], $app, $database);

        $data['clubs'] = $result['clubs'];
        $data['mode'] = $result['mode'];

        if ($data['mode'] === 'BC') {
            $app->getDocument()->getWebAssetManager()->useScript('bootstrap.carousel');
        }

        return $data;
    }
}
