(() => {
    const root = document.getElementById('seo-dashboard');
    if (!root || root.dataset.connected !== '1') return;
    const el = id => document.getElementById('seo-' + id);
    const fmt = (n, digits = 0) => Number(n ?? 0).toLocaleString('fr-FR', { maximumFractionDigits: digits });
    let generation = 0;
    async function get(report, params = '') {
        const response = await fetch(`${root.dataset.api}/${report}?${params}`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || `Erreur ${response.status}. Réessayez plus tard.`);
        return data;
    }
    function table(target, headers, rows) {
        target.replaceChildren();
        if (!rows.length) { target.textContent = 'Aucune donnée disponible.'; return; }
        const t = document.createElement('table'); t.className = 'w-full text-left';
        const head = t.createTHead().insertRow();
        headers.forEach(label => { const th = document.createElement('th'); th.textContent = label; th.className = 'p-2 border-b text-slate-500'; th.scope = 'col'; head.append(th); });
        const body = t.createTBody();
        rows.forEach(values => { const tr = body.insertRow(); values.forEach(value => { const td = tr.insertCell(); td.textContent = value; td.className = 'p-2 border-b break-words'; }); });
        target.append(t);
    }
    function chart(target, rows, series) {
        target.replaceChildren();
        if (!rows.length) { target.textContent = 'Aucune donnée disponible.'; return; }
        rows = [...rows].sort((a, b) => a.keys[0].localeCompare(b.keys[0]));
        const position = series[0][0] === 'position';
        const ns = 'http://www.w3.org/2000/svg';
        const wrap = document.createElement('div'); wrap.className = 'seo-plot';
        const svg = document.createElementNS(ns, 'svg');
        const legend = document.createElement('div'); legend.className = 'seo-chart-legend';
        series.forEach(([, label, color]) => { const item = document.createElement('span'); item.textContent = label; item.style.color = color; legend.append(item); });
        target.append(legend, wrap);
        const tooltip = document.createElement('div'); tooltip.className = 'seo-chart-tooltip'; tooltip.hidden = true;
        wrap.append(svg, tooltip);
        const dateLabel = date => new Date(date + 'T00:00:00Z').toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', timeZone: 'UTC' });
        const values = rows.flatMap(r => series.map(([key]) => Number(r[key])));
        // A single count axis preserves the actual relationship between the two metrics.
        const peak = Math.max(...values);
        const floor = position ? Math.min(...values) : 0;
        const span = position ? Math.max(1, peak - floor) : Math.max(1, peak);
        const rawStep = span / 4;
        const magnitude = 10 ** Math.floor(Math.log10(rawStep));
        const step = Math.max(position ? 0.1 : 1, [1, 2, 2.5, 5, 10].find(n => n * magnitude >= rawStep) * magnitude);
        const low = position ? Math.max(0, Math.floor((floor - span * .12) / step) * step) : 0;
        const high = Math.max(low + step, Math.ceil((peak + (position ? span * .12 : 0)) / step) * step);
        const details = document.createElement('details'); const summary = document.createElement('summary'); summary.textContent = 'Voir les données quotidiennes'; details.append(summary);
        const daily = document.createElement('div');
        table(daily, ['Date', ...series.map(s => s[1])], rows.map(r => [r.keys[0], ...series.map(s => fmt(r[s[0]], 2))])); details.append(daily);
        const note = document.createElement('p'); note.className = 'seo-chart-note';
        note.textContent = `${rows.length} jour${rows.length > 1 ? 's' : ''} avec données · ${dateLabel(rows[0].keys[0])} – ${dateLabel(rows.at(-1).keys[0])} · jours absents non interpolés`;
        target.append(note, details);
        function draw() {
            const width = Math.max(260, wrap.clientWidth), height = 180;
            const left = 44, right = width - 14, top = 23, bottom = height - 28;
            svg.replaceChildren(); svg.setAttribute('viewBox', `0 0 ${width} ${height}`); svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', `${series.map(s => s[1]).join(' et ')} : ${rows.length} jours, valeurs réelles de ${fmt(low, 2)} à ${fmt(high, 2)}. Données quotidiennes dans le tableau ci-dessous.`);
            function add(tag, attrs, text) {
                const node = document.createElementNS(ns, tag);
                Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, value));
                if (text !== undefined) node.textContent = text;
                svg.append(node); return node;
            }
            const y = value => bottom - (value - low) / (high - low) * (bottom - top);
            const first = Date.parse(rows[0].keys[0]), last = Date.parse(rows.at(-1).keys[0]);
            const spacing = (right - left) / Math.max(1, (last - first) / 86400000 + 1);
            const x = row => last === first ? (left + right) / 2 : left + spacing / 2 + (Date.parse(row.keys[0]) - first) / (last - first) * (right - left - spacing);
            add('text', { x: left, y: 11, fill: '#647886', 'font-size': 10 }, position ? 'Position' : 'Nombre');
            const tickCount = Math.round((high - low) / step);
            for (let i = 0; i <= tickCount; i++) {
                const value = low + i * step;
                add('line', { x1: left, x2: right, y1: y(value), y2: y(value), stroke: '#e5edf2' });
                add('text', { x: left - 8, y: y(value) + 3, 'text-anchor': 'end', fill: '#718592', 'font-size': 10 }, fmt(value, 2));
            }
            const maxLabels = Math.max(2, Math.floor((right - left) / 68));
            const labelCount = Math.min(rows.length, maxLabels);
            const labelIndices = new Set(Array.from({ length: labelCount }, (_, i) => Math.round(i * (rows.length - 1) / Math.max(1, labelCount - 1))));
            rows.forEach((row, index) => {
                if (labelIndices.has(index)) {
                    add('line', { x1: x(row), x2: x(row), y1: top, y2: bottom, stroke: '#f0f4f7', 'stroke-dasharray': '2 3' });
                    add('text', { x: x(row), y: bottom + 19, 'text-anchor': 'middle', fill: '#718592', 'font-size': 10 }, dateLabel(row.keys[0]));
                }
            });
            if (position) {
                // Split at missing dates rather than imply measured values between them.
                let segment = [];
                const flush = () => { if (segment.length > 1) add('polyline', { points: segment.map(r => `${x(r)},${y(Number(r.position))}`).join(' '), fill: 'none', stroke: series[0][2], 'stroke-width': 2, 'stroke-linejoin': 'round' }); segment = []; };
                rows.forEach((row, i) => { if (i && Date.parse(row.keys[0]) - Date.parse(rows[i - 1].keys[0]) > 86400000) flush(); segment.push(row); }); flush();
            }
            rows.forEach(row => {
                series.forEach(([key, label, color], i) => {
                    const value = Number(row[key]);
                    const barWidth = Math.max(.5, Math.min(16, spacing * .32));
                    const mark = position
                        ? add('circle', { cx: x(row), cy: y(value), r: rows.length > 90 ? 2 : 3.5, fill: color, stroke: '#fff', 'stroke-width': 1 })
                        : add('rect', { x: x(row) + (i === 0 ? -barWidth - 1 : 1), y: value ? y(value) : bottom - 1, width: barWidth, height: value ? bottom - y(value) : 1, fill: color, rx: 1 });
                    const title = document.createElementNS(ns, 'title'); title.textContent = `${row.keys[0]} · ${label} : ${fmt(value, 2)}`; mark.append(title);
                });
                const hit = add('rect', { x: x(row) - Math.max(5, spacing / 2), y: top, width: Math.max(10, spacing), height: bottom - top, fill: 'transparent' });
                hit.setAttribute('tabindex', '0');
                const description = `${row.keys[0]} · ${series.map(([key, label]) => `${label} : ${fmt(row[key], 2)}`).join(' · ')}`;
                hit.setAttribute('aria-label', description);
                const show = () => { tooltip.textContent = description; tooltip.hidden = false; tooltip.style.left = `${Math.max(0, Math.min(width - 210, x(row) - 80))}px`; };
                hit.addEventListener('mouseenter', show); hit.addEventListener('focus', show);
                hit.addEventListener('mouseleave', () => tooltip.hidden = true); hit.addEventListener('blur', () => tooltip.hidden = true);
            });
        }
        draw();
        if (target._chartObserver) target._chartObserver.disconnect();
        target._chartObserver = new ResizeObserver(() => { if (!wrap.isConnected) { target._chartObserver.disconnect(); return; } draw(); });
        target._chartObserver.observe(wrap);
    }

    async function load(refresh = false) {
        if (!el('site').value) return;
        const current = ++generation;
        const params = new URLSearchParams({ site: el('site').value, start: el('start').value, end: el('end').value, type: 'web', data_state: 'all', refresh: refresh ? '1' : '0' });
        el('diagnostics').textContent = 'Chargement de la trace…';
        el('status').textContent = 'Chargement des rapports…'; el('submit').disabled = true; el('refresh').disabled = true;
        el('refresh').setAttribute('aria-busy', refresh ? 'true' : 'false');
        el('refresh-label').textContent = refresh ? 'Actualisation…' : 'Actualiser';
        ['clicks', 'impressions', 'ctr', 'position'].forEach(k => el(k).textContent = '—');
        ['traffic', 'position-chart', 'queries', 'pages', 'countries', 'devices', 'sitemaps'].forEach(k => el(k).textContent = 'Chargement…');
        let failed = false;
        await Promise.all(['overview', 'queries', 'pages', 'countries', 'devices', 'sitemaps'].map(async report => {
            try {
                const data = await get(report, params);
                if (current !== generation) return;
                if (report === 'overview') {
                    ['clicks', 'impressions', 'ctr', 'position'].forEach(k => el(k).textContent = data.totals ? fmt(k === 'ctr' ? data.totals[k] * 100 : data.totals[k], ['ctr', 'position'].includes(k) ? 2 : 0) + (k === 'ctr' ? ' %' : '') : '—');
                    el('diagnostics').textContent = JSON.stringify({ ...data._meta, backend_totals: data.totals, displayed: Object.fromEntries(['clicks', 'impressions', 'ctr', 'position'].map(k => [k, el(k).textContent])), display_rules: 'Clics/impressions : valeurs Google ; CTR : ratio Google × 100 ; position : valeur Google. Arrondi visuel à 2 décimales.' }, null, 2);
                    chart(el('traffic'), data.rows, [['clicks', 'Clics', '#2D7080'], ['impressions', 'Impressions', '#E7B84B']]);
                    chart(el('position-chart'), data.rows, [['position', 'Position moyenne', '#0B2638']]);
                } else if (report === 'sitemaps') {
                    table(el(report), ['Sitemap', 'État', 'Erreurs', 'Alertes', 'Dernière lecture'], (data.sitemap || []).map(s => [s.path, s.isPending ? 'En attente' : Number(s.errors) > 0 ? 'Erreur' : Number(s.warnings) > 0 ? 'Avertissement' : 'Traité', fmt(s.errors), fmt(s.warnings), s.lastDownloaded || '—']));
                } else {
                    table(el(report), [{ queries: 'Requête', pages: 'Page', countries: 'Pays', devices: 'Appareil' }[report], 'Clics', 'Impressions', 'CTR', 'Position'], (data.rows || []).map(r => [r.keys[0], fmt(r.clicks), fmt(r.impressions), fmt(r.ctr * 100, 2) + ' %', fmt(r.position, 2)]));
                }
            } catch (error) {
                if (current !== generation) return;
                failed = true;
                if (report === 'overview') { el('traffic').textContent = error.message; el('position-chart').textContent = error.message; el('diagnostics').textContent = error.message; }
                else el(report).textContent = error.message;
            }
        }));
        if (current === generation) {
            el('submit').disabled = false; el('refresh').disabled = false;
            el('refresh').setAttribute('aria-busy', 'false');
            el('refresh-label').textContent = 'Actualiser';
            el('status').textContent = failed ? 'Certains rapports sont indisponibles. Consultez les messages ci-dessous.' : refresh ? 'Les dernières données Google ont été récupérées.' : 'Rapports chargés.';
        }
    }
    document.getElementById('seo-filters').addEventListener('submit', event => { event.preventDefault(); load(); });
    el('refresh').addEventListener('click', () => load(true));
    get('sites').then(data => {
        el('site').replaceChildren();
        (data.siteEntry || []).filter(s => s.permissionLevel !== 'siteUnverifiedUser').forEach(s => { const option = document.createElement('option'); option.value = s.siteUrl; option.textContent = s.siteUrl; el('site').append(option); });
        if (!el('site').options.length) { el('status').textContent = 'Aucune propriété accessible dans ce compte Google.'; return; }
        load();
    }).catch(error => { el('site').replaceChildren(); el('status').textContent = error.message; });
})();
