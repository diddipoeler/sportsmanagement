<?php
/**
 * Joomla 5/6 helper for loading SportsManagement extended administrator forms.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Registry\Registry;

/**
 * Load component-specific extended forms without the legacy sportsmanagementHelper.
 */
final class ExtendedFormHelper
{
    public function load(string $group, string $view, string $stored = ''): ?Form
    {
        $group = $group === 'extendeduser' ? 'extendeduser' : 'extended';
        $view = preg_replace('/[^A-Za-z0-9_-]/', '', trim($view)) ?: '';

        if ($view === '') {
            return null;
        }

        $path = JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/' . $group . '/' . $view . '.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $registry = new Registry();

            if ($stored !== '') {
                $registry->loadString($stored);
            }

            $container = Factory::getContainer();
            $factory = $container->get(FormFactoryInterface::class);
            $form = $factory->createForm(
                'com_sportsmanagement.' . $view . '.' . $group,
                ['control' => $group]
            );

            if (!$form->loadFile($path, true, '/config')) {
                return null;
            }

            $form->bind($registry);

            return $form;
        } catch (\Throwable $e) {
            /** @var AdministratorApplication $app */
            $app = Factory::getContainer()->get(AdministratorApplication::class);
            $app->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }
}
