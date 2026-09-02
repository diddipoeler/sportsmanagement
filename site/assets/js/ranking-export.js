(() => {
    'use strict';

    const download = (content, type, filename) => {
        const url = URL.createObjectURL(new Blob([content], {type}));
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = filename;
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        URL.revokeObjectURL(url);
    };

    const exportExcel = (tables) => {
        const html = '<!doctype html><html><head><meta charset="utf-8"></head><body>'
            + tables.map((table) => table.outerHTML).join('<br>')
            + '</body></html>';

        download(html, 'application/vnd.ms-excel;charset=utf-8', 'ranking.xls');
    };

    const exportMediaWiki = (tables) => {
        const blocks = tables.map((table) => {
            const rows = Array.from(table.rows).map((row) => {
                const cells = Array.from(row.cells).map((cell) => cell.innerText.trim().replace(/\|/g, '&#124;'));
                return '|-\n' + cells.map((cell) => '| ' + cell).join('\n');
            });

            return '{| class="wikitable"\n' + rows.join('\n') + '\n|}';
        });

        download(blocks.join('\n\n'), 'text/plain;charset=utf-8', 'ranking.mediawiki.txt');
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const button = event.target.closest('[data-ranking-export]');
        if (!button) {
            return;
        }

        const root = button.closest('[data-jsm-ranking]');
        if (!root) {
            return;
        }

        const tables = Array.from(root.querySelectorAll('table.ranking-exportable'));
        if (tables.length === 0) {
            return;
        }

        if (button.dataset.rankingExport === 'excel') {
            exportExcel(tables);
        } else if (button.dataset.rankingExport === 'mediawiki') {
            exportMediaWiki(tables);
        }
    });
})();
