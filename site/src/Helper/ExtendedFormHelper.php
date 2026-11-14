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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
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
     * cannot be located or loaded. Rejects unsafe form identifiers, limits
     * resolution to the real extended XML directory and refuses symlinks
     * escaping that directory. Logs parser failures privately rather than
     * exposing exception messages in site HTML.
     *
     * @param string $data Optional serialised form values.
     * @param string $file Extended form definition name, without .xml.
     * @return Form|false Bound Joomla form or false on failure.
     */
    public static function load(string $data = '', string $file = ''): Form|false
    {
        // Never convert an unsafe path into a different valid form name.
        // Form identifiers are simple filenames, not user-controlled paths.
        if ($file === '' || strlen($file) > 100
            || preg_match('/^[A-Za-z0-9_-]+$/D', $file) !== 1) {
            return false;
        }

        $root = realpath(JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/extended');
        $xmlFile = $root === false ? false : realpath($root . DIRECTORY_SEPARATOR . $file . '.xml');

        // A valid filename can still name a symlink to another directory.
        // Only load definitions whose canonical paths stay inside the
        // component's extended form asset directory.
        if ($xmlFile === false || !is_file($xmlFile)
            || !str_starts_with($xmlFile, $root . DIRECTORY_SEPARATOR)) {
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
            Log::add(
                __METHOD__ . ': extended form loading failed (' . get_class($e) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }
    }
}
