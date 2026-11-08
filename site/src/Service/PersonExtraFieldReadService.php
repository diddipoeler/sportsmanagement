<?php
/**
 * Native Joomla 5/6 read service for frontend person extra fields.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Joomla 5/6 read service for SportsManagement user extra fields used by the frontend person editor.
 */
final class PersonExtraFieldReadService
{
    /**
     * Create the frontend custom-field reader with an injected database connection.
     * Stores Joomla's read-only database dependency for person-editing form definitions and values.
     *
     * @param DatabaseInterface $database Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Check whether the requested frontend or backend template has custom fields.
     * Validates the template-column whitelist, then performs a bounded field-definition lookup using a bound template identifier.
     *
     * @param string $template Extra-field template category: frontend or backend.
     * @param string $templateName Name of the template whose fields are requested.
     * @return bool True when at least one matching custom-field definition exists.
     */
    public function hasFields(string $template = 'frontend', string $templateName = 'clubinfo'): bool
    {
        $column = $this->templateColumn($template);
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('ef.id'))
            ->from($this->database->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
            ->where($this->database->quoteName('ef.' . $column) . ' LIKE :templateName')
            ->bind(':templateName', $templateName, ParameterType::STRING);

        $this->database->setQuery($query, 0, 1);

        return (bool) $this->database->loadResult();
    }

    /**
     * Read extra-field definitions and stored values for one person.
     * Left-joins optional person-specific values to every matching field definition, keeping empty values and returning rows in configured ordering.
     *
     * @param int $personId Person identifier whose saved extra-field values should be loaded.
     * @param string $template Extra-field category, frontend or backend.
     * @param string $templateName Template identifier matched against field definitions.
     * @return array<int,object> Ordered extra-field definitions with optional values.
     */
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
                . ' AND ' . $this->database->quoteName('ev.jl_id') . ' = :personId'
                . ')'
            )
            ->where($this->database->quoteName('ef.' . $column) . ' LIKE :templateName')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':templateName', $templateName, ParameterType::STRING)
            ->order($this->database->quoteName('ef.ordering'));

        $this->database->setQuery($query);

        return (array) $this->database->loadObjectList();
    }

    /**
     * Resolve a safe database column for extra-field template filtering.
     * Allows only frontend or backend values so callers cannot inject arbitrary column names into Joomla's query builder.
     *
     * @param string $template Requested template category.
     * @return string Whitelisted template_frontend or template_backend column name.
     */
    private function templateColumn(string $template): string
    {
        if (!in_array($template, ['frontend', 'backend'], true)) {
            throw new \InvalidArgumentException('Unsupported extra-field template.');
        }

        return 'template_' . $template;
    }
}
