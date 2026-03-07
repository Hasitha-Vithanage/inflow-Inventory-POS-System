<template>
  <div class="main-content">
    <breadcumb :page="$t('CustomersReport')" :folder="$t('Reports')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="rows"
        :group-options="{
          enabled: true,
          headerPosition: 'bottom',
        }"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
        placeholder: $t('Search_this_table'),
        enabled: true,
      }"
      
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="tableOne table-hover vgt-table mt-3"
      >
        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a title="PDF" class="cursor-pointer" v-b-tooltip.hover @click="Download_PDF(props.row , props.row.id)">
              <FileText size="18" class="text-danger"></FileText>
            </a>
            <router-link title="Report" :to="'/app/reports/detail_customer/'+props.row.id">
             <Eye size="18" class="text-info"></Eye>
            </router-link>
          </span>
          <span v-else-if="props.column.field == 'total_amount'">
            {{ formatPriceWithSymbol(currentUser && currentUser.currency, props.row.total_amount, 2) }}
          </span>
          <span v-else-if="props.column.field == 'total_paid'">
            {{ formatPriceWithSymbol(currentUser && currentUser.currency, props.row.total_paid, 2) }}
          </span>
          <span v-else-if="props.column.field == 'due'">
            {{ formatPriceWithSymbol(currentUser && currentUser.currency, props.row.due, 2) }}
          </span>
          <span v-else-if="props.column.field == 'return_Due'">
            {{ formatPriceWithSymbol(currentUser && currentUser.currency, props.row.return_Due, 2) }}
          </span>
          <span v-else>
            {{ props.formattedRow[props.column.field] }}
          </span>
        </template>
      </vue-good-table>
    </b-card>
  </div>
</template>


<script>
import NProgress from "nprogress";
import { mapActions, mapGetters } from "vuex";
import { FileText, Eye } from "lucide-vue";
import {
  formatPriceDisplay as formatPriceDisplayHelper,
  getPriceFormatSetting
} from "../../../../utils/priceFormat";

export default {
  components: {
    FileText,
    Eye
  },
  metaInfo: {
    title: "Report Customers"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      limit: "10",
      search: "",
      totalRows: "",
      clients: [],
      client: {},
      rows: [{
          total_sales: 'Total',
         
          children: [
             
          ],
      },],
      // Optional price format key for frontend display (loaded from system settings/localStorage)
      price_format_key: null
    };
  },

  computed: {
    ...mapGetters(["currentUser"]),
    columns() {
      return [
       
        {
          label: this.$t("CustomerName"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Phone"),
          field: "phone",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("TotalSales"),
          field: "total_sales",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Amount"),
          field: "total_amount",
          type: "decimal",
          headerField: this.sumCount,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Paid"),
          field: "total_paid",
          type: "decimal",
          headerField: this.sumCount2,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total_Sale_Due"),
          field: "due",
          type: "decimal",
          headerField: this.sumCount3,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total_Sell_Return_Due"),
          field: "return_Due",
          type: "decimal",
          headerField: this.sumCount4,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Action"),
          field: "actions",
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
        sumCount(rowObj) {
        if (!rowObj || !rowObj.children || !Array.isArray(rowObj.children)) {
            return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, 0, 2);
        }

        let sum = 0;
        for (let i = 0; i < rowObj.children.length; i++) {
            if (typeof rowObj.children[i].total_amount === 'number') {
                sum += rowObj.children[i].total_amount;
            }
        }
        return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, sum, 2);
    },

    sumCount2(rowObj) {
        if (!rowObj || !rowObj.children || !Array.isArray(rowObj.children)) {
            return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, 0, 2);
        }

        let sum = 0;
        for (let i = 0; i < rowObj.children.length; i++) {
            if (typeof rowObj.children[i].total_paid === 'number') {
                sum += rowObj.children[i].total_paid;
            }
        }
        return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, sum, 2);
    },

    sumCount3(rowObj) {
        if (!rowObj || !rowObj.children || !Array.isArray(rowObj.children)) {
            return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, 0, 2);
        }

        let sum = 0;
        for (let i = 0; i < rowObj.children.length; i++) {
            if (typeof rowObj.children[i].due === 'number') {
                sum += rowObj.children[i].due;
            }
        }
        return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, sum, 2);
    },

    sumCount4(rowObj) {
        if (!rowObj || !rowObj.children || !Array.isArray(rowObj.children)) {
            return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, 0, 2);
        }

        let sum = 0;
        for (let i = 0; i < rowObj.children.length; i++) {
            if (typeof rowObj.children[i].return_Due === 'number') {
                sum += rowObj.children[i].return_Due;
            }
        }
        return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, sum, 2);
    },

    
     //--------------------------- Download_PDF-------------------------------\\
    Download_PDF(client , id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
     
       axios
        .get("report/client_pdf/" + id, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "report-" + client.name + ".pdf");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Client_Report(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Client_Report(1);
      }
    },

    //---- Event on Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Client_Report(this.serverParams.page);
    },

    //---- Event on Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Client_Report(this.serverParams.page);
    },

    // Price formatting for display only (does NOT affect calculations or stored values)
    // Uses the global/system price_format setting when available; otherwise falls back
    // to the existing toLocaleString behavior to preserve current behavior.
    formatPriceDisplay(number, dec) {
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

    //--------------------------- Get Customer Report -------------\\

    Get_Client_Report(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "report/client?page=" +
            page +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.clients = response.data.report;
          this.totalRows = response.data.totalRows;
          this.rows[0].children = this.clients;
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    }
  }, //end Methods

  //----------------------------- Created function------------------- \\

  created: function() {
    this.Get_Client_Report(1);
    
  }
};
</script>