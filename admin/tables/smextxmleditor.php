<?php
/**
 * Legacy SportsManagement extended XML editor table compatibility class.
 *
 * This historical table does not map to a database table. It is retained only
 * for third-party callers which still request the legacy class name.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       smextxmleditor.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

/**
 * Legacy no-op table retained for compatibility.
 */
class sportsmanagementTablesmextxmleditor extends JSMTable
{
    public function __construct(&$db)
    {
        // Intentionally no parent constructor: the historical XML editor table
        // never represented a database table.
    }
}
