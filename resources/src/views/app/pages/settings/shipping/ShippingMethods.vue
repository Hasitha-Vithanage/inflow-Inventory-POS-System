<template>
  <div class="main-content">
    <breadcumb :page="$t('ShippingMethods')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="methods"
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
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button @click="New_Method()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
             <!-- Disable delete for Store Pickup (hardcoded check for safety in UI, redundant with backend check but good UX) -->
            <a @click="Edit_Method(props.row)" class="btn-action btn-edit" title="Edit" v-b-tooltip.hover>
              <Edit size="16" :stroke-width="2" />
            </a>
            <a @click="Remove_Method(props.row)" class="btn-action btn-delete" title="Delete" v-b-tooltip.hover v-if="props.row.name !== 'Store Pickup'">
              <XCircle size="16" :stroke-width="2" />
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Method">
      <b-modal hide-footer size="md" id="New_Method" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Method">
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
                    :placeholder="$t('Enter_Name_Method')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Name-feedback"
                    label="Name"
                    v-model="method.name"
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
import { Edit, XCircle } from "lucide-vue";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Shipping Methods"
  },
  components: {
    Edit,
    XCircle
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
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      methods: [],
      editmode: false,
      method: {
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
    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
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
        this.Get_Methods(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Methods(1);
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
      this.Get_Methods(this.serverParams.page);
    },

    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Methods(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Method
    Submit_Method() {
      this.$refs.Create_Method.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Method();
          } else {
            this.Update_Method();
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

    //------------------------------ Modal (create Method) -------------------------------\\
    New_Method() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Method");
    },

    //------------------------------ Modal (Update Method) -------------------------------\\
    Edit_Method(method) {
      this.Get_Methods(this.serverParams.page);
      this.reset_Form();
      this.method = method;
      this.editmode = true;
      this.$bvModal.show("New_Method");
    },

    //----------------------------------------  Get All Methods -------------------------\\
    Get_Methods(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "shipping_methods?page=" +
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
          this.methods = response.data.methods;
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

    //---------------- Send Request with axios ( Create Method) --------------------\\
    Create_Method() {
      this.SubmitProcessing = true;
      axios
        .post("shipping_methods", {
          name: this.method.name,
        })
        .then(response => {
           this.SubmitProcessing = false;
          Fire.$emit("Event_Method");

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

    //--------------- Send Request with axios ( Update Method) --------------------\\
    Update_Method() {
       this.SubmitProcessing = true;
      axios
        .put("shipping_methods/" + this.method.id, {
          name: this.method.name,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Method");

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
      this.method = {
        id: "",
        name: "",
      };
    },

    //--------------------------------- Remove Method --------------------\\
    Remove_Method(row) {
      if (row.name === 'Store Pickup') {
         this.makeToast("warning", "Store Pickup cannot be deleted.", "Warning");
         return;
      }

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
            .delete("shipping_methods/" + row.id)
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
                  response.data.message || this.$t("Delete_Therewassomethingwronge"),
                  "warning"
                );
              }
              Fire.$emit("Delete_Method");
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

    //--------------------------------- Delete by selection --------------------\\
    delete_by_selected() {
      if (this.selectedIds.length <= 0) {
        this.makeToast("warning", this.$t("Select_at_least_one"), this.$t("Warning"));
        return;
      }
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
          NProgress.start();
          NProgress.set(0.1);
          axios
            .post("shipping_methods/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete_Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );
              Fire.$emit("Delete_Method");
            })
            .catch(() => {
              setTimeout(() => NProgress.done(), 500);
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
    this.Get_Methods(1);

    Fire.$on("Event_Method", () => {
      setTimeout(() => {
        this.Get_Methods(this.serverParams.page);
        this.$bvModal.hide("New_Method");
      }, 500);
    });

    Fire.$on("Delete_Method", () => {
      setTimeout(() => {
        this.Get_Methods(this.serverParams.page);
      }, 500);
    });
  }
};
</script>
