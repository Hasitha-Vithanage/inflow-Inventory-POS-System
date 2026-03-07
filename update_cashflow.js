const fs = require('fs');
let file = 'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/Cash_Flow_Report.vue';
let content = fs.readFileSync(file, 'utf8');

// replace body map
const oldBody = `const body = (this.rows||[]).map(r => ([ r.group, Number(r.inflow||0).toFixed(2), Number(r.outflow||0).toFixed(2), Number(r.net||0).toFixed(2) ]));`;
const newBody = `const dp = (v) => { try { return formatPriceDisplayHelper(Number(v||0), 2, this.price_format_key || getPriceFormatSetting({ store: this.$store })); } catch(e){ return Number(v||0).toFixed(2); } };
      const body = (this.rows||[]).map(r => ([ r.group, dp(r.inflow), dp(r.outflow), dp(r.net) ]));`;
content = content.replace(oldBody, newBody);

// replace foot content
content = content.replace(/{ content: Number\(this\.totalInflow\|\|0\)\.toFixed\(2\), styles:{ halign:'right', fontStyle:'bold' } },/g, "{ content: dp(this.totalInflow), styles:{ halign:'right', fontStyle:'bold' } },");
content = content.replace(/{ content: Number\(this\.totalOutflow\|\|0\)\.toFixed\(2\), styles:{ halign:'right', fontStyle:'bold' } },/g, "{ content: dp(this.totalOutflow), styles:{ halign:'right', fontStyle:'bold' } },");
content = content.replace(/{ content: Number\(this\.netCashFlow\|\|0\)\.toFixed\(2\), styles:{ halign:'right', fontStyle:'bold' } },/g, "{ content: dp(this.netCashFlow), styles:{ halign:'right', fontStyle:'bold' } },");

fs.writeFileSync(file, content, 'utf8');
console.log('Updated ' + file);
