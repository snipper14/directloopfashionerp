<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                        Delivery No: {{ delivery_details.delivery_no }}
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Print Label</th>
                                        <th scope="col">Delivery No</th>
                                        <th scope="col">Order No</th>
                                        <th>Stock</th>
                                        <th>P.Price</th>
                                        <th>S.Price</th>
                                        <th>Qty Ordered</th>
                                        <th>Qty Delivered</th>
                                        <th>Sub Total</th>
                                        <th>Available Qty</th>
                                        <th>Batch No</th>
                                        <th>Expiry</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in delivery_data"
                                        :key="i"
                                    >
                                    <td><v-btn color="primary" x-small @click="printGrnLabel(data)">Print Label</v-btn></td>
                                        <td>
                                            {{ data.delivery_no }}
                                        </td>
                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>
                                            {{ data.unit_price }}
                                        </td>
                                        <td>
                                            {{ data.stock.selling_price }}
                                        </td>
                                        <td>
                                            {{ data.qty_ordered }}
                                        </td>
                                        <td>
                                            {{ data.qty_delivered }}
                                        </td>
                                        <td>{{cashFormatter(data.sub_total)}}</td>
                                        <td>
                                            <Button
                                                type="primary"
                                                @click="
                                                    checkInventory(
                                                        data.stock_id
                                                    )
                                                "
                                                >Available Qty</Button
                                            >
                                        </td>
                                        <td>{{ data.batch_no }}</td>
                                        <td>{{ data.expiry_date }}</td>
                                        <td>
                                            <router-link
                                                v-if="isDeletePermitted"
                                                to="#"
                                                @click.native="
                                                    deleteDetails(data, i)
                                                "
                                            >
                                                <p style="color: red">Delete</p>
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="check_inventory_modal">
            <check-inventory-modal
                v-if="check_inventory_modal"
                :inventory_data="inventory_data"
        /></Modal>
           <Modal v-model="print_label_modal" style="880px">
            <print-grn-label
                v-if="print_label_modal"
                :item_details="print_label_item"
        /></Modal>

    </div>
</template>

<script>
import { mdiFeatureSearch } from "@mdi/js";
import CheckInventoryModal from "../../pos_retail/CheckInventoryModal.vue";
import PrintGrnLabel from './PrintGrnLabel.vue';
export default {
    components: { CheckInventoryModal, PrintGrnLabel },
    props: ["delivery_details"],
    data() {
        return {
            inventory_data: null,
            check_inventory_modal: false,
            delivery_data: [],
            print_label_item: null,
            print_label_modal: false
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        printGrnLabel(data) {
            // Logic to print GRN label
            console.log("Printing GRN label for:", data);
            this.print_label_item = data;
            this.print_label_modal = true;
        },
        async checkInventory(id) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
        async deleteDetails(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const dataObj = {
                order_no: data.order_no,
                id: data.id,
                delivery_no: data.delivery_no,
                stock_id: data.stock_id,
                qty_delivered: data.qty_delivered,
                department_id: data.department_id,
            };
            const res = await this.callApi(
                "POST",
                "data/po_receivable/deleteDeliveryItem",
                dataObj
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Deleted");
                this.delivery_data.splice(i, 1);
            } else {
                this.servo();
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetch()]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetch() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryDetails",
                { params: { delivery_no: this.delivery_details.delivery_no } }
            );
            if (res.status === 200) {
                this.delivery_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
