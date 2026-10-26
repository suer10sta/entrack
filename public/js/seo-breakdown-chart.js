(() => {
    const instances = new WeakMap();
    const ns = 'http://www.w3.org/2000/svg';
    const countryCodes = {"GUF":"GF","MCO":"MC","NCL":"NC","SHN":"SH","IRQ":"IQ","CXR":"CX","ARG":"AR","VNM":"VN","CCK":"CC","SXM":"SX","NGA":"NG","IND":"IN","COD":"CD","MDV":"MV","EST":"EE","BOL":"BO","MYS":"MY","ATG":"AG","BRA":"BR","KNA":"KN","NPL":"NP","MNP":"MP","TWN":"TW","WLF":"WF","HKG":"HK","LCA":"LC","PRY":"PY","TCA":"TC","HTI":"HT","ZAF":"ZA","SGP":"SG","NIC":"NI","SSD":"SS","DMA":"DM","RUS":"RU","GHA":"GH","MYT":"YT","CZE":"CZ","KHM":"KH","ECU":"EC","SYR":"SY","FRO":"FO","GBR":"GB","SEN":"SN","PYF":"PF","PHL":"PH","STP":"ST","HUN":"HU","SDN":"SD","BRB":"BB","FJI":"FJ","REU":"RE","TKM":"TM","GIB":"GI","NZL":"NZ","MSR":"MS","VCT":"VC","AZE":"AZ","DZA":"DZ","BLZ":"BZ","SVN":"SI","LSO":"LS","CUW":"CW","PRT":"PT","ZMB":"ZM","FSM":"FM","ETH":"ET","JPN":"JP","BEL":"BE","IOT":"IO","CHE":"CH","MUS":"MU","KAZ":"KZ","AND":"AD","MAC":"MO","UKR":"UA","NLD":"NL","PAK":"PK","ARE":"AE","BDI":"BI","GRD":"GD","KOR":"KR","POL":"PL","TKL":"TK","EGY":"EG","GEO":"GE","CUB":"CU","PCN":"PN","TUR":"TR","VEN":"VE","BIH":"BA","USA":"US","TTO":"TT","SUR":"SR","SOM":"SO","SJM":"SJ","KWT":"KW","BFA":"BF","ESP":"ES","CHN":"CN","COG":"CG","TUN":"TN","UGA":"UG","IDN":"ID","QAT":"QA","TJK":"TJ","KEN":"KE","LBR":"LR","PAN":"PA","ROU":"RO","GRL":"GL","CPV":"CV","ITA":"IT","TGO":"TG","KIR":"KI","MKD":"MK","BHS":"BS","LVA":"LV","ASM":"AS","NRU":"NR","URY":"UY","BWA":"BW","LUX":"LU","DJI":"DJ","PLW":"PW","TLS":"TL","BRN":"BN","IRN":"IR","CIV":"CI","MLI":"ML","NOR":"NO","TCD":"TD","CYP":"CY","GRC":"GR","JOR":"JO","OMN":"OM","SWE":"SE","MNE":"ME","AUS":"AU","SYC":"SC","LTU":"LT","SLV":"SV","CHL":"CL","MMR":"MM","GUY":"GY","PER":"PE","GNQ":"GQ","RWA":"RW","MRT":"MR","GMB":"GM","SAU":"SA","SWZ":"SZ","LKA":"LK","ISR":"IL","FIN":"FI","AUT":"AT","AFG":"AF","MLT":"MT","SRB":"RS","JEY":"JE","MDG":"MG","AIA":"AI","KGZ":"KG","ALB":"AL","FLK":"FK","VAT":"VA","AGO":"AO","TON":"TO","MDA":"MD","COK":"CK","SLE":"SL","WSM":"WS","JAM":"JM","LIE":"LI","DOM":"DO","TZA":"TZ","IMN":"IM","ISL":"IS","MEX":"MX","PRK":"KP","GAB":"GA","ARM":"AM","MNG":"MN","HND":"HN","THA":"TH","LBN":"LB","ABW":"AW","BLM":"BL","PNG":"PG","LBY":"LY","VGB":"VG","ZWE":"ZW","PRI":"PR","TUV":"TV","SPM":"PM","PSE":"PS","SLB":"SB","HRV":"HR","BGR":"BG","CYM":"KY","GIN":"GN","UZB":"UZ","MOZ":"MZ","BEN":"BJ","NFK":"NF","BLR":"BY","FRA":"FR","DNK":"DK","NIU":"NU","VIR":"VI","CAF":"CF","GLP":"GP","LAO":"LA","MWI":"MW","IRL":"IE","ERI":"ER","ALA":"AX","GNB":"GW","DEU":"DE","MTQ":"MQ","BHR":"BH","GGY":"GG","YEM":"YE","CRI":"CR","UMI":"UM","COL":"CO","CAN":"CA","GUM":"GU","MAR":"MA","NER":"NE","SMR":"SM","SVK":"SK","NAM":"NA","BGD":"BD","BMU":"BM","CMR":"CM","MAF":"MF","VUT":"VU","BTN":"BT","BES":"BQ","GTM":"GT","MHL":"MH","COM":"KM"}; // ISO region labels, never measurement values.
    const regionNames = new Intl.DisplayNames(['fr'], { type: 'region' });
    const fmt = (value, digits = 0) => Number(value ?? 0).toLocaleString('fr-FR', { maximumFractionDigits: digits });
    function countryName(value) {
        const code = String(value || '').toUpperCase();
        const alpha2 = countryCodes[code] || (code.length === 2 ? code : null);
        try { return alpha2 ? regionNames.of(alpha2) : value || 'Non renseigné'; } catch (_) { return value || 'Non renseigné'; }
    }
    window.DalyTracBreakdown = {
        label(value, countries) {
            return countries ? countryName(value) : ({ desktop: 'Desktop', mobile: 'Mobile', tablet: 'Tablet' }[String(value).toLowerCase()] || value || 'Non renseigné');
        },
        destroy(target) { instances.get(target)?.disconnect(); instances.delete(target); },
        render(target, rows, metrics, defaultMetric) {
            this.destroy(target); target.replaceChildren(); target.classList.add('seo-breakdown');
            if (!rows.length) { target.textContent = 'Aucune donnée disponible sur cette période.'; return; }
            const toolbar = document.createElement('label'); toolbar.className = 'seo-breakdown-toolbar';
            const title = document.createElement('span'); title.textContent = 'Comparer par';
            const select = document.createElement('select'); select.setAttribute('aria-label', 'Métrique à comparer');
            metrics.forEach(metric => { const option = document.createElement('option'); option.value = metric.key; option.textContent = metric.label; select.append(option); });
            select.value = defaultMetric;
            toolbar.append(title, select);
            const axis = document.createElementNS(ns, 'svg'); axis.classList.add('seo-breakdown-axis'); axis.setAttribute('aria-hidden', 'true');
            const viewport = document.createElement('div'); viewport.className = 'seo-breakdown-viewport';
            const svg = document.createElementNS(ns, 'svg'); svg.setAttribute('role', 'img');
            viewport.append(svg);
            const tooltip = document.createElement('div'); tooltip.className = 'seo-breakdown-tooltip'; tooltip.hidden = true; tooltip.setAttribute('role', 'status');
            target.append(toolbar, axis, viewport, tooltip);
            const format = (value, metric) => fmt(metric.percent ? value * 100 : value, metric.digits || 0) + (metric.percent ? ' %' : '');
            function draw() {
                tooltip.hidden = true; svg.replaceChildren(); axis.replaceChildren();
                const width = Math.max(180, viewport.clientWidth || 260), left = Math.min(145, width * .34), right = width - 48;
                const rowHeight = 34, height = rows.length * rowHeight;
                const metric = metrics.find(m => m.key === select.value) || metrics[0];
                const sorted = [...rows].sort((a, b) => metric.key === 'position' ? a[metric.key] - b[metric.key] : b[metric.key] - a[metric.key]);
                const peak = Math.max(1, ...sorted.map(row => Number(row[metric.key] ?? 0) * (metric.percent ? 100 : 1)));
                const rawStep = peak / 4, magnitude = 10 ** Math.floor(Math.log10(rawStep));
                let step = Math.max(metric.percent || metric.digits ? .01 : 1, [1, 2, 2.5, 5, 10].find(n => n * magnitude >= rawStep) * magnitude);
                if (!metric.percent && !metric.digits) step = Math.ceil(step);
                const max = Math.ceil(peak / step) * step;
                svg.setAttribute('viewBox', `0 0 ${width} ${height}`); svg.style.height = `${height}px`;
                svg.setAttribute('aria-label', `${metric.label} par catégorie. ${rows.length} catégories. Valeurs réelles Google.`);
                axis.setAttribute('viewBox', `0 0 ${width} 25`);
                axis.style.width = `${width}px`;
                function add(parent, tag, attrs, text) { const node = document.createElementNS(ns, tag); Object.entries(attrs).forEach(([k, v]) => node.setAttribute(k, v)); if (text !== undefined) node.textContent = text; parent.append(node); return node; }
                for (let value = 0; value <= max + step / 100; value += step) {
                    const x = left + value / max * (right - left);
                    add(svg, 'line', { x1: x, x2: x, y1: 0, y2: height, stroke: '#e7eef2', 'stroke-dasharray': '2 3' });
                    add(axis, 'text', { x, y: 16, 'text-anchor': 'middle', fill: '#718592', 'font-size': 10 }, fmt(value, metric.digits || (metric.percent ? 1 : 0)) + (metric.percent ? '%' : ''));
                }
                sorted.forEach((row, index) => {
                    const y = index * rowHeight, value = Number(row[metric.key] ?? 0);
                    const label = String(row.label);
                    const labelLimit = Math.floor(left / 6.2);
                    add(svg, 'text', { x: left - 12, y: y + 21, 'text-anchor': 'end', fill: '#435f6f', 'font-size': 11 }, label.length > labelLimit ? label.slice(0, labelLimit - 1) + '…' : label);
                    const size = value * (metric.percent ? 100 : 1) / max * (right - left);
                    add(svg, 'rect', { x: left, y: y + 9, width: Math.max(0, size), height: 17, rx: 3, fill: '#2D7080' });
                    add(svg, 'text', { x: left + size + 7, y: y + 22, fill: '#0B2638', 'font-size': 10 }, format(value, metric));
                    const detail = `${label}${row.code && row.code !== label ? ' (' + row.code + ')' : ''}\n${metrics.map(m => `${m.label} : ${format(Number(row[m.key] ?? 0), m)}`).join('\n')}`;
                    const hit = add(svg, 'rect', { x: 0, y, width, height: rowHeight, fill: 'transparent', tabindex: 0, 'aria-label': detail });
                    const nativeTitle = document.createElementNS(ns, 'title'); nativeTitle.textContent = detail; hit.append(nativeTitle);
                    const show = () => { tooltip.textContent = detail; tooltip.hidden = false; };
                    ['mouseenter', 'focus', 'touchstart'].forEach(event => hit.addEventListener(event, show));
                    ['mouseleave', 'blur'].forEach(event => hit.addEventListener(event, () => tooltip.hidden = true));
                });
            }
            select.addEventListener('change', draw); viewport.addEventListener('scroll', () => tooltip.hidden = true);
            draw(); const observer = new ResizeObserver(draw); observer.observe(viewport); instances.set(target, observer);
        }
    };
})();
