<template>
<div class="main-content">
    <breadcumb :page="$t('Variants')" :folder="$t('Settings')" />

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
        <vue-good-table mode="remote" :columns="columns" :totalRows="totalRows" :rows="variantsp" @on-page-change="onPageChange" @on-per-page-change="onPerPageChange" @on-sort-change="onSortChange" @on-search="onSearch" :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),  
      }" :select-options="{ 
          enabled: true ,
          clearSelectionText: '',
        }" @on-selected-rows-change="selectionChanged" :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }" styleClass="table-hover tableOne vgt-table">
            <div slot="selected-row-actions">
                <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
            </div>
            <div slot="table-actions" class="mt-2 mb-3">
                <b-button @click="New_variant()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
                    <i class="i-Add"></i>
                    {{$t('Add')}}
                </b-button>
            </div>

            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field == 'actions'">
                    <a @click="Edit_variant(props.row)" title="Edit" v-b-tooltip.hover>
                        <i class="i-Edit text-25 text-success"></i>
                    </a>
                    <a title="Delete" v-b-tooltip.hover @click="Remove_Variant(props.row.id)">
                        <i class="i-Close-Window text-25 text-danger"></i>
                    </a>
                </span>
            </template>
        </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Variant">
        <b-modal hide-footer size="md" id="New_Variant" :title="editmode?$t('Edit'):$t('Add')">
            <b-form @submit.prevent="Submit_Variant">
                <b-row>
                    <!-- Variant Name -->
                    <b-col md="12">
                        <validation-provider name="Variant Name" :rules="{ required: true}" v-slot="validationContext">
                            <b-form-group :label="$t('VariantName')">
                                <b-form-input :placeholder="$t('Enter_variant_name')" :state="getValidationState(validationContext)" aria-describedby="Code-feedback" label="Variant Name" v-model="variant.v_name"></b-form-input>
                                <b-form-invalid-feedback id="Code-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
                    </b-col>

                    <!-- Variant Cost -->
                    <b-col md="12">
                        <validation-provider name="Variant Cost" :rules="{ required: true}" v-slot="validationContext">
                            <b-form-group :label="$t('VariantCost')">
                                <b-form-input :placeholder="$t('Enter_variant_cost')" :state="getValidationState(validationContext)" aria-describedby="Name-feedback" label="Variant Cost" v-model="variant.v_cost"></b-form-input>
                                <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
                    </b-col>

                    <!-- Variant Price -->
                    <b-col md="12">
                        <validation-provider name="Variant Price" :rules="{ required: true}" v-slot="validationContext">
                            <b-form-group :label="$t('VariantPrice')">
                                <b-form-input :placeholder="$t('Enter_variant_price')" :state="getValidationState(validationContext)" aria-describedby="Name-feedback" label="Variant Price" v-model="variant.v_price"></b-form-input>
                                <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
                    </b-col>

                    <b-col md="12" class="mt-3">
                        <b-button variant="primary" type="submit" :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
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
import NProgress from "nprogress";

export default {
    metaInfo: {
        title: "Variant"
    },
    data() {
        return {
            isLoading: true,
            SubmitProcessing: false,
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
            variantsp: [],
            editmode: false,

            variant: {
                id: "",
                v_name: "",
                v_cost: "",
                v_price: ""
            }
        };
    },
    computed: {
        columns() {
            return [{
                    label: this.$t("VariantName"),
                    field: "v_name",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("VariantCost"),
                    field: "v_cost",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("VariantPrice"),
                    field: "v_price",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("Action"),
                    field: "actions",
                    html: true,
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
        onPageChange({
            currentPage
        }) {
            if (this.serverParams.page !== currentPage) {
                this.updateParams({
                    page: currentPage
                });
                this.Get_Variants(currentPage);
            }
        },

        //---- Event Per Page Change
        onPerPageChange({
            currentPerPage
        }) {
            if (this.limit !== currentPerPage) {
                this.limit = currentPerPage;
                this.updateParams({
                    page: 1,
                    perPage: currentPerPage
                });
                this.Get_Variants(1);
            }
        },

        //---- Event Select Rows
        selectionChanged({
            selectedRows
        }) {
            this.selectedIds = [];
            selectedRows.forEach((row, index) => {
                this.selectedIds.push(row.id);
            });
        },

        //---- Event on Sort Change
        onSortChange(params) {
            this.updateParams({
                sort: {
                    type: params[0].type,
                    field: params[0].field
                }
            });
            this.Get_Variants(this.serverParams.page);
        },

        //---- Event on Search

        onSearch(value) {
            this.search = value.searchTerm;
            this.Get_Variants(this.serverParams.page);
        },

        //---- Validation State Form

        getValidationState({
            dirty,
            validated,
            valid = null
        }) {
            return dirty || validated ? valid : null;
        },

        //------------- Submit Validation Create & Edit Variant
        Submit_Variant() {
            this.$refs.Create_Variant.validate().then(success => {
                if (!success) {
                    this.makeToast(
                        "danger",
                        this.$t("Please_fill_the_form_correctly"),
                        this.$t("Failed")
                    );
                } else {
                    if (!this.editmode) {
                        this.Create_Variant();
                    } else {
                        this.Update_Variant();
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

        //------------------------------ Modal  (create variant) -------------------------------\\
        New_variant() {
            this.reset_Form();
            this.editmode = false;
            this.$bvModal.show("New_Variant");
        },

        //------------------------------ Modal (Update variant) -------------------------------\\
        Edit_variant(variant) {
            this.Get_Variants(this.serverParams.page);
            this.reset_Form();
            this.variant = variant;
            this.editmode = true;
            this.$bvModal.show("New_Variant");
        },

        //--------------------------Get ALL Variants & Sub variant ---------------------------\\

        Get_Variants(page) {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            axios
                .get(
                    "variantsp?page=" +
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
                    console.log(response.data);
                    this.variantsp = response.data.variantsp;
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

        //----------------------------------Create new Variant ----------------\\
        Create_Variant() {
            this.SubmitProcessing = true;
            axios
                .post("variantsp", {
                    v_name: this.variant.v_name,
                    v_cost: this.variant.v_cost,
                    v_price: this.variant.v_price
                })
                .then(response => {
                    this.SubmitProcessing = false;
                    Fire.$emit("Event_Variant");
                    this.makeToast(
                        "success",
                        this.$t("Create.TitleVar"),
                        this.$t("Success")
                    );
                })
                .catch(error => {
                    this.SubmitProcessing = false;
                    this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
                });
        },

        //---------------------------------- Update Variant ----------------\\
        Update_Variant() {
            this.SubmitProcessing = true;
            axios
                .put("variantsp/" + this.variant.id, {
                    v_name: this.variant.v_name,
                    v_cost: this.variant.v_cost,
                    v_price: this.variant.v_price
                })
                .then(response => {
                    this.SubmitProcessing = false;
                    Fire.$emit("Event_Variant");
                    this.makeToast(
                        "success",
                        this.$t("Update.TitleVar"),
                        this.$t("Success")
                    );
                })
                .catch(error => {
                    this.SubmitProcessing = false;
                    this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
                });
        },

        //--------------------------- reset Form ----------------\\

        reset_Form() {
            this.variant = {
                id: "",
                v_name: "",
                v_cost: "",
                v_price: ""
            };
        },

        //--------------------------- Remove Variant----------------\\
        Remove_Variant(id) {
            this.$swal({
                title: this.$t("Delete.Title"),
                text: this.$t("Delete.Text"),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: this.$t("Delete.cancelButtonText"),
                confirmButtonText: this.$t("Delete.confirmButtonText")
            }).then(result => {
                if (result.value) {
                    axios
                        .delete("variantsp/" + id)
                        .then(() => {
                            this.$swal(
                                this.$t("Delete.Deleted"),
                                this.$t("Delete.CatDeleted"),
                                "success"
                            );

                            Fire.$emit("Delete_Variant");
                        })
                        .catch(() => {
                            this.$swal(
                                this.$t("Delete.Failed"),
                                this.$t("Delete.Therewassomethingwronge"),
                                "warning"
                            );
                        });
                }
            });
        },

        //---- Delete variant by selection

        delete_by_selected() {
            this.$swal({
                title: this.$t("Delete.Title"),
                text: this.$t("Delete.Text"),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: this.$t("Delete.cancelButtonText"),
                confirmButtonText: this.$t("Delete.confirmButtonText")
            }).then(result => {
                if (result.value) {
                    // Start the progress bar.
                    NProgress.start();
                    NProgress.set(0.1);
                    axios
                        .post("variantsp/delete/by_selection", {
                            selectedIds: this.selectedIds
                        })
                        .then(() => {
                            this.$swal(
                                this.$t("Delete.Deleted"),
                                this.$t("Delete.VarDeleted"),
                                "success"
                            );

                            Fire.$emit("Delete_Variant");
                        })
                        .catch(() => {
                            // Complete the animation of theprogress bar.
                            setTimeout(() => NProgress.done(), 500);
                            this.$swal(
                                this.$t("Delete.Failed"),
                                this.$t("Delete.Therewassomethingwronge"),
                                "warning"
                            );
                        });
                }
            });
        }
    }, //end Methods

    //----------------------------- Created function-------------------

    created: function () {
        this.Get_Variants(1);

        Fire.$on("Event_Variant", () => {
            setTimeout(() => {
                this.Get_Variants(this.serverParams.page);
                this.$bvModal.hide("New_Variant");
            }, 500);
        });

        Fire.$on("Delete_Variant", () => {
            setTimeout(() => {
                this.Get_Variants(this.serverParams.page);
            }, 500);
        });
    }
};
</script>
