<template>
    <div class="">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <POSideNav />
            </div>
            <div class="col-md-10">
                <div class="" v-if="isReadPermitted">
                    <div>
                        <v-btn color="primary" @click="viewItemizedReport()"
                            >View Itemized Report</v-btn
                        >
                        <div class="card">
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
                                                v-model="params.supplier_id"
                                                :multiple="false"
                                                :options="supplier_options"
                                                :show-count="true"
                                                placeholder="Select "
                                            />
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
                                    <div class="d-flex flex-column mr-2">
                                        <span
                                            class="badge badge-info totals-badge"
                                        >
                                            Total Tax:
                                            {{
                                                cashRounded(
                                                    grn_totals_data.sum_tax_amount
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="badge badge-primary totals-badge"
                                        >
                                            Total Invoice :
                                            {{
                                                cashRounded(
                                                    grn_totals_data.sum_sub_total
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="table-responsive"
                                    v-if="grouped_report"
                                >
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3 custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Delivery No</th>
                                                <th scope="col">Order No</th>
                                                <th>Vendor</th>

                                                <th>
                                                    Received Date
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_received_date
                                                        "
                                                        sort-key="sort_received_date"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th>
                                                    User<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_user
                                                        "
                                                        sort-key="sort_user"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>

                                                <th scope="col">
                                                    Qty Order<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_qty_ordered
                                                        "
                                                        sort-key="sort_sum_qty_ordered"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th scope="col">
                                                    Qty Delivered
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_qty_delivered
                                                        "
                                                        sort-key="sort_sum_qty_delivered"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                  <th scope="col">
                                                  Discount Amount
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_discount_amount
                                                        "
                                                        sort-key="sort_discount_amount"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th>Tax Total</th>
                                                <th>
                                                    Total<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_sub_total
                                                        "
                                                        sort-key="sort_sum_sub_total"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>

                                                <th>CU INVOICE NO</th>
                                                <th>Branch</th>
                                                <th></th>
                                                <th></th>
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
                                                    {{
                                                        cashFormatter(
                                                            data.sum_discount_amount
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashRounded(
                                                            data.sum_tax_amount
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_sub_total
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{ data.cu_invoice_no }}
                                                </td>
                                                <td>
                                                    {{ data.branch.branch }}
                                                </td>
                                                <td>
                                                    <v-btn
                                                        v-if="isUpdatePermitted"
                                                        @click="
                                                            generateReturnsGRN(
                                                                data
                                                            )
                                                        "
                                                        color="info"
                                                        x-small
                                                    >
                                                        Generate Return GRN
                                                    </v-btn>
                                                </td>
                                                <td>
                                                    <v-btn
                                                        v-if="false"
                                                        @click="
                                                            downloadPO(data)
                                                        "
                                                        color="primary"
                                                        x-small
                                                    >
                                                        Download GRN
                                                    </v-btn>
                                                    <v-btn
                                                        color="primary"
                                                        x-small
                                                        @click="printGRN(data)"
                                                        >Print GRN</v-btn
                                                    >
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination
                                    v-if="order_data !== null"
                                    v-bind:results="order_data"
                                    v-on:get-page="fetchDeliveryNotes"
                                ></Pagination>
                            </div>
                        </div>
                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small
                                        >Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small
                                        >Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
        <grn-itemized-report
            v-if="active.itemized_report"
            v-on:dashboard-active="setActiveRefresh"
        />

        <delivery-details
            v-if="active.delivery_details"
            v-on:dashboard-active="setActiveDonotRefresh"
            :delivery_details="delivery_details"
        />

        <addgrn-returns-component
            v-if="active.return_grn"
            v-on:dashboard-active="setActiveRefresh"
            :delivery_details="delivery_details"
        />
        <PrintGRNTemplate
            v-if="print_grn_modal"
            ref="PrintPurchaseOrder"
            :grn_details="grn_details"
        />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import DownloadGrn from "./DownloadGrn.vue";
import DeliveryDetails from "./DeliveryDetails.vue";
import GrnItemizedReport from "./GrnItemizedReport.vue";
import AddgrnReturnsComponent from "./AddgrnReturnsComponent.vue";
import SortButtons from "../../utilities/SortButtons.vue";
import PrintGRNTemplate from "../../utilities/PrintGRNTemplate.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                delivery_details: false,
                itemized_report: false,
                print_grn: false,
                return_grn: false,
            },
            grouped_report: true,

            show_loading: true,
            delivery_modal: false,
            grn_download_data: null,
            order_details: null,
            delivery_details: null,
            grn_totals_data: {},
            params: {
                order_no: "",
                delivery_no: "",
                supplier_id: null,
                from: null,
                to: null,
            },
            sort_params: {
                sort_sum_sub_total: "",
                sort_sum_qty_ordered: "",
                sort_sum_qty_delivered: "",
                sort_received_date: "",
                sort_user: "",
                sort_discount_amount:""
            },
            order_data: [],
            supplier_data: [],
            supplier_options: null,
            print_grn_modal: false,
            grn_details: null,
        };
    },
    components: {
        Treeselect,
        POSideNav,
        Pagination,
        DownloadGrn,
        DeliveryDetails,
        GrnItemizedReport,
        AddgrnReturnsComponent,
        SortButtons,
        PrintGRNTemplate,
    },
    watch: {
        params: {
            handler() {
                this.show_loading = false;
                this.concurrentApiReq();
            },
            deep: true,
        },
        sort_params: {
            handler() {
                this.show_loading = true;
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async printGRN(data) {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryDetails",
                { params: { delivery_no: data.delivery_no } }
            );
            if (res.status === 200) {
                this.grn_details = res.data;
                this.print_grn_modal = true;

                setTimeout(() => {
                    this.$refs.PrintPurchaseOrder.printBill();
                    this.print_grn_modal = false;
                }, 1000);
            } else {
                this.swr("Server error try again later");
            }
        },
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        generateReturnsGRN(data) {
            this.delivery_details = data;
            this.setActive(this.active, "return_grn");
        },
        viewItemizedReport() {
            this.setActive(this.active, "itemized_report");
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        setActiveDonotRefresh() {
            this.setActive(this.active, "dashboard");
        },
        viewDetails(data) {
            this.delivery_details = data;
            this.setActiveComponent("delivery_details");
        },
        async downloadPO(data) {
            this.showLoader();
            const dataObj = {
                order_no: data.order_no,
                supplier_id: data.supplier_id,
                delivery_no: data.delivery_no,
                department_id: data.department_id,
                cu_invoice_no: data.cu_invoice_no,
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

        async concurrentApiReq() {
            this.show_loading ? this.showLoader() : null;
            await Promise.all([
                this.fetchDeliveryNotes(1),
                this.getAllSuppliers(),
                this.fetchDeliveryTotals(),
            ]).then(function (results) {
                return results;
            });
            this.show_loading ? this.hideLoader() : null;
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
            this.grn_totals_data = res.data;
        },
        async fetchExcel() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryNotes",
                {
                    params: {
                        ...this.params,
                        ...this.sort_params,
                    },
                }
            );
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    delivery_no: data.delivery_no,
                    order_no: data.order_no,
                    supplier: data.supplier.company_name,
                    received_date: data.received_date,
                    user: data.user.name,

                    sum_qty_ordered: data.sum_qty_ordered,
                    sum_qty_delivered: data.sum_qty_delivered,
                    sum_tax_amount: data.sum_tax_amount,
                    sum_sub_total: data.sum_sub_total,
                    cu_invoice_no: data.cu_invoice_no,
                    batch_no: data.batch_no,
                    expiry_date: data.expiry_date,
                });
            });
            return data_array;
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
                        ...this.sort_params,
                    },
                }
            );
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
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
