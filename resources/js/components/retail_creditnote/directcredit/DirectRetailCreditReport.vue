<template>
    <v-app>
        <div >
            <div v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <direct-credit-nav />
                    </div>
                    <div class="col-md-10">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5>Active Pre-Orders</h5>
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
                                                <th>Credit No.</th>
                                                 <th>Ref No.</th>
                                                <th>Branch</th>
                                                <th scope="col">Total Qty</th>
                                                <th scope="col">Total VAT</th>
                                                <th scope="col">
                                                    Total Amount
                                                </th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in array_data.data"
                                                :key="i"
                                            >
                                                <td>
                                                  
                                                    <v-icon
                                                        @click="
                                                            viewDetails(data)
                                                        "
                                                        class="actions-icon"
                                                        color="info"
                                                        >mdi-eye</v-icon
                                                    >
                                                    <v-icon
                                                        title="DELETE"
                                                        @click="
                                                            deleteCreditNote(data, i)
                                                        "
                                                        v-if="isDeletePermitted"
                                                        class="actions-icon"
                                                        color="red"
                                                        >mdi-delete</v-icon
                                                    >
                                                </td>
                                               
                                                <td>
                                                    {{ data.customer.company_name }}
                                                </td>
                                                <td>
                                                    {{ data.customer.company_phone }}
                                                </td>
                                                <td>{{ data.credit_no }}</td>
                                                  <td>{{ data.ref_no }}</td>
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
                                                <td><a target="_blank" rel="noopener noreferrer" :href="data.img_url">File</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <pagination
                                    v-if="array_data !== null"
                                    v-bind:results="array_data"
                                    v-on:get-page="fetch"
                                ></pagination>
                                Items Count {{ array_data.total }}
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
                    <reprint-print-direct-cr
                        ref="ReprintPreorders"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :array_data="order_details"
                    />
                </v-col>
            </v-row>
            <Modal v-model="show_details_modal" width="1000px">
                <modal-retails-credit-details
                    v-if="show_details_modal"
                    :details_data="details_data"
                    v-on:dismiss-modal="dismissModal"
                />
            </Modal>
        </div>
    </v-app>
</template>

<script>


import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";


import DirectCreditNav from '../DirectCreditNav.vue';
import ModalRetailsCreditDetails from './ModalRetailsCreditDetails.vue';
import ReprintPrintDirectCR from './ReprintPrintDirectCR.vue';
import Unauthorized from '../../utilities/Unauthorized.vue';
import Pagination from '../../utilities/Pagination.vue';

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
          
            array_data: [],
            order_details: null,
            customer_details: null,
            form_data: null,
            search: "",
            params: {
                search: "",
              
                status: "completed",
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
      
      
        DirectCreditNav,
        ModalRetailsCreditDetails,
        ReprintPrintDirectCR,
        Unauthorized,
        Pagination,
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
        dismissModal() {
            this.show_details_modal = false;
            
            this.fetch(1);
        },
        viewDetails(details) {
            this.details_data = details;
            this.show_details_modal = true;
        },
       
        async deleteCreditNote(data, i) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/directretail_creditnote/deleteCreditNote",  data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.array_data.data.splice(i, 1);
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
                "data/directretail_creditnote/groupByCreditNo",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );
          this.show_loader?  this.hideLoader():"";

            if (res.status === 200) {
                this.array_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
