<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <POSideNav />
            </div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Low Stock</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 d-flex flex-column">
                                <div class="form-group d-flex flex-column mr-2">
                                    <label for=""> Location*</label>

                                    <Select v-model="params.department_id">
                                        <Option value="">All</Option>
                                        <Option
                                            v-for="item in department_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                    color="primary"
                                    @click="concurrentApiReq()"
                                    x-small
                                    >Fetch Low Stock</v-btn
                                >
                            </div>
                             <div class="col-md-4">
                                <v-btn
                                    color="primary"
                                    @click="inventory_data=[]"
                                    x-small
                                    >Clear Table</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Supplier</th>

                                            <th scope="col">Purchase P</th>
                                            <th scope="col">Selling P</th>
                                            <th scope="col">reorder qty</th>
                                            <th scope="col">qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in inventory_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td>
                                                {{ data.stock?data.stock.name:"" }}
                                            </td>
                                            <td>
                                                {{ data.stock?data.stock.unit?data.stock.unit.name:"":"" }}
                                            </td>
                                            <td>
                                                {{
                                                    data.stock.supplier
                                                        .company_name
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.stock
                                                            .purchase_price
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.stock.selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <p>
                                                    {{ data.stock.reorder_qty }}
                                                </p>
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>
                                                <v-btn
                                                    color="primary"
                                                    @click="createPO(data)"
                                                    x-small
                                                    >Create PO</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <PendingPOComponent :pending_po_array="pending_po_array"/>
                        </div>
                 
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="add_po_modal">
            <AddPOModal
                v-if="add_po_modal"
                :details_data="details_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
    </div>
</template>

<script>
import AddPOModal from "./AddPOModal.vue";
import PendingPOComponent from './PendingPOComponent.vue';
import POSideNav from "./POSideNav.vue";
export default {
    components: { POSideNav, AddPOModal, PendingPOComponent },
    data() {
        return {
            add_po_modal: false,
            details_data: null,
            inventory_data: [],
            department_data: null,
            pending_po_array: [],
            params: {
                department_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq2();
    },
    methods: {
        async concurrentApiReq2() {
            this.showLoader();
            await Promise.all([
                this.fetchDepartment(),
                this.fetchPendingPO(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchPendingPO() {
            const res = await this.getApi("data/temp_po/fetch", "");

            this.pending_po_array = res.data;
        },
        dismissDialog() {
            this.add_po_modal = false;
               this.fetchPendingPO()
        },
        createPO(data) {
            this.details_data = data;
            this.add_po_modal = true;
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.getLowStock(1)]);
            this.hideLoader();
        },

        async getLowStock(page) {
            const res = await this.getApi("data/inventory/getLowStock", {
                params: {
                    page,
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.inventory_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
