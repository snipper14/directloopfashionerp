<template>
    <div class="container">
        <div v-if="isReadPermitted">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-2">
                    <DirectCreditNav />
                </div>
                <div class="col-md-10">
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="row">
                                <div class="col-md-3">
                                    <v-btn
                                        v-if="isWritePermitted"
                                        class="ma-2 v-btn-primary"
                                        @click="$router.push('customers')"
                                        color="primary"
                                        dark
                                    >
                                        <v-icon medium>{{
                                            icons.mdiPlusThick
                                        }}</v-icon>
                                        Create Customer
                                    </v-btn>
                                </div>
                                <div class="col-md-3">
                                    <h5>Generate Direct Retail CreditNote</h5>
                                </div>
                                <div class="col-md-4">
                                    <input
                                        type="text"
                                        placeholder="Search.."
                                        class="form-control small-input"
                                        v-model="search"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Company</th>
                                            <th>Add Sale</th>
                                            <th scope="col">Co. Contacts</th>
                                            <th scope="col">Co. Address</th>

                                            <th scope="col">C. Email</th>
                                            <th scope="col">Contact Person</th>
                                            <th scope="col">Contact Email</th>
                                            <th scope="col">Contact Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in customer_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.company_name }}
                                            </td>
                                            <td>
                                                <v-btn
                                                    small
                                                    title="WRITE"
                                                    color="primary"
                                                    v-if="isWritePermitted"
                                                    to="#"
                                                    @click="
                                                        showOrderModal(data)
                                                    "
                                                    >New CreditNote</v-btn
                                                >
                                            </td>
                                            <td>{{ data.company_phone }}</td>
                                            <td>{{ data.address }}</td>

                                            <td>{{ data.company_email }}</td>
                                            <td>{{ data.contact_person }}</td>
                                            <td>{{ data.contact_email }}</td>
                                            <td>{{ data.contact_phone }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <pagination
                                v-if="customer_data !== null"
                                v-bind:results="customer_data"
                                v-on:get-page="fetchCustomers"
                            ></pagination>
                            Items Count {{ customer_data.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <center v-else>
            <b style="color: red; font-size: 1.2rem"><unauthorized /></b>
        </center>
        <Modal v-model="add_cr_modal" width="1200px"> <AddRetailCrModal v-on:dismiss-modal="dismissModal" v-if="add_cr_modal" :customer_details="customer_details" /></Modal>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

import DirectCreditNav from "../DirectCreditNav.vue";
import AddRetailCrModal from './AddRetailCrModal.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,

                add_order: false,
            },
           
            customer_data: [],
            add_cr_modal: false,
            customer_details: null,
            search: "",
            params: {
                name: "",
                description: "",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Unauthorized,
        Pagination,
        DirectCreditNav,
        AddRetailCrModal,
    },

    mounted() {
        this.fetchCustomers(1);
    },
    watch: {
        params: {
            handler() {
                this.searchCustomer();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchCustomer();
            }
        },
    },
    methods: {
        dismissModal() {
            this.add_cr_modal = false;
        },
        showOrderModal(data) {
            this.customer_details = data;
            this.add_cr_modal = true;
        },
        async searchCustomer() {
            let page = 1;
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.customer_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchCustomers(1);
        },

        async fetchCustomers(page) {
            this.showLoader();
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.customer_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
