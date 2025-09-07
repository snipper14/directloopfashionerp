<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>View/ Print Supplier Statement</h5>
                        </div>
                        <div class="col-md-4 float-md-right ">
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
                                    <th scope="col">Co. Address</th>
                                    <th scope="col">Co. Postal</th>
                                    <th scope="col">City</th>
                                    <th scope="col">Country</th>
                                    <th scope="col">C. Email</th>
                                    <th scope="col">Contact Person</th>
                                    <th scope="col">Contact Email</th>
                                    <th scope="col">Contact Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in suppliers_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.company_name }}
                                    </td>
                                    <td>{{ data.company_phone }}</td>
                                    <td>{{ data.address }}</td>
                                    <td>{{ data.postal_address }}</td>
                                    <td>{{ data.city }}</td>
                                    <td>{{ data.country }}</td>
                                    <td>{{ data.company_email }}</td>
                                    <td>{{ data.contact_person }}</td>
                                    <td>{{ data.contact_email }}</td>
                                    <td>{{ data.contact_phone }}</td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewStatement(data)"
                                            >View Statemt</router-link
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="suppliers_data !== null"
                            v-bind:results="suppliers_data"
                            v-on:get-page="fetchSuppliers"
                        ></Pagination>
                        Items Count {{ suppliers_data.total }}
                    </div>
                </div>
            </div>
        </div>

        <supplier-ledger-details
            v-if="active.view_stamt"
            :supplier_info="supplier_info"
            v-on:dashboard-active="setActiveRefresh"
        />
    </div>
</template>

<script>
import SupplierLedgerDetails from "./SupplierLedgerDetails";
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";

export default {
    data() {
        return {
            active: {
                dashboard: true,

                view_stamt: false
            },
            suppliers_data: [],
            supplier_info: null,

            search: "",
            params: {
                name: "",
                description: ""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        SupplierLedgerDetails,
        Pagination,
        
    },
    mounted() {
        this.fetchSuppliers(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
            
        viewStatement(data) {
           
            this.supplier_info = data;
          
            this.setActive(this.active, "view_stamt");
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/suppliers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.suppliers_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
           
        },

        async fetchSuppliers(page) {
            this.showLoader();
            const res = await this.getApi("data/suppliers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.suppliers_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
