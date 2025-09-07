<template>
    <div class="container">
        <div v-if="isReadPermitted">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-2">
                    <payable-nav />
                </div>
                <div class="col-md-10">
                    <div class="row" style="background: #ffb74d">
                        <div class="col-md-8">
                            <import-supplier-opening-balance />
                        </div>
                        <div class="col-md-4">
                            <button
                                @click="$router.push('suppliers')"
                                class="btn btn-primary custom-button"
                            >
                                New Supplier
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-5">
                                <h3>Suppliers Invoice</h3>
                            </div>
                            <div class="col-md-4 float-md-right">
                                <input
                                    type="text"
                                    placeholder="Search.."
                                    class="form-control small-input"
                                    v-model="search"
                                />
                            </div>
                        </div>

                        <div class="card-body">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Company</th>
                                        <th scope="col">Co. Contacts</th>

                                        <th scope="col">City</th>
                                        <th scope="col">Country</th>
                                        <th scope="col">C. Email</th>
                                        <th scope="col">Contact Person</th>
                                        <th scope="col">Contact Email</th>
                                        <th scope="col">Contact Phone</th>
                                        <th>Add</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in supplier_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.company_name }}
                                        </td>
                                        <td>{{ data.company_phone }}</td>

                                        <td>{{ data.city }}</td>
                                        <td>{{ data.country }}</td>
                                        <td>{{ data.company_email }}</td>
                                        <td>{{ data.contact_person }}</td>
                                        <td>{{ data.contact_email }}</td>
                                        <td>{{ data.contact_phone }}</td>
                                        <td>
                                            <v-btn
                                                v-if="isWritePermitted"
                                                @click="addBalances(data)"
                                                color="info"
                                                title="WRITE"
                                                small
                                            >
                                                Add Balances
                                            </v-btn>
                                            <v-btn
                                                v-if="isWritePermitted"
                                                @click="createInvoice(data)"
                                                color="secondary"
                                                title="write"
                                                small
                                            >
                                                Add Invoice
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination
                                v-if="supplier_data !== null"
                                v-bind:results="supplier_data"
                                v-on:get-page="fetchSupplier"
                            ></Pagination>
                            Items Count {{ supplier_data.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>

        <add-supplier
            v-if="active.add_supplier"
            v-on:dashboard-active="setActiveRefresh"
        />
        <create-invoice
            v-if="active.create_invoice"
            :data="supplier_info"
            v-on:dashboard-active="setActiveRefresh"
        />
        <Modal v-model="opening_balance_modal">
            <add-supplier-opening-balance
                v-if="opening_balance_modal"
                :data="supplier_info"
                v-on:dashboard-active="setActiveRefresh"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import CreateInvoice from "./CreateInvoice.vue";
import PayableNav from "./PayableNav";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import ImportSupplierOpeningBalance from "./ImportSupplierOpeningBalance.vue";
import AddSupplierOpeningBalance from "./AddSupplierOpeningBalance.vue";
import Unauthorized from '../../utilities/Unauthorized.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_supplier: false,
                create_invoice: false,
            },
            search: "",
            opening_balance_modal: false,
            supplier_info: {},
            supplier_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        CreateInvoice,
        PayableNav,
        ImportSupplierOpeningBalance,
        AddSupplierOpeningBalance,
        Unauthorized,
    },
    mounted() {
        this.fetchSupplier(1);
    },
    watch: {
          params: {
              deep: true,
            handler: _.debounce(function (val, old) {
             this.searchSuppliers();
            }, 500),
        },
        search: {
             
            handler: _.debounce(function (val, old) {
           this.searchSuppliers();
            }, 500),
        },
        
    },
    methods: {
        createInvoice(data) {
            this.supplier_info = data;
            this.setActiveComponent("create_invoice");
        },
        addBalances(data) {
            this.supplier_info = data;
            this.opening_balance_modal = true;
        },

        async searchSuppliers() {
            let page = 1;
            const res = await this.getApi("data/suppliers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.supplier_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.opening_balance_modal = false;
            this.fetchSupplier(1);
        },

        async fetchSupplier(page) {
            this.showLoader();
            const res = await this.getApi("data/suppliers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.supplier_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
