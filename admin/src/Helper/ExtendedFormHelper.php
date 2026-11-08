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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

/**
 * Load component-specific extended forms without the legacy sportsmanagementHelper.
 */
final class ExtendedFormHelper
{
    /**
     * Load an extended administrator form from its configured XML definition.
     *
     * Resolves a safe group and view identifier, loads any stored Registry
     * data, and creates the form using Joomla's injected FormFactoryInterface.
     * Missing definitions or invalid stored settings return null; internal
     * exceptions are logged without exposing raw exception text in the UI.
     *
     * @param string $group Extended form group, or extendeduser for user forms.
     * @param string $view Administrator entity name whose form XML is loaded.
     * @param string $stored Optional JSON Registry state saved for the form.
     * @return Form|null Prepared Joomla form, or null if unavailable.
     */
    public function load(string $group, string $view, string $stored = ''): ?Form
    {
        $group = $group === 'extendeduser' ? 'extendeduser' : 'extended';
        $view = trim($view);

        if ($view === '' || strlen($view) > 100
            || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $view) !== 1) {
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
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': unable to prepare extended form (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return null;
        }
    }
}
