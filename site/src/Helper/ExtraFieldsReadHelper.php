<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Read configured frontend extra fields and their current item values. */
final class ExtraFieldsReadHelper
{
    public static function hasFields(DatabaseInterface $db, string $templateName): bool
    {
        $templateName = self::templateName($templateName);

        if ($templateName === '') {
            return false;
        }

        try {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
                ->where($db->quoteName('template_frontend') . ' = ' . $db->quote($templateName));
            $db->setQuery($query);

            return (int) $db->loadResult() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<int, object> */
    public static function load(DatabaseInterface $db, int $itemId, string $templateName): array
    {
        $templateName = self::templateName($templateName);

        if ($itemId <= 0 || $templateName === '') {
            return [];
        }

        try {
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
                ->where($db->quoteName('ef.template_frontend') . ' = ' . $db->quote($templateName))
                ->order($db->quoteName('ef.ordering') . ' ASC');
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private static function templateName(string $templateName): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', trim($templateName)) ?: '';
    }
}
