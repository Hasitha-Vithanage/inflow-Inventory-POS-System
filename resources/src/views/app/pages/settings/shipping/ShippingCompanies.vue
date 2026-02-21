<template>
  <div class="main-content">
    <breadcumb :page="$t('ShippingCompanies')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="companies"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),  
      }"
        :select-options="{ 
          enabled: true ,
          clearSelectionText: '',
        }"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button @click="New_Company()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Company(props.row)" title="Edit" v-b-tooltip.hover>
              <edit size="20" class="text-success mr-2"></edit>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Remove_Company(props.row.id)">
              <x size="20" class="text-danger"></x>
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Company">
      <b-modal hide-footer size="md" id="New_Company" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Company">
          <b-row>
            <!-- Name -->
            <b-col md="12">
              <validation-provider
                name="Name"
                :rules="{ required: true }"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Name') + ' ' + '*'">
                  <b-form-input
                    :placeholder="$t('Enter_Name_Company')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Name-feedback"
                    label="Name"
                    v-model="company.name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

             <b-col md="12" class="mt-3">
                <b-button variant="primary" type="submit"  :disabled="SubmitProcessing"><i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}</b-button>
                  <div v-once class="typo__p" v-if="SubmitProcessing">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
            </b-col>

          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>


<script>
import { Edit, X } from "lucide-vue";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Shipping Companies"
  },
  components: {
    Edit,
    X
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      totalRows: "",
      search: "",
      limit: "10",
      companies: [],
      editmode: false,
      company: {
        id: "",
        name: "",
      }
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
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
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Companies(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Companies(1);
      }
    },

    //---- Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Companies(this.serverParams.page);
    },

    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Companies(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Company
    Submit_Company() {
      this.$refs.Create_Company.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Company();
          } else {
            this.Update_Company();
          }
        }
      });
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------------------------------ Modal (create Company) -------------------------------\\
    New_Company() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Company");
    },

    //------------------------------ Modal (Update Company) -------------------------------\\
    Edit_Company(company) {
      this.Get_Companies(this.serverParams.page);
      this.reset_Form();
      this.company = company;
      this.editmode = true;
      this.$bvModal.show("New_Company");
    },

    //----------------------------------------  Get All Companies -------------------------\\
    Get_Companies(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "shipping_companies?page=" +
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
          this.companies = response.data.companies;
          this.totalRows = response.data.totalRows;

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
    },

    //---------------- Send Request with axios ( Create Company) --------------------\\
    Create_Company() {
      this.SubmitProcessing = true;
      axios
        .post("shipping_companies", {
          name: this.company.name,
        })
        .then(response => {
           this.SubmitProcessing = false;
          Fire.$emit("Event_Company");

          this.makeToast(
            "success",
            this.$t("Successfully_Created"),
            this.$t("Success")
          );
        })
        .catch(error => {
           this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //--------------- Send Request with axios ( Update Company) --------------------\\
    Update_Company() {
       this.SubmitProcessing = true;
      axios
        .put("shipping_companies/" + this.company.id, {
          name: this.company.name,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Company");

          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //------------------------------ reset Form ------------------------------\\
    reset_Form() {
      this.company = {
        id: "",
        name: "",
      };
    },

    //--------------------------------- Remove Company --------------------\\
    Remove_Company(id) {
      this.$swal({
        title: this.$t("Delete_Title"),
        text: this.$t("Delete_Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete_cancelButtonText"),
        confirmButtonText: this.$t("Delete_confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("shipping_companies/" + id)
            .then(response => {
              if (response.data.success) {
                this.$swal(
                  this.$t("Delete_Deleted"),
                  this.$t("Deleted_in_successfully"),
                  "success"
                );
              } else {
                this.$swal(
                  this.$t("Delete_Failed"),
                  this.$t("Delete_Therewassomethingwronge"),
                  "warning"
                );
              }
              Fire.$emit("Delete_Company");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete_Failed"),
                this.$t("Delete_Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

  }, //end Method

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Companies(1);

    Fire.$on("Event_Company", () => {
      setTimeout(() => {
        this.Get_Companies(this.serverParams.page);
        this.$bvModal.hide("New_Company");
      }, 500);
    });

    Fire.$on("Delete_Company", () => {
      setTimeout(() => {
        this.Get_Companies(this.serverParams.page);
      }, 500);
    });
  }
};
</script>
