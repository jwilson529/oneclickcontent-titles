const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync(require('node:path').join(__dirname,'../../admin/js/occ-titles-settings.js'),'utf8').replace(/\r\n/g,'\n');
const test=require('node:test');
test('settings saves preserve overlapping edits, failures, and provider reload order',()=>{
const a=source.indexOf('    let isProcessing = false;'), b=source.indexOf('    /**\n     * Show a notification',a);
assert(a>=0 && b>a,'Autosave queue boundary exists');
const requests=[],states=[];let timer, reloads=0;
const ctx={Map,Set,getSettingsString:(key,fallback)=>fallback,getFieldPayload:x=>x,setSaveState:(message,state)=>states.push({message,state}),showNotification(){},setTimeout:fn=>(timer=fn,1),clearTimeout(){},location:{reload(){reloads++}},occ_titles_admin_vars:{ajax_url:'/ajax',occ_titles_ajax_nonce:'test'},$:{ajax(options){const p={options,done(fn){this.ok=fn;return this},fail(fn){this.failFn=fn;return this},always(fn){this.end=fn;return this}};requests.push(p);return p}}};
vm.createContext(ctx);vm.runInContext(source.slice(a,b),ctx);
const save=(fieldName,fieldValue)=>ctx.autoSaveField({fieldName,fieldValue});
const finish=(p,ok=true,refresh=false)=>{p.ok({success:ok,data:{refresh}});p.end();};
save('model','first');save('logging','1');timer();assert.equal(requests.length,1);
save('model','second');save('model','third');finish(requests[0]);assert.equal(requests.length,2);
finish(requests[1]);assert.equal(requests[2].options.data.field_value,'third');
finish(requests[2]);assert.equal(states.at(-1).state,'saved');
save('voice','warm');save('provider','openrouter');timer();finish(requests[3]);
assert.equal(reloads,0);finish(requests[4],true,true);assert.equal(reloads,1);
save('model','bad');timer();finish(requests[5],false);
save('logging','0');timer();finish(requests[6]);assert.equal(states.at(-1).state,'error');
save('model','fixed');timer();finish(requests[7]);assert.equal(states.at(-1).state,'saved');
console.log('PASS: rapid different-field saves, in-flight edits, newest value, provider reload ordering, and persistent save errors.');


});
