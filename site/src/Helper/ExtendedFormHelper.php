<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Registry\Registry;

/**
 * Load legacy extended-data XML definitions without loading the monolithic legacy helper.
 */
final class ExtendedFormHelper
{
    public static function load(string $data = '', string $file = ''): Form|false
    {
        $file = basename($file);

        if ($file === '') {
            return false;
        }

        $xmlFile = JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/extended/' . $file . '.xml';

        if (!is_file($xmlFile)) {
            return false;
        }

        try {
            $registry = new Registry();

            if ($data !== '') {
                $registry->loadString($data);
            }

            $factory = Factory::getContainer()->get(FormFactoryInterface::class);
            $form = $factory->createForm(
                'com_sportsmanagement.' . $file . '.extended',
                ['control' => 'extended']
            );

            if (!$form->loadFile($xmlFile, false, '/config')) {
                return false;
            }

            $form->bind($registry);

            return $form;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
