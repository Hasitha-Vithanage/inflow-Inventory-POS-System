const fs = require('fs');
const files = [
    'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/report_sales_by_brand.vue',
    'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/report_sales_by_category.vue'
];

const methods_code = `      formatPriceDisplay(number, dec) {
        try {
          const decimals = Number.isInteger(dec) ? dec : 2;
          const n = Number(number || 0);
          const key = this.price_format_key || getPriceFormatSetting({ store: this.$store });
          if (key) {
            this.price_format_key = key;
          }
          const effectiveKey = key || null;
          return formatPriceDisplayHelper(n, decimals, effectiveKey);
        } catch (e) {
          const n = Number(number || 0);
          return n.toLocaleString(undefined, { maximumFractionDigits: dec || 2 });
        }
      },

      formatPriceWithSymbol(symbol, number, dec) {
        const safeSymbol = symbol || "";
        const value = this.formatPriceDisplay(number, dec);
        return safeSymbol ? \`\${safeSymbol} \${value}\` : value;
      },

      formatNumber(number, dec) {`;

for (const file of files) {
    let content = fs.readFileSync(file, 'utf8');
    if (!content.includes('formatPriceDisplay(number, dec)') && content.includes('formatNumber(number, dec) {')) {
        content = content.replace('      formatNumber(number, dec) {', methods_code);
        fs.writeFileSync(file, content, 'utf8');
        console.log("Updated " + file);
    } else {
        console.log("Skipped " + file);
    }
}
