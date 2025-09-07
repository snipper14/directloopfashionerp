<template>
    <div class="container" v-if="isReadPermitted">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <ImportExcel/>
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_customer')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Customer Management</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="params.search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Company <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_customer
                                            "
                                            sort-key="sort_customer"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                        <th scope="col">Account</th>
                                        <th scope="col">tax_deduction</th>
                                        <th>Price Group</th>
                                        <th>Loyalty Points</th>
                                        <th>Redeemed Loyalty Points</th>
                                        <th>Available Points</th>
                                        <th scope="col">Etims ID</th>
                                        <th scope="col">Co. Contacts</th>
                                        <th scope="col">Co. Address</th>
                                        <th scope="col">Co. Postal</th>
                                        <th scope="col">City</th>
                                        <th scope="col">Country</th>
                                        <th scope="col">C. Email</th>
                                        <th scope="col">Contact Person</th>
                                        <th scope="col">Contact Email</th>
                                        <th scope="col">Contact Phone</th>
                                        <th scope="col">PIN</th>
                                        <th scope="col">Credit Limit</th>
                                        <th>Diagnosis</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in customer_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editCustomer(data)
                                                "
                                            >
                                                {{
                                                    data.company_name
                                                }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.acc_code }}</td>
                                        <td>{{ data.vat_deduction }}</td>
                                        <td>
                                            {{ data.price_group.price_group }}
                                        </td>
                                             <td>
                                            {{
                                                cashFormatter(
                                                    data.loyalty_points
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.redeemed_loyalty_points
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.loyalty_points -
                                                        data.redeemed_loyalty_points
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ data.etims_id }}
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
                                        <td>{{ data.pin }}</td>
                                        <td>{{ data.credit_limit }}</td>
                                       
                                        <td>
                                            <button
                                                v-if="isDeletePermitted"
                                                @click="
                                                    deleteRecord(data.id, i)
                                                "
                                                class="btn btn-danger custom-button"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                        <td>
                                            <v-btn
                                                v-if="isUpdatePermitted"
                                                @click="updateEtims(data, i)"
                                                title="UPDATE"
                                                color="primary"
                                                x-small
                                                >Update Etims</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="customer_data !== null"
                            v-bind:results="customer_data"
                            v-on:get-page="fetchCustomers"
                        ></Pagination>
                        Items Count {{ customer_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllCustomers"
                                    worksheet="My Worksheet"
                                    name="customers.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllCustomers"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="customers.xls"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <add-customer
            v-if="active.add_customer"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-customer>
        <edit-customer
            v-if="active.edit_customer"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-customer>
        <Modal v-model="add_etims_modal">
            <add-customer-etims-modal
                v-if="add_etims_modal"
                :edit_data="edit_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
    </div>
</template>

<script>
import AddCustomer from "./AddCustomer.vue";
import EditCustomer from "./EditCustomer.vue";
import Pagination from "../utilities/Pagination.vue";
import ImportExcel from "./ImportExcel";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import AddCustomerEtimsModal from "./AddCustomerEtimsModal.vue";
import SortButtons from '../utilities/SortButtons.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
            },
            add_etims_modal: false,
            customer_data: [],
            edit_data: null,
            pdf_data: [],

            search: "",
            params: {
                search:""
            },
            sort_params:{
                sort_customer:""
            },
            index: null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AddCustomer,
        EditCustomer,
        VueHtml2pdf,
        Pagination,
        ImportExcel,
        AddCustomerEtimsModal,
        SortButtons,
    },
    mounted() {
        this.fetchCustomers(1);
    },
    watch: {
         params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchCustomers(1);
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchCustomers(1);
            }, 500),
        },
    },
    methods: {
          updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async updateEtims(data, i) {
            this.index = i;
            this.edit_data = data;
            this.add_etims_modal = true;
        },
        dismissDialog(data) {
            this.add_etims_modal = false;
            console.log(JSON.stringify(this.data));
            this.customer_data.data[this.index] = data;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/customers/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.customer_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editCustomer(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_customer");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllCustomers();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllCustomers() {
            const res = await this.getApi("data/customers/fetchAll", "");

            
             const data_array = [];
            res.data.results.map((data) => {
                data_array.push({
                    id: data.id,
                    customer_name: data.company_name,
                    company_phone: data.company_phone,

                    address: data.address,
                    postal_address: data.postal_address,

                    city: data.city,
                    country: data.country,
                    company_email: data.company_email,
                    bank_name: data.bank_name,
                    bank_branch: data.bank_branch,
                    account_name: data.account_name,
                    account_no: data.account_no,
                    acc_code: data.acc_code,
                    contact_person: data.contact_person,
                    contact_email: data.contact_email,
                    contact_phone: data.contact_phone,
                    pin:data.pin,
                    credit_limit:data.credit_limit,
                    vat_deduction:data.vat_deduction
                });
            });
            return data_array;
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
                    ...this.sort_params,
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
