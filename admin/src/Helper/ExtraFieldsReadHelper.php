<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Read administrator extra-field definitions and current item values.
 */
final class ExtraFieldsReadHelper
{
    /** @return array<int, object> */
    public function getFields(
        int $itemId,
        string $view,
        string $template = 'backend',
        ?DatabaseInterface $database = null
    ): array {
        $view = trim($view);
        $template = preg_replace('/[^A-Za-z0-9_]/', '', trim($template)) ?: 'backend';

        if ($itemId <= 0 || $view === '') {
            return [];
        }

        $db = (new SportsManagementDatabaseResolver())->resolve(null, $database);
        $templateColumn = 'template_' . $template;
        $query = $db->getQuery(true)
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
                . ' AND ' . $db->quoteName('ev.jl_id') . ' = ' . $itemId
            )
            ->where($db->quoteName('ef.' . $templateColumn) . ' LIKE ' . $db->quote($view))
            ->order($db->quoteName('ef.ordering') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    private function reportDatabaseError(\Throwable $e): void
    {
        $app = Factory::getApplication();
        $app->enqueueMessage(
            Text::sprintf(
                'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                $e->getCode(),
                $e->getMessage()
            ),
            'error'
        );
    }
}
