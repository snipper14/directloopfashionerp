<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <executive-nav />
            </div>
            <div class="col-md-10">
                <div class="container">
                    <div class="card" v-if="active.dashboard">
                        <div class="card-header">
                            <h3><b> GOODS RECEIVED NOTES(GRN)</b></h3>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-center">
                                <div class="form-group">
                                    <label for="">Delivery No</label>
                                    <input
                                        type="text"
                                        v-model="params.delivery_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Order No</label>
                                    <input
                                        type="text"
                                        v-model="params.order_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="supp_dropdown">
                                    <label>Supplier Name*</label>

                                    <div class="form-group">
                                        <treeselect
                                            :auto-load-root-options="false"
                                            v-model="params.supplier_id"
                                            :multiple="false"
                                            :options="supplier_options"
                                            :show-count="true"
                                            placeholder="Select "
                                            :load-options="getAllSuppliers"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="flex-row">
                                        <div class="d-flex flex-row mr-2">
                                            <span
                                                class="badge badge-info totals-badge"
                                            >
                                                Items Total:
                                                {{
                                                    cashFormatter(
                                                        purchase_total.sum_qty_delivered
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div class="d-flex flex-row">
                                            <span
                                                class="badge badge-primary totals-badge"
                                            >
                                                Total Purchases :
                                                {{
                                                    cashFormatter(
                                                        purchase_total.sum_sub_total
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="d-flex flex-column">
                                    <label> From Datetime *</label>

                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.from"
                                    ></date-picker>
                                </div>
                                <div class="d-flex flex-column mr-2">
                                    <label> To Datetime *</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.to"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="row">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3 custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Delivery No</th>
                                                <th scope="col">Order No</th>
                                                <th>Vendor</th>
                                                <th>Deadline Date</th>
                                                <th>Received Date</th>
                                                <th>User</th>

                                                <th scope="col">Qty Order</th>
                                                <th scope="col">
                                                    Qty Delivered
                                                </th>
                                                <th>TOTAL</th>
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
                                                    <router-link
                                                        to="#"
                                                        @click.native="
                                                            viewDetails(data)
                                                        "
                                                    >
                                                        {{
                                                            data.delivery_no
                                                        }}</router-link
                                                    >
                                                </td>
                                                <td>
                                                    {{ data.order_no }}
                                                </td>
                                                <td>
                                                    {{
                                                        data.supplier
                                                            ? data.supplier
                                                                  .company_name
                                                            : ""
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        data.purchase_order
                                                            .order_deadline
                                                    }}
                                                </td>
                                                <td>
                                                    {{ data.received_date }}
                                                </td>
                                                <td>
                                                    {{
                                                        data.user
                                                            ? data.user.name
                                                            : ""
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_qty_ordered
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_qty_delivered
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <b>{{
                                                        cashFormatter(
                                                            data.sum_sub_total
                                                        )
                                                    }}</b>
                                                </td>
                                                <td>
                                                    <v-btn
                                                        @click="
                                                            downloadPO(data)
                                                        "
                                                        color="primary"
                                                        x-small
                                                    >
                                                        Download GRN
                                                    </v-btn>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <pagination
                                v-if="order_data !== null"
                                v-bind:results="order_data"
                                v-on:get-page="fetchDeliveryNotes"
                            ></pagination>
                        </div>

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="info" small> Export Excel</v-btn>
                                   
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.csv"
                                >
                                   <v-btn color="success" small>Export CSV</v-btn>
                                    
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12" id="printMe">
                <download-grn
                    v-if="grn_download_data && active.print_grn"
                    :grn_download_data="grn_download_data"
                    ref="grnDownloadComponent"
                />
            </div>
        </div>
        <Modal title="Delivery Details" v-model="delivery_modal" width="1000px">
            <delivery-details
                v-if="delivery_modal"
                :delivery_details="delivery_details"
            />
        </Modal>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import ExecutiveNav from "./ExecutiveNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import DownloadGrn from "../../store/delivery_notes/DownloadGrn.vue";
import DeliveryDetails from "../../store/delivery_notes/DeliveryDetails.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                print_grn: false,
            },
            purchase_total: { sum_purchase: 0, sum_qty: 0 },
            delivery_modal: false,
            grn_download_data: null,
            order_details: null,
            delivery_details: null,
            params: {
                order_no: "",
                delivery_no: "",
                supplier_id: null,
                from: null,
                to: null,
            },
            order_data: [],
            supplier_data: [],
            supplier_options: null,
        };
    },
    components: {
        Treeselect,

        ExecutiveNav,
        Pagination,
        DownloadGrn,
        DeliveryDetails,
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        viewDetails(data) {
            this.delivery_details = data;
            this.delivery_modal = true;
        },
        async downloadPO(data) {
            this.showLoader();
            const dataObj = {
                order_no: data.order_no,
                supplier_id: data.supplier_id,
                delivery_no: data.delivery_no,
                department_id: data.department_id,
            };
            axios({
                url: "data/po_receivable/downloadDN",
                method: "POST",
                data: dataObj,
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.getTimeStamp() + "_DN" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
        async downloadGRN(data) {
            this.showLoader();
            const dataObj = {
                order_no: data.order_no,
                supplier_id: data.supplier_id,
                delivery_no: data.delivery_no,
            };
            const res = await this.callApi(
                "POST",
                "data/po_receivable/downloadDN",
                dataObj
            );
            this.hideLoader();
            if (res.status == 200) {
                this.grn_download_data = res.data;
                this.active.print_grn = true;
                await this.sleep(2000);
                await this.$htmlToPaper("printMe");
                await this.sleep(2000);
                this.grn_download_data = null;
                //  this.$refs.grnDownloadComponent.setValue();
            } else {
                this.servo();
            }
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },

        async searchRecords() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryNotes",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchDeliveryNotes(1),
                this.fetchDeliveryTotals(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
        },
        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

            if (res.status === 200) {
                this.supplier_data = res.data.results;
                this.supplier_options = this.modifyProductKey();
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchDeliveryNotes(page) {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryNotes",
                {
                    params: {
                        page,
                        ...this.params,
                    },
                }
            );
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchDeliveryTotals() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryTotals",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            if (res.status === 200) {
                this.purchase_total = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryNotes",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    company_name: data.supplier.company_name,
                    delivery_no: data.delivery_no,
                    order_no: data.order_no,
                    supply_deadline: data.purchase_order.order_deadline,
                    sum_qty_ordered: data.sum_qty_ordered,
                    sum_qty_delivered: data.sum_qty_delivered,
                    sum_sub_total: data.sum_sub_total,
                    user: data.user.name,
                });
            });
            return data_array;
        },
    },
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
#printMe {
    visibility: hidden;
}
</style>
