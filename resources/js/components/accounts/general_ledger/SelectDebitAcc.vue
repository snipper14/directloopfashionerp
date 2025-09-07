<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3><b>Debit Acc.</b></h3>
                        </div>

                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="form-group col-md-6 nopadding">
                                        <label for=""
                                            >Search Supplier Account</label
                                        >
                                        <treeselect
                                            :load-options="fetchSupplier"
                                            :options="
                                                supplier_accounts_select_data
                                            "
                                            :auto-load-root-options="false"
                                            v-model="supplier_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select  Account "
                                        />
                                    </div>
                                    <div class="form-group col-md-6 nopadding">
                                        <label for=""
                                            >Search Customer Account</label
                                        >
                                        <treeselect
                                            :load-options="fetchCustomer"
                                            :options="
                                                customer_accounts_select_data
                                            "
                                            :auto-load-root-options="false"
                                            v-model="customer_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select  Account "
                                        />
                                    </div>

                                    <div class="form-group col-md-6 nopadding">
                                        <label for=""
                                            >Employee Accounts</label
                                        >
                                        <treeselect
                                            :load-options="fetchEmployee"
                                            :options="employee_account_select_data"
                                            :auto-load-root-options="false"
                                            v-model="employee_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Employee Accounts "
                                        />
                                    </div>
                                       <div class="form-group col-md-6 nopadding">
                                        <label for=""
                                            >Search Other Accounts</label
                                        >
                                        <treeselect
                                            :load-options="fetchAccounts"
                                            :options="accounts_select_data"
                                            :auto-load-root-options="false"
                                            v-model="account_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select   Account "
                                        />
                                    </div>

                                    <div class="row form-group col-md-4 mt-3">
                                        <v-btn
                                            x-small
                                            type="button"
                                            color="info"
                                            @click="selectAccount()"
                                        >
                                            Select
                                        </v-btn>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: {
        Treeselect,
    },
    data() {
        return {
            accounts_select_data: null,
            supplier_accounts_select_data: null,
            customer_accounts_select_data: null,
            employee_account_select_data:null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
            employee_id:null,
            supplier_id: null,
            customer_id: null,
            account_id: null,
            selected_object: {
                id: null,
                name: "",
                type: "",
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
    },
    watch: {
        supplier_id: {
            handler: _.debounce(function (val, old) {
                var name = this.supplier_accounts_select_data.find(
                    (x) => x.id === val
                ).label;
                this.selected_object.name = name;
                this.selected_object.id = val;
                this.selected_object.type = "supplier";
                this.customer_accounts_select_data = null;
                this.customer_id = null;
                 this.accounts_select_data = null;
                this.account_id = null;
                    this.employee_account_select_data=null
                this.employee_id=null
            }, 0),
        },
        customer_id: {
            handler: _.debounce(function (val, old) {
                var name = this.customer_accounts_select_data.find(
                    (x) => x.id === val
                ).label;
                this.selected_object.name = name;
                this.selected_object.id = val;
                this.selected_object.type = "customer";
                this.supplier_accounts_select_data = null;
                this.supplier_id = null;
                this.accounts_select_data = null;
                this.account_id = null;
                  this.employee_account_select_data=null
                this.employee_id=null
            }, 0),
        },
        account_id: {
            handler: _.debounce(function (val, old) {
                var name = this.accounts_select_data.find(
                    (x) => x.id === val
                ).label;
                this.selected_object.name = name;
                this.selected_object.id = val;
                this.selected_object.type = "account";
                this.supplier_accounts_select_data = null;
                this.supplier_id = null;
                this.customer_accounts_select_data = null;
                this.customer_id = null;
                this.employee_account_select_data=null
                this.employee_id=null
               
            }, 0),
            
        },
          employee_id: {
            handler: _.debounce(function (val, old) {
                var name = this.employee_account_select_data.find(
                    (x) => x.id === val
                ).label;
                this.selected_object.name = name;
                this.selected_object.id = val;
                this.selected_object.type = "employee";
                this.supplier_accounts_select_data = null;
                this.supplier_id = null;
                this.customer_accounts_select_data = null;
                this.customer_id = null;
                 this.accounts_select_data = null;
                this.account_id = null;
               
            }, 0),
          }
    },
    methods: {
        selectAccount() {
            this.$emit('dismissModal',this.selected_object)
        },
        async fetchAccounts() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/ledger_account/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.accounts_select_data = this.modifyAccountSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyAccountSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.account,
                };
            });
            return modif;
        },

        async fetchSupplier() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/suppliers/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.supplier_accounts_select_data = res.data.results.map(
                    (obj) => {
                        return {
                            id: obj.id,
                            label: obj.company_name,
                        };
                    }
                );
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchEmployee(){
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/employee/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.employee_account_select_data = res.data.results.map(
                    (obj) => {
                        return {
                            id: obj.id,
                            label: obj.first_name+" "+obj.last_name,
                        };
                    }
                );
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCustomer() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/customers/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.customer_accounts_select_data = res.data.results.map(
                    (obj) => {
                        return {
                            id: obj.id,
                            label: obj.company_name,
                        };
                    }
                );
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
