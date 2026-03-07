<template>
    <div class="main-content">
      <breadcumb :page="$t('Deposits_Report')" :folder="$t('Reports')"/>
  
      <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

      <b-col md="12" class="text-center" v-if="!isLoading">
        <date-range-picker 
          v-model="dateRange" 
          :startDate="startDate" 
          :endDate="endDate" 
           @update="Submit_filter_dateRange"
          :locale-data="locale" > 

          <template v-slot:input="picker" style="min-width: 350px;">
              {{ picker.startDate.toJSON().slice(0, 10)}} - {{ picker.endDate.toJSON().slice(0, 10)}}
          </template>        
        </date-range-picker>
      </b-col>

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
            <span v-if="props.column.field == 'total_deposits'">
              {{ formatPriceWithSymbol(currentUser && currentUser.currency, props.row.total_deposits, 2) }}
            </span>
            <span v-else>
              {{ props.formattedRow[props.column.field] }}
            </span>
          </template>
  
         <div slot="table-actions" class="mt-2 mb-3">
          
            <b-button @click="deposits_report_pdf()" size="sm" variant="outline-danger ripple m-1">
              <FileText size="14" class="mr-1"></FileText> PDF
            </b-button>
             <vue-excel-xlsx
                class="btn btn-sm btn-outline-success ripple m-1"
                :data="reports"
                :columns="columns"
                :file-name="'deposits_report'"
                :file-type="'xlsx'"
                :sheet-name="'deposits_report'"
                >
                <FileSpreadsheet size="14" class="mr-1"></FileSpreadsheet> EXCEL
            </vue-excel-xlsx>
          </div>
        </vue-good-table>
      </b-card>
    </div>
  </template>
  
  
  <script>
import NProgress from "nprogress";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { mapActions, mapGetters } from "vuex";
import { FileText, FileSpreadsheet } from "lucide-vue";

import DateRangePicker from 'vue2-daterange-picker'
//you need to import the CSS manually
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import moment from 'moment'
import {
  formatPriceDisplay as formatPriceDisplayHelper,
  getPriceFormatSetting
} from "../../../../utils/priceFormat";
  
  export default {
    components: {
      DateRangePicker,
      FileText,
      FileSpreadsheet
    },
    metaInfo: {
      title: "Deposits Report"
    },
    data() {
      return {
        startDate: "", 
        endDate: "", 
        dateRange: { 
          startDate: "", 
          endDate: "" 
        }, 
        locale:{ 
            //separator between the two ranges apply
            Label: "Apply", 
            cancelLabel: "Cancel", 
            weekLabel: "W", 
            customRangeLabel: "Custom Range", 
            daysOfWeek: moment.weekdaysMin(), 
            //array of days - see moment documenations for details 
            monthNames: moment.monthsShort(), //array of month names - see moment documenations for details 
            firstDay: 1 //ISO first day of week - see moment documenations for details
          },
          today_mode: true,
          to: "",
          from: "",
        isLoading: true,
        rows: [{
          category_name: 'Total',
         
          children: [
             
          ],
      },],
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
        reports: [],
        report: {},
        warehouse_id: 0
      };
    },
  
    computed: {
      ...mapGetters(["currentUser"]),
      columns() {
        return [
          {
            label: this.$t("Deposit_Category"),
            field: "category_name",
            tdClass: "text-left",
            thClass: "text-left",
            sortable: false
          },
         
          {
            label: this.$t("Total_Deposits"),
            field: "total_deposits",
            type: "decimal",
            headerField: this.sumCount,
            tdClass: "text-left",
            thClass: "text-left",
            sortable: false
          },

         
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
          sum += rowObj.children[i].total_deposits;
        }
        return this.formatPriceWithSymbol(this.currentUser && this.currentUser.currency, sum, 2);
      },
  
       //----------------------------------- Sales PDF ------------------------------\\
      deposits_report_pdf() {
        var self = this;
        let pdf = new jsPDF("p", "pt");

        const fontPath = "/fonts/Vazirmatn-Bold.ttf";
        pdf.addFont(fontPath, "VazirmatnBold", "bold"); 
        pdf.setFont("VazirmatnBold"); 

        let columns = [
          { header: self.$t("Deposit_Category"), dataKey: "category_name" },
          { header: self.$t("Total_Deposits"), dataKey: "total_deposits" },
        ];

        // Calculate totals
        let totalGrandTotal = self.reports.reduce((sum, report) => sum + parseFloat(report.total_deposits || 0), 0);
        
        let footer = [{
          category_name: self.$t("Total"),
          total_deposits: self.formatPriceDisplay(totalGrandTotal, 2),
        }];

        const body = (self.reports || []).map(r => ({
           category_name: r.category_name,
           total_deposits: self.formatPriceDisplay(r.total_deposits, 2)
        }));

        autoTable(pdf, {
             columns: columns,
             body: body,
             foot: footer,
             startY: 70,
             theme: "grid", 
             didDrawPage: (data) => {
               pdf.setFont("VazirmatnBold");
               pdf.setFontSize(18);
               pdf.text("Deposits Report", 40, 25);   
             },
             styles: {
               font: "VazirmatnBold", 
               halign: "center", // 
             },
             headStyles: {
               fillColor: [26, 86, 219], 
               textColor: 255, 
               fontStyle: "bold", 
             },
             footStyles: {
               fillColor: [26, 86, 219], 
               textColor: 255, 
               fontStyle: "bold", 
             },
        });

        pdf.save("deposits_report.pdf");
        
      },
  
      //---- update Params Table
      updateParams(newProps) {
        this.serverParams = Object.assign({}, this.serverParams, newProps);
      },
  
      //---- Event Page Change
      onPageChange({ currentPage }) {
        if (this.serverParams.page !== currentPage) {
          this.updateParams({ page: currentPage });
          this.get_deposits_report(currentPage);
        }
      },
  
      //---- Event Per Page Change
      onPerPageChange({ currentPerPage }) {
        if (this.limit !== currentPerPage) {
          this.limit = currentPerPage;
          this.updateParams({ page: 1, perPage: currentPerPage });
          this.get_deposits_report(1);
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
        this.get_deposits_report(this.serverParams.page);
      },
  
      //---- Event on Search
  
      onSearch(value) {
        this.search = value.searchTerm;
        this.get_deposits_report(this.serverParams.page);
      },
  
      //------------------------------Formetted Numbers -------------------------\\
      formatNumber(number, dec) {
        const value = (typeof number === "string"
          ? number
          : number.toString()
        ).split(".");
        if (dec <= 0) return value[0];
        let formated = value[1] || "";
        if (formated.length > dec)
          return `${value[0]}.${formated.substr(0, dec)}`;
        while (formated.length < dec) formated += "0";
        return `${value[0]}.${formated}`;
      },

      // Price formatting for display only (does NOT affect calculations or stored values)
      // Uses the global/system price_format setting when available; otherwise falls back
      // to the existing formatNumber helper to preserve current behavior.
      formatPriceDisplay(number, dec) {
        try {
          const decimals = Number.isInteger(dec) ? dec : 0;
          const key = this.price_format_key || getPriceFormatSetting({ store: this.$store });
          if (key) {
            this.price_format_key = key;
          }
          const effectiveKey = key || null;
          return formatPriceDisplayHelper(number, decimals, effectiveKey);
        } catch (e) {
          return this.formatNumber(number, dec);
        }
      },

      formatPriceWithSymbol(symbol, number, dec) {
        const safeSymbol = symbol || "";
        const value = this.formatPriceDisplay(number, dec);
        return safeSymbol ? `${safeSymbol} ${value}` : value;
      },
  
    //----------------------------- Submit Date Picker -------------------\\
    Submit_filter_dateRange() {
      var self = this;
      self.startDate =  self.dateRange.startDate.toJSON().slice(0, 10);
      self.endDate = self.dateRange.endDate.toJSON().slice(0, 10);
      self.get_deposits_report(1);
    },


    get_data_loaded() {
      var self = this;
      if (self.today_mode) {
        let startDate = new Date("01/01/2000");  // Set start date to "01/01/2000"
        let endDate = new Date();  // Set end date to current date

        self.startDate = startDate.toISOString();
        self.endDate = endDate.toISOString();

        self.dateRange.startDate = startDate.toISOString();
        self.dateRange.endDate = endDate.toISOString();
      }
    },

  
      //--------------------------- Get Customer Report -------------\\
  
      get_deposits_report(page) {
        // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);
        this.get_data_loaded();
        axios
          .get(
            "report/deposits_report?page=" +
              page +
              "&SortField=" +
              this.serverParams.sort.field +
              "&SortType=" +
              this.serverParams.sort.type +
              "&search=" +
              this.search +
              "&limit=" +
              this.limit +
              "&to=" +
            this.endDate +
            "&from=" +
            this.startDate
          )
          .then(response => {
            this.reports = response.data.reports;
            this.totalRows = response.data.totalRows;
            this.rows[0].children = this.reports;

            // Complete the animation of theprogress bar.
            NProgress.done();
            this.isLoading = false;
            this.today_mode = false;
          })
          .catch(response => {
            // Complete the animation of theprogress bar.
            NProgress.done();
            setTimeout(() => {
              this.isLoading = false;
              this.today_mode = false;
            }, 500);
          });
      }
    }, //end Methods
  
    //----------------------------- Created function------------------- \\
  
    created: function() {
      this.get_deposits_report(1);
    }
  };
  </script>