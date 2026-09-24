<?php
/**
 * Legacy no-op table compatibility class for quote text-file administration.
 *
 * The quote text-files view is filesystem-backed and has never represented a
 * database table. This class remains only for third-party code that still
 * requests the historical table class name.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

if (!class_exists('sportsmanagementTablesmquotestxt', false)) {
    class sportsmanagementTablesmquotestxt
    {
        public function __construct(&$db)
        {
            // Intentionally no-op: quote text files are not stored in a DB table.
        }
    }
}
