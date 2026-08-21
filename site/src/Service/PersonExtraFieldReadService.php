<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Joomla 5/6 read service for SportsManagement user extra fields used by the frontend person editor.
 */
final class PersonExtraFieldReadService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    public function hasFields(string $template = 'frontend', string $templateName = 'clubinfo'): bool
    {
        $column = $this->templateColumn($template);
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('ef.id'))
            ->from($this->database->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
            ->where($this->database->quoteName('ef.' . $column) . ' LIKE ' . $this->database->quote($templateName));

        $this->database->setQuery($query, 0, 1);

        return (bool) $this->database->loadResult();
    }

    public function fields(
        int $personId,
        string $template = 'frontend',
        string $templateName = 'clubinfo'
    ): array {
        if ($personId <= 0) {
            return [];
        }

        $column = $this->templateColumn($template);
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('ef') . '.*',
                $this->database->quoteName('ev.fieldvalue', 'fvalue'),
                $this->database->quoteName('ev.id', 'value_id'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
            ->join(
                'LEFT',
                $this->database->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev')
                . ' ON ('
                . $this->database->quoteName('ef.id') . ' = ' . $this->database->quoteName('ev.field_id')
                . ' AND ' . $this->database->quoteName('ev.jl_id') . ' = ' . $personId
                . ')'
            )
            ->where($this->database->quoteName('ef.' . $column) . ' LIKE ' . $this->database->quote($templateName))
            ->order($this->database->quoteName('ef.ordering'));

        $this->database->setQuery($query);

        return (array) $this->database->loadObjectList();
    }

    private function templateColumn(string $template): string
    {
        if (!in_array($template, ['frontend', 'backend'], true)) {
            throw new \InvalidArgumentException('Unsupported extra-field template.');
        }

        return 'template_' . $template;
    }
}
