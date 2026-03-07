const fs = require('fs');
let file = 'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/report_transactions.vue';
let content = fs.readFileSync(file, 'utf8');

// replace montant footer
content = content.replace("montant: `${totalGrandTotal.toFixed(2)}`,", "montant: this.formatPriceDisplay(totalGrandTotal, 2),");

// Formatted body for Payment_PDF
const oldPaymentBody = 'body: this.payments,';
const newPaymentBody = `body: (this.payments || []).map(r => ({
          date: r.date,
          Ref: r.Ref,
          Ref_Sale: r.Ref_Sale,
          client_name: r.client_name,
          payment_method: r.payment_method,
          account_name: r.account_name,
          montant: this.formatPriceDisplay(r.montant, 2),
          user_name: r.user_name
        })),`;
if (content.includes(oldPaymentBody)) {
    content = content.replace(oldPaymentBody, newPaymentBody);
}

// Format summary body
const oldSummaryBody = `      const summaryBody = this.payment_summary.map(item => ({
        payment_method: item.payment_method,
        sale_total: item.sale_total.toFixed(2),
        purchase_total: item.purchase_total.toFixed(2),
        expense_total: item.expense_total.toFixed(2)
      }));`;
const newSummaryBody = `      const summaryBody = this.payment_summary.map(item => ({
        payment_method: item.payment_method,
        sale_total: this.formatPriceDisplay(item.sale_total, 2),
        purchase_total: this.formatPriceDisplay(item.purchase_total, 2),
        expense_total: this.formatPriceDisplay(item.expense_total, 2)
      }));`;
if (content.includes(oldSummaryBody)) {
    content = content.replace(oldSummaryBody, newSummaryBody);
}

fs.writeFileSync(file, content, 'utf8');
console.log('Updated ' + file);
