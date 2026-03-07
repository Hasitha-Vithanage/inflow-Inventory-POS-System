import os

files = [
    'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/report_sales_by_brand.vue',
    'c:/xampp/htdocs/inflow/resources/src/views/app/pages/reports/report_sales_by_category.vue'
]

methods_code = """      formatPriceDisplay(number, dec) {
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
        return safeSymbol ? `${safeSymbol} ${value}` : value;
      },

      formatNumber(number, dec) {"""

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    if 'formatPriceDisplay(number, dec)' not in content and 'formatNumber(number, dec) {' in content:
        content = content.replace('      formatNumber(number, dec) {', methods_code)
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print("Updated " + file)
    else:
        print("Skipped " + file)
