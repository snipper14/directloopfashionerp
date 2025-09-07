<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <v-btn
                        class="ma-2 v-btn-primary"
                        @click="$emit('dashboard-active')"
                        color="primary"
                        dark
                    >
                        <v-icon dark left> mdi-arrow-left </v-icon>
                        Back
                    </v-btn>
                    <div class="card">
                        <div class="card-header">
                            {{ details_data.product_name }} History
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Entry Type</th>
                                            <th>Ref</th>
                                            <th>Qty</th>
                                            <th>cummulative qty</th>
                                            <th>Datetime</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in history_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                <span
                                                    v-if="
                                                        data.table ==
                                                        'AllSaleDetailsReport'
                                                    "
                                                    >Sales</span
                                                >
                                                <span
                                                    v-else-if="
                                                        data.table ==
                                                        'IssueStock'
                                                    "
                                                    >Dispatch</span
                                                >
                                                <!-- <span
                                                    v-else-if="
                                                        data.table ==
                                                        'PurchaseOrderReceivable'
                                                    "
                                                    >Purchase Received </span
                                                > -->
                                                <span
                                                    v-else-if="
                                                        data.table ==
                                                        'Purchase delivered'
                                                    "
                                                    >Purchase Received
                                                    <v-btn
                                                        x-small
                                                        @click="
                                                            reprintGrn(data.ref)
                                                        "
                                                        color="primary"
                                                        >print grn</v-btn
                                                    ></span
                                                >
                                                <span
                                                    v-else-if="
                                                        data.table ==
                                                        'Retail sale'
                                                    "
                                                    >{{ data.table }}
                                                    <v-btn
                                                        x-small
                                                        @click="
                                                            reprintSales(
                                                                data.ref
                                                            )
                                                        "
                                                        color="info"
                                                        >reprint</v-btn
                                                    ></span
                                                >
                                                <span v-else>{{
                                                    data.table
                                                }}</span>
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        viewDetails(data)
                                                    "
                                                    >{{ data.ref }}</router-link
                                                >
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>{{ data.cumulative_qty }}</td>
                                            <td>
                                                {{
                                                    formatMonthDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <Pagination
                                v-if="history_data !== null"
                                v-bind:results="history_data"
                                v-on:get-page="fetchMovementHistory"
                            ></Pagination>
                            Items Count {{ history_data.total }}
                        </div>

                        <div class="row">
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn x-small color="secondary">
                                        Export CSV</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="erp_stock.csv"
                                >
                                    <v-btn color="secondary" x-small>
                                        Export CSV</v-btn
                                    >
                                </export-excel>
                                <v-btn
                                    color="primary"
                                    class="mr-2"
                                    small
                                    @click="printInfo()"
                                    >Print</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div></v-app
        >
        <Modal v-model="view_sale_history_modal" width="800px">
            <sale-detail-history
                :info_data="info_data"
                v-if="view_sale_history_modal"
            />
        </Modal>
        <Modal v-model="receivable_history_modal" width="800px">
            <purchase-history-details
                :delivery_details="info_data"
                v-if="receivable_history_modal"
            />
        </Modal>

        <Modal v-model="dispatch_modal" width="1200px">
            <dispatch-history-modal
                :info_data="info_data"
                v-if="dispatch_modal"
            />
        </Modal>
        <Modal v-model="pos_credit_note_modal" width="1200px">
            <pos-credit-note
                :info_data="info_data"
                v-if="pos_credit_note_modal"
            />
        </Modal>
        <Modal v-model="invoice_sales_modal" width="1200px">
            <invoice-details-history-modal
                :info_data="info_data"
                v-if="invoice_sales_modal"
            />
        </Modal>
        <Modal v-model="invoice_credit_modal" width="1200px">
            <invoice-credit-modal-history
                :info_data="info_data"
                v-if="invoice_credit_modal"
            />
        </Modal>
        <Modal v-model="stock_stake_modal" width="1200px">
            <bulk-stock-take-report-history
                :info_data="info_data"
                v-if="stock_stake_modal"
                :details_data="details_data"
            />
        </Modal>
        <Modal v-model="merged_modal" width="1000px">
            <merged-history-modal
                :info_data="info_data"
                v-if="merged_modal"
                :details_data="details_data"
        /></Modal>
        <Modal v-model="manual_stock_deduction_modal" width="1000px">
            <manual-decrement-history-modal
                :info_data="info_data"
                v-if="manual_stock_deduction_modal"
                :details_data="details_data"
            />
        </Modal>
         <Modal v-model="manual_stock_increment_modal" width="1000px">
            <manual-increment-history-modal
                :info_data="info_data"
                v-if="manual_stock_increment_modal"
                :details_data="details_data"
            />
        </Modal>
         <Modal v-model="manual_stocktake_modal" width="1000px">
            <manual-update-modal
                :info_data="info_data"
                v-if="manual_stocktake_modal"
                :details_data="details_data"
            />
        </Modal>
          <Modal v-model="view_waste_modal" width="1000px">
            <waste-modal
                :info_data="info_data"
                v-if="view_waste_modal"
                :details_data="details_data"
            />
        </Modal>
           <Modal v-model="view_interbranch_transfer_modal" width="1000px">
            <inter-branch-trasfer-history-details
                :info_data="info_data"
                v-if="view_interbranch_transfer_modal"
                :details_data="details_data"
            />
        </Modal>
          <Modal v-model="view_interbranch_receive_modal" width="1000px">
            <inter-branch-receivable-history-details
                :info_data="info_data"
                v-if="view_interbranch_receive_modal"
                :details_data="details_data"
            />
        </Modal>
        <PrintItemMovementHistory
            ref="PrintItemMovementHistory"
            v-if="show_print"
            :print_data="print_data"
            :details_data="details_data"
        />
        <PrintGRNTemplate
            v-if="print_grn_modal"
            ref="PrintPurchaseOrder"
            :grn_details="grn_details"
        />
        <div class="row">
            <div class="col-md-4">
                <ReprintPosReceipt
                    ref="ReceiptPrintComponent"
                    v-if="load_printer_component"
                    :form_data="form_data"
                    :order_data="order_data"
                    :etims_data="etims_data"
                />
            </div>
        </div>
    </div>
</template>

<script>
import ReprintPosReceipt from "../../pos/pos_report/ReprintPosReceipt.vue";
import Pagination from "../../utilities/Pagination.vue";
import PrintGRNTemplate from "../../utilities/PrintGRNTemplate.vue";
import BulkStockTakeReportHistory from "./movementhistory/BulkStockTakeReportHistory.vue";
import DispatchHistoryModal from "./movementhistory/DispatchHistoryModal.vue";
import InterBranchReceivableHistoryDetails from './movementhistory/InterBranchReceivableHistoryDetails.vue';
import InterBranchTrasferHistoryDetails from './movementhistory/InterBranchTrasferHistoryDetails.vue';
import InvoiceCreditModalHistory from "./movementhistory/InvoiceCreditModalHistory.vue";
import InvoiceDetailsHistoryModal from "./movementhistory/InvoiceDetailsHistoryModal.vue";
import ManualDecrementHistoryModal from './movementhistory/ManualDecrementHistoryModal.vue';
import ManualIncrementHistoryModal from './movementhistory/ManualIncrementHistoryModal.vue';
import ManualUpdateModal from "./movementhistory/ManualUpdateModal.vue";
import MergedHistoryModal from "./movementhistory/MergedHistoryModal.vue";
import PosCreditNote from "./movementhistory/PosCreditNote.vue";
import PurchaseHistoryDetails from "./movementhistory/PurchaseHistoryDetails.vue";
import SaleDetailHistory from "./movementhistory/SaleDetailHistory.vue";
import WasteModal from './movementhistory/WasteModal.vue';
import PrintItemMovementHistory from "./PrintItemMovementHistory.vue";
export default {
    components: {
        Pagination,
        SaleDetailHistory,
        PurchaseHistoryDetails,
        DispatchHistoryModal,
        PosCreditNote,
        InvoiceDetailsHistoryModal,
        InvoiceCreditModalHistory,
        BulkStockTakeReportHistory,
        PrintItemMovementHistory,
        PrintGRNTemplate,
        ReprintPosReceipt,
        MergedHistoryModal,
        ManualUpdateModal,
        ManualDecrementHistoryModal,
        ManualIncrementHistoryModal,
        WasteModal,
        InterBranchTrasferHistoryDetails,
        InterBranchReceivableHistoryDetails,
    },
    props: ["details_data"],
    data() {
        return {
            grn_details: null,
            print_grn_modal: false,
            show_print: false,
            print_data: [],
            view_sale_history_modal: false,
            receivable_history_modal: false,
            dispatch_modal: false,
            pos_credit_note_modal: false,
            invoice_sales_modal: false,
            invoice_credit_modal: false,
            stock_stake_modal: false,
            manual_stock_increment_modal:false,
            history_data: [],
            view_interbranch_receive_modal:false,
            info_data: null,
            form_data: null,
            order_data: null,
            etims_data: null,
            merged_modal: false,
            view_waste_modal:false,
            load_printer_component: false,
            manual_stocktake_modal: false,
            manual_stock_deduction_modal: false,
            view_interbranch_transfer_modal:false,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async reprintSales(ref) {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/fetchSalesByReceiptNo",
                { params: { ref_no: ref } }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.form_data = res.data.form_data;
                this.order_data = res.data.order_items;
                this.etims_data = res.data.form_data;
                this.load_printer_component = true;
                setTimeout(() => {
                    this.$refs.ReceiptPrintComponent.printReceipt();
                }, 1000);
            } else {
                this.swr("Server error try again later");
            }
        },
        async reprintGrn(ref) {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryDetails",
                { params: { delivery_no: ref } }
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
        viewDetails(data) {
            console.log(JSON.stringify(data));
            
            if (data.table == "Interbranch Receivable") {
                this.info_data = data;
                this.view_interbranch_receive_modal = true;
            }
             if (data.table == "Interbranch Transfer") {
                this.info_data = data;
                this.view_interbranch_transfer_modal = true;
            }
              if (data.table == "Stock waste") {
                this.info_data = data;
                this.view_waste_modal = true;
            }
               if (data.table == "Retail sale") {
                this.info_data = data;
                this.view_sale_history_modal = true;
            }
            if (data.table == "Purchase delivered") {
                this.info_data = data;
                this.receivable_history_modal = true;
            }
            if (data.table == "Dispatch") {
                this.info_data = data;
                this.dispatch_modal = true;
            }
            if (data.table == "pos credit note") {
                this.info_data = data;
                this.pos_credit_note_modal = true;
            }
            if (data.table == "Invoice sales") {
                this.info_data = data;
                this.invoice_sales_modal = true;
            }
            if (data.table == "Invoice Credit Note") {
                this.info_data = data;
                this.invoice_credit_modal = true;
            }
            if (data.table == "Stocktake") {
                this.info_data = data;
                this.stock_stake_modal = true;
            }
            if (data.table == "Merged Inventory") {
                this.info_data = data;
                this.merged_modal = true;
            }
            if (data.table == "Stock Direct Update") {
                this.manual_stocktake_modal = true;
            }
            this.info_data = data;
            if (data.table == "Manual Stock Deduction") {
                this.manual_stock_deduction_modal = true;
            }
             if (data.table == "Manual Stock Increment") {
                this.manual_stock_increment_modal = true;
            }
            
        },
        async printInfo() {
            this.showLoader();
            const res = await this.getApi(
                "data/inventory/fetchItemHistoryExcel",
                {
                    params: {
                        stock_id: this.details_data.id,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.show_print = true;
                this.print_data = res.data;
                setTimeout(() => {
                    this.$refs.PrintItemMovementHistory.printBill();
                    this.show_print = false;
                }, 1000);
            }
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/inventory/fetchItemHistoryExcel",
                {
                    params: {
                        stock_id: this.details_data.id,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    ref: data.ref,
                    table: data.table,
                    qty: data.qty,
                    created_at: this.formatMonthDateTime(data.created_at),
                });
            });
            return data_array;
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchMovementHistory(1)]);
            this.hideLoader();
        },
        async fetchMovementHistory(page) {
            const res = await this.getApi(
                "data/inventory/fetchMovementHistory",
                {
                    params: {
                        stock_id: this.details_data.id,
                        page: page,
                    },
                }
            );
            if (res.status == 200) {
                this.history_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
