const fs=require('fs'),vm=require('vm'),assert=require('assert');
const source=fs.readFileSync('public/js/manager-seo.js','utf8');
const elements=new Map(); const el=id=>{if(!elements.has(id))elements.set(id,{textContent:'',value:'',disabled:false}); return elements.get(id);};
el('site').value='sc-domain:dalytrac.com'; el('start').value='2026-09-26';el('end').value='2026-10-26';el('type').value='web';el('data-state').value='all';
const totals={clicks:2,impressions:10,ctr:.2,position:26.3};
const context={el,generation:0,URLSearchParams,fmt:(n,d=0)=>Number(n??0).toLocaleString('fr-FR',{maximumFractionDigits:d}),chart:()=>{},table:()=>{},get:async report=>report==='overview'?{totals,rows:[{clicks:0,impressions:7,ctr:0,position:99}],_meta:{raw_aggregate_response:{rows:[totals]}}}:{rows:[]}};
vm.createContext(context);
vm.runInContext(source.slice(source.indexOf('    async function load('),source.indexOf("    document.getElementById('seo-filters')")),context);
(async()=>{await context.load();assert.equal(el('impressions').textContent,'10');assert.equal(el('clicks').textContent,'2');assert.equal(el('ctr').textContent,'20 %');assert.equal(el('position').textContent,'26,3');const trace=JSON.parse(el('diagnostics').textContent);assert.deepEqual(trace.raw_aggregate_response.rows[0],trace.backend_totals);assert.equal(trace.displayed.impressions,'10');console.log('Raw API aggregate → backend totals → all four frontend KPI cards: passed.');})().catch(e=>{console.error(e);process.exitCode=1;});
