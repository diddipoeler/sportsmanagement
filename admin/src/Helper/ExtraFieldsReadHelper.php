<?php
/**
 * Joomla 5/6 SportsManagement administrator helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Read administrator extra-field definitions and current item values.
 */
final class ExtraFieldsReadHelper
{
    /**
     * Resolve the Joomla administrator application for generic error notices.
     *
     * @return CMSApplicationInterface Active administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Load field definitions and saved values for one administrator entity.
     *
     * Selects only the supported backend/frontend template column and uses
     * exact template names rather than SQL LIKE wildcard matching. Invalid
     * IDs or template settings return no records without querying the DB.
     * Parameter binding prevents accidental type coercion in join predicates
     * and ensures consistent query preparation on Joomla database drivers.
     *
     * @param int $itemId Entity identifier whose extra fields are requested.
     * @param string $view Exact extra-field template/view identifier.
     * @param string $template Either backend or frontend.
     * @param DatabaseInterface|null $database Optional injected Joomla driver.
     * @return array<int,object> Ordered extra fields with optional item values.
     */
    public function getFields(
        int $itemId,
        string $view,
        string $template = 'backend',
        ?DatabaseInterface $database = null
    ): array {
        if ($itemId <= 0 || $view === '' || trim($view) !== $view
            || strlen($view) > 100
            || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $view) !== 1
            || !in_array($template, ['backend', 'frontend'], true)) {
            return [];
        }

        try {
            $db = (new SportsManagementDatabaseResolver())->resolve(null, $database);
            $templateColumn = 'template_' . $template;
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('ef') . '.*',
                    $db->quoteName('ev.fieldvalue', 'fvalue'),
                    $db->quoteName('ev.id', 'value_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev')
                    . ' ON ' . $db->quoteName('ef.id') . ' = ' . $db->quoteName('ev.field_id')
                    . ' AND ' . $db->quoteName('ev.jl_id') . ' = :extraFieldItemId'
                )
                ->where($db->quoteName('ef.' . $templateColumn) . ' = :extraFieldTemplate')
                ->bind(':extraFieldItemId', $itemId, ParameterType::INTEGER)
                ->bind(':extraFieldTemplate', $view, ParameterType::STRING)
                ->order($db->quoteName('ef.ordering') . ' ASC');

            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    /**
     * Log database failures privately and display a generic Joomla warning.
     * Exception classes are logged without SQL statements or credentials
     * that some database drivers include in exception messages.
     *
     * @param \Throwable $e Error thrown while building or executing the query.
     * @return void
     */
    private function reportDatabaseError(\Throwable $e): void
    {
        // Keep SQL statements, connection details and filesystem paths in
        // the server log instead of exposing exception text in administrator HTML.
        \Joomla\CMS\Log\Log::add(
            __METHOD__ . ': extra-field lookup failed (' . get_class($e) . ').',
            \Joomla\CMS\Log\Log::ERROR,
            'com_sportsmanagement'
        );

        $app = self::administratorApplication();
        $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
    }
}
