const fs = require('fs');
const vm = require('vm');
const source = fs.readFileSync('public/js/manager-analytics.js', 'utf8');
class Element {
 constructor(tag) { this.tag=tag; this.children=[]; this.attrs={}; this.style={}; this.clientWidth=500; this.isConnected=true; }
 append(...items) { this.children.push(...items); }
 replaceChildren(...items) { this.children=items; }
 setAttribute(k,v) { this.attrs[k]=String(v); }
 addEventListener() {}
 createTHead() { const n=new Element('thead'); this.append(n); return n; }
 createTBody() { const n=new Element('tbody'); this.append(n); return n; }
 insertRow() { const n=new Element('tr'); this.append(n); return n; }
 insertCell() { const n=new Element('td'); this.append(n); return n; }
}
const context = { document: { createElement: t => new Element(t), createElementNS: (_,t) => new Element(t) }, ResizeObserver: class { observe() {} disconnect() {} }, observers: new Map(), number: (n,d=0) => Number(n??0).toLocaleString('fr-FR',{maximumFractionDigits:d}) };
vm.createContext(context);
vm.runInContext(source.slice(source.indexOf('    function table('), source.indexOf('    const definitions =')),context);
const walk = node => [node,...node.children.flatMap(walk)];
for (const count of [0,1,2,30,180]) {
 for (const width of [280,500,900]) {
  const rows=Array.from({length:count},(_,i)=>({date:new Date(Date.UTC(2026,0,i+1)).toISOString().slice(0,10).replaceAll('-',''),activeUsers:i%3,sessions:i%9,screenPageViews:12+i%7}));
  for (const series of [[['activeUsers','Utilisateurs','#2D7080'],['sessions','Sessions','#E7B84B']],[['screenPageViews','Vues','#0B2638']]]) {
   const target=new Element('div');
   context.target=target; context.rows=rows; context.series=series;
   vm.runInContext('chart(target, rows, series)',context);
   if (walk(target).some(n=>Object.values(n.attrs).some(v=>/NaN|Infinity/.test(v)))) throw new Error('Invalid chart coordinates');
   if (count && !walk(target).some(n=>n.tag==='text' && n.textContent==='01/01')) throw new Error('Missing date axis');
  }
 }
}
console.log('Chart rendering checks passed: empty, single-day, two-day, 30-day and 180-day data.');
