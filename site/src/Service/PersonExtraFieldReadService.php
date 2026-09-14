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
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

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

    private function templateColumn(string $template): string
    {
        if (!in_array($template, ['frontend', 'backend'], true)) {
            throw new \InvalidArgumentException('Unsupported extra-field template.');
        }

        return 'template_' . $template;
    }
}
