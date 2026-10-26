(() => {
    const root = document.getElementById('ga-dashboard');
    if (!root || root.dataset.connected !== '1') return;
    const el = key => document.getElementById('ga-' + key);
    const number = (value, digits = 0) => Number(value || 0).toLocaleString('fr-FR', { maximumFractionDigits: digits });
    let days = 28, generation = 0;
    const observers = new Map();
    function dailySeries(rows, period, meta) {
        // Anchor cached data to its collection date in the GA4 property's timezone.
        const parts = new Intl.DateTimeFormat('en-CA', { timeZone: meta.timezone || 'UTC', year: 'numeric', month: '2-digit', day: '2-digit' })
            .formatToParts(new Date(meta.fetched_at));
        const part = name => Number(parts.find(p => p.type === name).value);
        const end = Date.UTC(part('year'), part('month') - 1, part('day'));
        const start = end - period * 86400000;
        const existing = new Map(rows.map(row => [row.date, row]));
        const result = [];
        // Match the API exactly: NdaysAgo through today, both bounds inclusive.
        for (let timestamp = start; timestamp <= end; timestamp += 86400000) {
            const date = new Date(timestamp).toISOString().slice(0, 10).replaceAll('-', '');
            result.push({ date, activeUsers: 0, sessions: 0, screenPageViews: 0, ...existing.get(date), missing: !existing.has(date) });
        }
        return result;
    }
    function table(target, columns, rows) {
        target.replaceChildren();
        if (!rows.length) { target.textContent = 'Aucune donnée sur cette période.'; return; }
        const t = document.createElement('table'); const header = t.createTHead().insertRow();
        columns.forEach(([, label]) => { const th = document.createElement('th'); th.scope = 'col'; th.textContent = label; header.append(th); });
        const body = t.createTBody();
        rows.forEach(row => { const tr = body.insertRow(); columns.forEach(([key, , format]) => { const td = tr.insertCell(); td.textContent = format ? format(row[key]) : String(row[key] ?? '—'); }); });
        target.append(t);
    }
    function chart(target, rows, series) {
        observers.get(target)?.disconnect(); target.replaceChildren();
        if (!rows.length) { target.textContent = 'Aucune donnée sur cette période.'; return; }
        const legend = document.createElement('div'); legend.className = 'ga-legend';
        series.forEach(([, title, color], index) => { const label = document.createElement('span'); label.style.color = color; label.className = index ? 'ga-series-dashed' : ''; label.textContent = title; legend.append(label); });
        const wrap = document.createElement('div'); wrap.className = 'ga-plot';
        const ns = 'http://www.w3.org/2000/svg'; const svg = document.createElementNS(ns, 'svg');
        const tooltip = document.createElement('div'); tooltip.className = 'ga-tooltip'; tooltip.hidden = true;
        wrap.append(svg, tooltip); target.append(legend, wrap);
        const date = value => `${value.slice(6, 8)}/${value.slice(4, 6)}`;
        const epoch = value => Date.parse(`${value.slice(0, 4)}-${value.slice(4, 6)}-${value.slice(6, 8)}T00:00:00Z`);
        const range = document.createElement('p'); range.className = 'ga-chart-range';
        range.textContent = `${date(rows[0].date)}/${rows[0].date.slice(0, 4)} → ${date(rows.at(-1).date)}/${rows.at(-1).date.slice(0, 4)} · jours sans données représentés à 0`;
        target.insertBefore(range, legend);
        function draw() {
            const width = Math.max(250, wrap.clientWidth), height = 190, left = 44, right = width - 12, top = 14, bottom = 160;
            svg.replaceChildren(); svg.setAttribute('viewBox', `0 0 ${width} ${height}`); svg.setAttribute('role', 'img'); svg.setAttribute('aria-label', series.map(s => s[1]).join(' et ') + ' par date, valeurs réelles. Tableau quotidien disponible ci-dessous.');
            function add(tag, attrs, text) { const node = document.createElementNS(ns, tag); Object.entries(attrs).forEach(([k, v]) => node.setAttribute(k, v)); if (text !== undefined) node.textContent = text; svg.append(node); return node; }
            const peak = Math.max(1, ...rows.flatMap(row => series.map(([key]) => Number(row[key]))));
            const magnitude = 10 ** Math.floor(Math.log10(peak / 4));
            const step = Math.max(1, [1, 2, 2.5, 5, 10].find(n => n * magnitude >= peak / 4) * magnitude);
            const max = Math.ceil(peak / step) * step;
            const first = epoch(rows[0].date), last = epoch(rows.at(-1).date);
            const x = row => first === last ? (left + right) / 2 : left + 4 + (epoch(row.date) - first) / (last - first) * (right - left - 8);
            const y = value => bottom - value / max * (bottom - top);
            for (let value = 0; value <= max; value += step) { add('line', { x1: left, x2: right, y1: y(value), y2: y(value), stroke: '#e7eef2' }); add('text', { x: left - 7, y: y(value) + 3, 'text-anchor': 'end', fill: '#7c909d', 'font-size': 10 }, number(value)); }
            const labels = Math.min(rows.length, Math.max(2, Math.floor((right - left) / 65)));
            const indices = new Set(Array.from({ length: labels }, (_, i) => Math.round(i * (rows.length - 1) / Math.max(1, labels - 1))));
            rows.forEach((row, i) => { if (indices.has(i)) { add('line', { x1: x(row), x2: x(row), y1: top, y2: bottom, stroke: '#f0f4f6', 'stroke-dasharray': '2 3' }); add('text', { x: x(row), y: 181, 'text-anchor': 'middle', fill: '#7c909d', 'font-size': 10 }, date(row.date)); } });
            series.forEach(([key, , color], seriesIndex) => {
                let segment = [];
                const flush = () => { if (segment.length > 1) add('polyline', { points: segment.map(row => `${x(row)},${y(row[key])}`).join(' '), fill: 'none', stroke: color, 'stroke-width': 2.5, 'stroke-linejoin': 'round', 'stroke-dasharray': seriesIndex ? '6 4' : 'none' }); segment = []; };
                rows.forEach((row, i) => { if (i && epoch(row.date) - epoch(rows[i - 1].date) > 86400000) flush(); segment.push(row); }); flush();
                rows.forEach(row => { if (row[key] > 0 || rows.length <= 8) add('circle', { cx: x(row), cy: y(row[key]), r: seriesIndex ? 2 : 3.5, fill: color, stroke: 'white', 'stroke-width': .6 }); });
            });
            const guide = add('line', { x1: left, x2: left, y1: top, y2: bottom, stroke: '#91a8b5', 'stroke-dasharray': '3 3', visibility: 'hidden' });
            const hoverDots = series.map(([, , color]) => add('circle', { r: 4, fill: color, stroke: 'white', 'stroke-width': 1, visibility: 'hidden' }));
            const hitWidth = (right - left - 8) / Math.max(1, rows.length - 1);
            rows.forEach(row => {
                const hitLeft = Math.max(left, x(row) - hitWidth / 2);
                const hit = add('rect', { x: hitLeft, y: top, width: Math.min(right, x(row) + hitWidth / 2) - hitLeft, height: bottom - top, fill: 'transparent', tabindex: 0 });
                const value = `${date(row.date)}/${row.date.slice(0, 4)}\n${series.map(([key, title]) => `${title} : ${number(row[key])}`).join('\n')}${row.missing ? '\nAucune ligne GA4 : valeur affichée à 0' : ''}`;
                hit.setAttribute('aria-label', value);
                const show = () => {
                    tooltip.hidden = false; tooltip.textContent = value; tooltip.style.left = `${Math.max(0, Math.min(width - 220, x(row) - 80))}px`;
                    guide.setAttribute('x1', x(row)); guide.setAttribute('x2', x(row)); guide.setAttribute('visibility', 'visible');
                    hoverDots.forEach((dot, i) => { dot.setAttribute('cx', x(row)); dot.setAttribute('cy', y(row[series[i][0]])); dot.setAttribute('visibility', 'visible'); });
                };
                ['mouseenter', 'focus', 'touchstart'].forEach(event => hit.addEventListener(event, show));
                ['mouseleave', 'blur'].forEach(event => hit.addEventListener(event, () => { tooltip.hidden = true; guide.setAttribute('visibility', 'hidden'); hoverDots.forEach(dot => dot.setAttribute('visibility', 'hidden')); }));
            });
        }
        draw(); const observer = new ResizeObserver(draw); observer.observe(wrap); observers.set(target, observer);
        const details = document.createElement('details'); details.className = 'ga-chart-details'; const summary = document.createElement('summary'); summary.textContent = 'Voir les données quotidiennes';
        const daily = document.createElement('div'); daily.className = 'ga-table-wrap'; table(daily, [['date', 'Date', date], ...series.map(([key, title]) => [key, title, number])], rows); details.append(summary, daily); target.append(details);
    }
    const definitions = {
        sources: [['sessionDefaultChannelGroup', 'Canal'], ['sessionSourceMedium', 'Source / support'], ['sessions', 'Sessions', number], ['activeUsers', 'Actifs', number], ['newUsers', 'Nouveaux', number]],
        campaigns: [['sessionCampaignName', 'Campagne'], ['sessionSourceMedium', 'Source / support'], ['sessionManualAdContent', 'Contenu UTM'], ['sessionManualTerm', 'Terme UTM'], ['sessions', 'Sessions', number], ['activeUsers', 'Actifs', number], ['keyEvents', 'Événements clés', number]],
        events: [['eventName', 'Événement'], ['eventCount', 'Total', number], ['totalUsers', 'Utilisateurs', number], ['keyEvents', 'Événements clés', number]],
        pages: [['pagePath', 'Page'], ['screenPageViews', 'Vues', number], ['activeUsers', 'Actifs', number], ['engagementRate', 'Engagement', n => number(n * 100, 1) + ' %']],
        countries: [['country', 'Pays'], ['activeUsers', 'Actifs', number], ['sessions', 'Sessions', number], ['screenPageViews', 'Vues', number]],
        devices: [['deviceCategory', 'Appareil'], ['activeUsers', 'Actifs', number], ['sessions', 'Sessions', number], ['screenPageViews', 'Vues', number]],
    };
    const kpis = ['activeUsers', 'sessions', 'newUsers', 'returningUsers', 'screenPageViews', 'conversions'];
    async function load(refresh = false) {
        const run = ++generation; const period = days; let failed = 0, overviewMeta = null, limited = false;
        observers.forEach(observer => observer.disconnect()); observers.clear();
        kpis.forEach(key => el(key).textContent = '—');
        ['countries', 'devices'].forEach(key => window.DalyTracBreakdown.destroy(el(key)));
        ['traffic', 'views-chart', ...Object.keys(definitions)].forEach(key => el(key).textContent = 'Chargement…');
        el('refresh').disabled = true; el('refresh').setAttribute('aria-busy', 'true'); el('status').textContent = 'Chargement des données Google Analytics…';
        await Promise.all(['kpis', 'traffic', ...Object.keys(definitions)].map(async report => {
            try {
                const response = await fetch(`${root.dataset.api}/${report}?${new URLSearchParams({ days: period, refresh: refresh ? 1 : 0 })}`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Rapport indisponible. Réessayez plus tard.');
                if (run !== generation) return;
                const quality = data.meta?.data_quality || {};
                limited ||= Boolean(quality.subjectToThresholding || quality.dataLossFromOtherRow || quality.samplingMetadatas?.length || data.meta?.truncated);
                if (report === 'kpis') {
                    overviewMeta = data.meta; const t = data.totals;
                    kpis.forEach(key => el(key).textContent = number(t[key]));
                } else if (report === 'traffic') {
                    const rows = dailySeries(data.rows, period, data.meta);
                    chart(el('traffic'), rows, [['activeUsers', 'Utilisateurs actifs', '#2D7080'], ['sessions', 'Sessions', '#E7B84B']]);
                    chart(el('views-chart'), rows, [['screenPageViews', 'Vues', '#0B2638']]);
                } else if (report === 'countries' || report === 'devices') {
                    const rows = data.rows.map(row => ({ ...row, label: window.DalyTracBreakdown.label(row[report === 'countries' ? 'country' : 'deviceCategory'], report === 'countries') }));
                    window.DalyTracBreakdown.render(el(report), rows, [
                        { key: 'activeUsers', label: 'Utilisateurs actifs' }, { key: 'sessions', label: 'Sessions' }, { key: 'screenPageViews', label: 'Pages vues' }
                    ], 'activeUsers');
                } else table(el(report), definitions[report], data.rows);
            } catch (error) {
                if (run !== generation) return; failed++;
                if (report === 'kpis') el('status').textContent = error.message;
                else { el(report).textContent = error.message; if (report === 'traffic') el('views-chart').textContent = error.message; }
            }
        }));
        if (run !== generation) return;
        el('refresh').disabled = false; el('refresh').setAttribute('aria-busy', 'false');
        if (overviewMeta) el('status').textContent = `${period} jours jusqu’à aujourd’hui · données récentes susceptibles d’évoluer · ${overviewMeta.timezone || 'fuseau de la propriété'} · ${overviewMeta.cache_hit ? 'données en cache' : 'données récupérées depuis Google'} · ${new Date(overviewMeta.fetched_at).toLocaleString('fr-FR')}${failed ? ' · Certains rapports sont indisponibles.' : ''}${limited ? ' · Google applique des seuils, un échantillonnage ou une limite de lignes à certains résultats.' : ''}`;
    }
    root.querySelectorAll('[data-days]').forEach(button => button.addEventListener('click', () => {
        days = Number(button.dataset.days); root.querySelectorAll('[data-days]').forEach(b => b.setAttribute('aria-pressed', String(b === button))); load();
    }));
    el('refresh').addEventListener('click', () => load(true)); load();
})();
