const fs=require('fs'),vm=require('vm'),assert=require('assert');
const source=fs.readFileSync('public/js/analytics-consent.js','utf8');
async function scenario(seed={}) {
 const storage=new Map(Object.entries(seed)); const commands=[],requests=[];
 const window={gtag:(...args)=>{commands.push(args);if(args[0]==='get')args[3](args[2]==='client_id'?'123.456':'1234567890');},addEventListener:()=>{}};
 const context={window,localStorage:{getItem:k=>storage.get(k)??null,setItem:(k,v)=>storage.set(k,v)}, document:{querySelector:sel=>({content:sel.includes('csrf')?'csrf-test':'/analytics/consent'}),querySelectorAll:()=>[]},fetch:async(url,opt)=>requests.push(JSON.parse(opt.body)),setTimeout,clearTimeout,AbortController,URL,location:{href:'https://dalytrac.com/register'},Date};
 vm.createContext(context);vm.runInContext(source,context);await new Promise(r=>setImmediate(r));return {window,commands,requests,storage};
}
(async()=>{
 const s=await scenario();assert.equal(s.commands[0][2].analytics_storage,'denied');assert.equal(s.requests.at(-1).granted,false);
 await s.window.DalyTracConsent.choose('all');assert.equal(s.requests.at(-1).client_id,'123.456');assert.equal(s.commands.filter(c=>c[0]==='consent').at(-1)[2].analytics_storage,'granted');
 await s.window.DalyTracConsent.choose('rejected');assert.equal(s.requests.at(-1).granted,false);assert.equal(s.commands.filter(c=>c[0]==='consent').at(-1)[2].analytics_storage,'denied');
 assert.equal(s.commands.filter(c=>['event','config'].includes(c[0])).length,0);
 const restored=await scenario(Object.fromEntries(s.storage));assert.equal(restored.requests.at(-1).granted,false);
 const grant=await scenario({'dalytrac_cookie_banner_state':JSON.stringify({choice:'all',timestamp:'2026-10-26T00:00:00Z'})});assert.equal(grant.requests.at(-1).granted,true);
 await grant.window.DalyTracConsent.choose('necessary',true);assert.equal(grant.requests.at(-1).granted,false);
 console.log('Consent checks passed: default deny, accept, reject, necessary-only, persistence, restored consent, no duplicate page_view or manual campaign events.');
})().catch(e=>{console.error(e);process.exitCode=1});
