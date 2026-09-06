<?php
/**
 * Native Joomla 5/6 table implementation for SportsManagement persons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PersonTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_person', 'id', $db);
    }

    public function check()
    {
        $firstname = trim((string) ($this->firstname ?? ''));
        $lastname = trim((string) ($this->lastname ?? ''));

        if ($firstname === '' && $lastname === '') {
            $this->setError(Text::_('ERROR FIRSTNAME OR LASTNAME REQUIRED'));
            return false;
        }

        $alias = OutputFilter::stringURLSafe(trim($firstname . ' ' . $lastname));
        $this->alias = trim((string) ($this->alias ?? '')) === ''
            ? $alias
            : OutputFilter::stringURLSafe((string) $this->alias);

        return true;
    }
}
