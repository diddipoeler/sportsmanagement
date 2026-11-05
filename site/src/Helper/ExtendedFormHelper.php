<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Registry\Registry;

/**
 * Load legacy extended-data XML definitions without loading the monolithic legacy helper.
 */
final class ExtendedFormHelper
{
    /**
     * Load and bind a legacy extended-data XML form through Joomla services.
     *
     * Restricts the form definition to the component's extended assets,
     * resolves Joomla's form factory and binds the supplied Registry
     * values after loading its XML. Returns false when the definition
     * cannot be located or loaded, while reporting parsing errors.
     *
     * @param string $data Optional serialised form values.
     * @param string $file Extended form definition name, without .xml.
     * @return Form|false Bound Joomla form or false on failure.
     */
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

        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

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
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
