<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Read configured backend extra fields without loading the legacy admin helper. */
final class ExtraFieldsReader
{
    public function load(DatabaseInterface $db, int $itemId, string $template): array
    {
        $template = preg_replace('/[^A-Za-z0-9_-]/', '', trim($template)) ?: '';

        if ($itemId <= 0 || $template === '') {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                'ef.*',
                $db->quoteName('ev.fieldvalue', 'fvalue'),
                $db->quoteName('ev.id', 'value_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev')
                . ' ON ' . $db->quoteName('ev.field_id') . ' = ' . $db->quoteName('ef.id')
                . ' AND ' . $db->quoteName('ev.jl_id') . ' = ' . $itemId
            )
            ->where($db->quoteName('ef.template_backend') . ' = ' . $db->quote($template))
            ->order($db->quoteName('ef.ordering'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
