<template>
    <div class="container" v-if="isReadPermitted">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <import-supplier-excel />
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_supplier')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Supplier Management</h5>
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
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Company
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                    <th scope="col">Co. Contacts</th>
                                    <th scope="col">Co. Address</th>
                                    <th scope="col">Co. Postal</th>
                                    <th scope="col">City</th>
                                    <th scope="col">Country</th>
                                    <th scope="col">C. Email</th>
                                    <th scope="col">Contact Person</th>
                                    <th scope="col">Contact Email</th>
                                    <th scope="col">Contact Phone</th>
                                    <th scope="col">pin</th>
                                    <th scope="col">Credit Limit</th>
                                    <th>WHT</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in supplier_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editSupplier(data)"
                                        >
                                            {{ data.company_name }}</router-link
                                        >
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
                                    <td>
                                        {{ cashFormatter(data.credit_limit) }}
                                    </td>
                                    <td>{{data.deduct_wht}}</td>
                                    <td>
                                        <button
                                            v-if="isDeletePermitted"
                                            @click="deleteRecord(data.id, i)"
                                            class="btn btn-danger custom-button"
                                        >
                                            Delete
                                        </button>
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

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllSupplier"
                                    worksheet="My Worksheet"
                                    name="supplier_excel.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllSupplier"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="supplier_excel.xls"
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

        <add-supplier
            v-if="active.add_supplier"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-supplier>
        <edit-supplier
            v-if="active.edit_supplier"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-supplier>
    </div>
</template>

<script>
import AddSupplier from "./AddSupplier.vue";
import EditSupplier from "./EditSupplier.vue";
import Pagination from "../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import ImportSupplierExcel from "./ImportSupplierExcel.vue";
import SortButtons from '../utilities/SortButtons.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_supplier: false,
                edit_supplier: false,
            },
            sort_params: {
                sort_supplier: "",
            },
            supplier_data: [],
            edit_data: null,
            pdf_data: [],

           
            params: {
                search:""
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
        AddSupplier,
        EditSupplier,
        VueHtml2pdf,
        Pagination,
        ImportSupplierExcel,
        SortButtons,
    },
    mounted() {
        this.fetchSupplier(1);
    },
    watch: {
       
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchSupplier(1);
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchSupplier(1);
            }, 500),
        },
      
    },
    methods: {
         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/suppliers/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.supplier_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editSupplier(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_supplier");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllSupplier();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllSupplier() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

              const data_array = [];
            res.data.results.map((data) => {
                data_array.push({
                    id: data.id,
                    company_name: data.company_name,
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
                    vat_deduction:data.vat_deduction,
                    deduct_wht:data.deduct_wht
                });
            });
            return data_array;
        },

        async fetchSupplier(page) {
          this.showLoader()
            const res = await this.getApi("data/suppliers/fetch", {
                params: {
                    page,
                   ...this.sort_params,
                    ...this.params,
                },
            });
            this.hideLoader()
            if (res.status === 200) {
                this.supplier_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchSupplier(1);
        },

       
    },
};
</script>
<style scoped></style>
