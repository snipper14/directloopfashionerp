<template>
    <v-app>
        <div >
            <div v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <PreorderNav />
                    </div>
                    <div class="col-md-10">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5>Completed Orders</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <input
                                            type="text"
                                            placeholder="Search.."
                                            class="form-control small-input"
                                            v-model="params.search"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th scope="col">Customer</th>
                                                <th>Customer Phone</th>
                                                <th>OrderNo</th>
                                                <th>Branch</th>
                                                <th scope="col">Total Qty</th>
                                                <th scope="col">Total VAT</th>
                                                <th scope="col">
                                                    Total Amount
                                                </th>
                                                <th>User</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in order_data.data"
                                                :key="i"
                                            >
                                                <td>
                                                    <v-icon
                                                        title="Download"
                                                        v-if="
                                                            isDownloadPermitted
                                                        "
                                                        class="actions-icon"
                                                        color="primary"
                                                        left
                                                        @click="
                                                            printOrders(data)
                                                        "
                                                        >mdi-printer</v-icon
                                                    >
                                                    <v-icon
                                                        @click="
                                                            viewDetails(data)
                                                        "
                                                        class="actions-icon"
                                                        color="info"
                                                        >mdi-eye</v-icon
                                                    >
                                                   
                                                </td>
                                                <td>
                                                    {{ data.customer_name }}
                                                </td>
                                                <td>
                                                    {{ data.customer_phone }}
                                                </td>
                                                <td>{{ data.order_no }}</td>
                                                <td>
                                                    {{ data.branch.branch }}
                                                </td>
                                                <td>{{ data.sum_qty }}</td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_row_vat
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_row_amount
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ data.user.name }}</td>
                                                <th>{{data.order_status}}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <pagination
                                    v-if="order_data !== null"
                                    v-bind:results="order_data"
                                    v-on:get-page="fetch"
                                ></pagination>
                                Items Count {{ order_data.total }}
                            </div>
                             <div class="row">
                            <div
                                class="col-md-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" x-small>
                                        Export Excel
                                    </v-btn>
                                </export-excel>

                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-btn color="info" x-small>
                                        Export CSV
                                    </v-btn>
                                </export-excel>
                                <div>
                                    <v-btn
                                        @click="printDetailedData()"
                                        v-if="isDownloadPermitted"
                                        color="success" x-small
                                    >
                                        Print Detailed Records
                                    </v-btn>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <center v-else>
                <b style="color: red; font-size: 1.2rem"><unauthorized /></b>
            </center>
           
            <v-row>
                <v-col cols="4">
                    <ReprintPreorders
                        ref="ReprintPreorders"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :order_data="order_details"
                    />
                </v-col>
            </v-row>
            <Modal v-model="show_details_modal" width="1000px">
                <modal-preorder-details
                    v-if="show_details_modal"
                    :details_data="details_data"
                    v-on:dismiss-modal="dismissModal"
                />
            </Modal>
        </div>
    </v-app>
</template>

<script>
import PreorderNav from "./PreorderNav.vue";
import AddPreorders from "./AddPreorders.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../utilities/Pagination.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import ReprintPreorders from "./ReprintPreorders.vue";
import ModalPreorderDetails from "./ModalPreorderDetails.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,

                add_order: false,
            },
            show_details_modal: false,
            details_data: null,
            load_printer_component: false,
          
            order_data: [],
            order_details: null,
            customer_details: null,
            form_data: null,
            search: "",
            params: {
                search: "",
              
                status: "ready",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
            show_loader:true,
        };
    },
    components: {
        PreorderNav,
        Pagination,
        Unauthorized,
        AddPreorders,
        ReprintPreorders,
        ModalPreorderDetails,
    },

    mounted() {
        this.fetch(1);
    },
    watch: {
        params: {
            handler() {
             this.show_loader=false
             this.fetch(1)
            },
            deep: true,
        },
     
    },
    methods: {
        async fetchExcel() {
            this.showLoader()
           const res = await this.getApi(
                "data/preorders/fetchGroupedPreorder",
                {
                    params: {
                        
                        ...this.params,
                    },
                }
            );
            this.hideLoader()
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        customer_name: data.customer_name,
                        customer_phone: data.customer_phone,
                        order_no: data.order_no,
                        branch: data.branch.branch,
                        sum_qty: data.sum_qty,
                        sum_row_vat: data.sum_row_vat,
                        sum_row_amount: data.sum_row_amount,
                        user: data.user.name,
                        order_status: data.order_status,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        dismissModal() {
            this.show_details_modal = false;
            
            this.fetch(1);
        },
        viewDetails(details) {
            this.details_data = details;
            this.show_details_modal = true;
        },
        async printOrders(data) {
            this.showLoader();
            const res = await this.getApi("data/preorders/getByOrderNo", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.load_printer_component = true;
                this.order_details = res.data.res;
                this.form_data = res.data.sales_details;
                setTimeout(() => {
                    //disable browser printing

                    this.$refs.ReprintPreorders.printReceipt();
                    this.load_printer_component = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async deleteOrder(data, i) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "DELETE",
                "data/preorders/deleteOrder/" + data.order_no,
                {}
            );
            this.hideLoader();
            if (res.status === 200) {
                this.order_data.data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
      
      
       
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchCustomers(1);
        },

        async fetch(page) {
            this.show_loader?this.showLoader():"";
            const res = await this.getApi(
                "data/preorders/fetchGroupedPreorder",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );
          this.show_loader?  this.hideLoader():"";

            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
