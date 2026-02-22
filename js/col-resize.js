(function () {
    'use strict';

    var STORAGE_KEY_PREFIX = 'smd-col-widths-';

    function storageKey(colCount) {
        return STORAGE_KEY_PREFIX + colCount;
    }

    function saveWidths(ths) {
        var key = storageKey(ths.length);
        var widths = Array.from(ths).map(function (th) {
            return th.getBoundingClientRect().width;
        });
        try { localStorage.setItem(key, JSON.stringify(widths)); } catch (e) {}
    }

    function loadWidths(ths) {
        var key = storageKey(ths.length);
        try {
            var saved = localStorage.getItem(key);
            return saved ? JSON.parse(saved) : null;
        } catch (e) { return null; }
    }

    function addResetButton(ths, table) {
        var old = ths[0].querySelector('.col-reset-btn');
        if (old) old.remove();

        var btn = document.createElement('button');
        btn.className = 'col-reset-btn';
        btn.title = 'Restablecer anchos de columna';
        btn.innerHTML = '<i class="fa fa-undo"></i>';

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            try { localStorage.removeItem(storageKey(ths.length)); } catch (e2) {}
            table.style.tableLayout = '';
            ths.forEach(function (th) { th.style.width = ''; });
            // Tras reflow auto, recapturar anchos y volver a fixed
            requestAnimationFrame(function () {
                var autoW = Array.from(ths).map(function (th) { return th.getBoundingClientRect().width; });
                table.style.tableLayout = 'fixed';
                ths.forEach(function (th, i) { th.style.width = autoW[i] + 'px'; });
            });
        });

        ths[0].appendChild(btn);
    }

    function addResizer(th, ths, table) {
        var old = th.querySelector('.col-resizer');
        if (old) old.remove();

        var resizer = document.createElement('div');
        resizer.className = 'col-resizer';
        th.appendChild(resizer);

        var startX, startW;

        resizer.addEventListener('mousedown', function (e) {
            // La tabla ya está en fixed layout — sin reflow, sin offset
            startX = e.clientX;
            startW = th.getBoundingClientRect().width;

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
            resizer.classList.add('resizing');
            document.body.style.cursor = 'col-resize';
            e.preventDefault();
        });

        function onMove(e) {
            var w = Math.max(40, startW + (e.clientX - startX));
            th.style.width = w + 'px';
        }

        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            resizer.classList.remove('resizing');
            document.body.style.cursor = '';
            saveWidths(ths);
        }
    }

    function initResizable() {
        var table = document.getElementById('tblBoard');
        if (!table) return;
        var ths = Array.from(table.querySelectorAll('thead th'));
        if (!ths.length) return;

        var saved = loadWidths(ths);
        if (saved) {
            table.style.tableLayout = 'fixed';
            ths.forEach(function (th, i) { if (saved[i]) th.style.width = saved[i] + 'px'; });
        } else {
            // Inicializar siempre en fixed — evita reflow en mousedown y flash futuro
            var autoW = ths.map(function (th) { return th.getBoundingClientRect().width; });
            table.style.tableLayout = 'fixed';
            ths.forEach(function (th, i) { th.style.width = autoW[i] + 'px'; });
        }

        ths.forEach(function (th) { addResizer(th, ths, table); });
        addResetButton(ths, table);
    }

    function watchAndInit() {
        var table = document.getElementById('tblBoard');
        if (!table) return;
        var firstTh = table.querySelector('thead th');
        if (firstTh && !firstTh.querySelector('.col-resizer')) {
            initResizable();
        }
    }

    // MutationObserver: dispara antes del primer paint → elimina flash de recarga
    var monitorEl = document.getElementById('monitor-data');
    if (monitorEl) {
        new MutationObserver(watchAndInit).observe(monitorEl, { childList: true, subtree: true });
    }

    // Fallback setInterval reducido a 100ms
    setInterval(watchAndInit, 100);
}());
