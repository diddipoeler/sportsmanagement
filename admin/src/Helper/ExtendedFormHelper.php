<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

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

            $factory = Factory::getContainer()->get(FormFactoryInterface::class);
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
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }
}
