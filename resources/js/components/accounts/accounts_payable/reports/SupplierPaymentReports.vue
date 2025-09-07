<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
             <div class="col-2">
                <payable-report-nav/>
            </div>
            <div class="col-md-10">
                
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center "
                    >
                        <div class="col-md-5">
                            <h5>Supplier Payment Reports</h5>
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
                    <div class="row">
                        
                        <div class="col-md-4">
                            <b> TOTAL PAYMENT:</b>
                            {{ cashFormatter(sum_amount_paid) }}
                        </div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Company

                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Total Payment<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_amount_paid
                                            "
                                            sort-key="sort_total_amount_paid"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                     <th scope="col">User</th>

                                   <th scope="col">View Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in supplier_payment_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.supplier
                                                ? data.supplier.company_name
                                                : ""
                                        }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.total_amount_paid) }}
                                    </td>
                                    <td>
                                        {{ data.user?data.user.name:'' }}
                                    </td>
                                    
                                    <td>
                                        <button
                                            class="btn btn-primary custom-button"
                                            @click="viewDetails(data)"
                                        >
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="supplier_payment_data !== null"
                            v-bind:results="supplier_payment_data"
                            v-on:get-page="fetchSupplierPaymentsReport"
                        ></Pagination>
                        Items Count {{ supplier_payment_data.total }}
                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
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
                <div v-else>
                   <unauthorized/>
                </div>
            </div>
        </div>

         <payment-statements
            v-if="active.view_details"
            :data="payable_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import PayableReportNav from "./PayableReportNav.vue";
import PaymentStatements from "../PaymentStatements.vue";
import Unauthorized from '../../../utilities/Unauthorized.vue'
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import SortButtons from '../../../utilities/SortButtons.vue';
export default {
    data() {
        return {
            active: {
                view_details: false,
              dashboard: true,
            
            },
            search: "",
            sum_amount_paid:0,
            supplier_payment_data: [],
           icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            },
            sort_params:{
                sort_supplier:"",
                sort_total_amount_paid:""
            }
        };
    },
    components: {
        Pagination,
        PaymentStatements,
        Unauthorized,
        PayableReportNav,
        SortButtons
    },
    mounted() {
        this.fetchSupplierPaymentsReport(1);
       
    },
    watch: {
        
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchSuppliers();
            }
        },
         sort_params: {
            handler: _.debounce(function (val, old) {
                this.fetchSupplierPaymentsReport(1);
            }, 500),
            deep: true,
        },
    },
    methods: {
         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        viewDetails(data) {
            this.payable_data = data;
            this.setActiveComponent("view_details");
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi("data/supplier_payment/exportExcel", "");
            this.hideLoader();
            var data_array = [];
            res.data.map(array_item => {
                data_array.push({
                    supplier: array_item.supplier ? array_item.supplier.company_name:"",
                    amount_paid: array_item.amount_paid,
                    outstanding_balance:array_item.outstanding_balance,
                    ref_no:array_item.ref_no,
                    date_paid:array_item.date_paid,
                    payment_details:array_item.payment_details,
                    user:array_item.user ? array_item.user.name:'',
                });
            });
            return data_array;
        },

        viewStaements(data) {
            this.supplier_info = data;
            this.setActiveComponent("view_stamt");
        },
        async searchSuppliers() {
            let page = 1;
            const res = await this.getApi(
                "data/supplier_payment/fetchSupplierPaymentReport",
                {
                    params: {
                        page,
                        search: this.search.length >= 2 ? this.search : "",
                        
                    }
                }
            );
            if (res.status === 200) {
                this.supplier_payment_data = res.data;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchSupplierPaymentsReport();
        },
    
        async fetchSupplierPaymentsReport(page) {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_payment/fetchSupplierPaymentReport",
                {
                    params: {
                        page,
                        search: this.search.length >= 3 ? this.search : "",
                      ...this.sort_params
                    }
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.supplier_payment_data = res.data;

                this.sum_amount_paid = this.supplier_payment_data.data.reduce(function(sum, val) {
                    return sum + val.total_amount_paid;
                }, 0);
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
