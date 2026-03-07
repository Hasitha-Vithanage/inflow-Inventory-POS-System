const fs = require('fs');
let file = 'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/product_sales_report.vue';
let content = fs.readFileSync(file, 'utf8');

// replace quantity and total footer
content = content.replace("quantity: `${totalquantity.toFixed(2)}`,", "quantity: `${totalquantity.toFixed(2)}`,");
content = content.replace("total: `${totaltotal.toFixed(2)}`,", "total: self.formatPriceDisplay(totaltotal, 2),");

// replace 'body: self.sales,' with the formatted body
const oldBody = 'body: self.sales,';
const newBody = `body: (self.sales || []).map(r => ({
               date: r.date,
               Ref: r.Ref,
               client_name: r.client_name,
               warehouse_name: r.warehouse_name,
               product_name: r.product_name,
               quantity: r.quantity,
               total: self.formatPriceDisplay(r.total, 2)
             })),`;
if (content.includes(oldBody)) {
    content = content.replace(oldBody, newBody);
}

fs.writeFileSync(file, content, 'utf8');
console.log('Updated ' + file);

// similarly update product_purchases_report.vue
file = 'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/product_purchases_report.vue';
content = fs.readFileSync(file, 'utf8');
content = content.replace("quantity: `${totalquantity.toFixed(2)}`,", "quantity: `${totalquantity.toFixed(2)}`,");
content = content.replace("total: `${totaltotal.toFixed(2)}`,", "total: self.formatPriceDisplay(totaltotal, 2),");

const oldBodyPurchases = 'body: self.purchases,';
const newBodyPurchases = `body: (self.purchases || []).map(r => ({
               date: r.date,
               Ref: r.Ref,
               provider_name: r.provider_name,
               warehouse_name: r.warehouse_name,
               product_name: r.product_name,
               quantity: r.quantity,
               total: self.formatPriceDisplay(r.total, 2)
             })),`;
if (content.includes(oldBodyPurchases)) {
    content = content.replace(oldBodyPurchases, newBodyPurchases);
}
fs.writeFileSync(file, content, 'utf8');
console.log('Updated ' + file);
