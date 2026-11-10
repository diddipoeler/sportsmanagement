/**
 * Joomla 5/6 read-only SportsManagement tournament tree.
 *
 * Data is JSON in a non-executable script element; team labels are inserted
 * as text, avoiding the legacy inline JavaScript/HTML interpolation path.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (window, document) {
    'use strict';

    /**
     * Initialise each Joomla tournament bracket once after assets have loaded.
     *
     * @return {void}
     */
    function init() {
        const $ = window.jQuery;

        if (!$ || !$.fn || typeof $.fn.bracket !== 'function') {
            return;
        }

        document.querySelectorAll('[data-jsm-tournament-tree]').forEach(function (root) {
            if (root.dataset.jsmTournamentReady === '1') {
                return;
            }

            const dataNode = root.querySelector('[data-jsm-tournament-data]');
            const target = root.querySelector('[data-jsm-tournament-bracket]');

            if (!dataNode || !target) {
                return;
            }

            let payload;

            try {
                payload = JSON.parse(dataNode.textContent || '');
            } catch (_) {
                return;
            }

            if (!payload || !Array.isArray(payload.teams) || payload.teams.length === 0
                || !Array.isArray(payload.results) || !Array.isArray(payload.rounds)) {
                return;
            }

            root.dataset.jsmTournamentReady = '1';

            try {
                $(target).bracket({
                    init: {teams: payload.teams, results: payload.results},
                    roundsheader: {roundsheader: payload.rounds},
                    decorator: {
                        // This is a read-only public tournament display.
                        edit: function () {},
                        render: function (container, name) {
                            container.text(typeof name === 'string' ? name : '');
                        }
                    }
                });
            } catch (_) {
                // A malformed historical tournament must not break the page.
                root.dataset.jsmTournamentReady = '0';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
