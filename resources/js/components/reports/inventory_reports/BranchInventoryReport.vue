<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><branch-inventory-nav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Branch Inventory Totals</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-bordered table-sm stylish-table custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Description</th>
                                        <th scope="col">
                                            Total Qty
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_qty
                                                "
                                                sort-key="sort_total_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            Value on Cost
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_value_purchase_price
                                                "
                                                sort-key="sort_total_value_purchase_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            Value on Selling Price
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_value_selling_price
                                                "
                                                sort-key="sort_total_value_selling_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>% margin  <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_profit_markup
                                                "
                                                sort-key="sort_profit_markup"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(data, i) in inventory_totals"
                                        :key="i"
                                    >
                                        <td class="table-label">
                                            {{ data.branch_name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_qty) }}
                                        </td>
                                        <td class="table-value">
                                            {{
                                                cashFormatter(
                                                    data.total_value_purchase_price
                                                )
                                            }}
                                        </td>
                                        <td class="table-value">
                                            {{
                                                cashFormatter(
                                                    data.total_value_selling_price
                                                )
                                            }}
                                        </td>
                                        <td>{{ data.profit_markup }}</td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Grand&nbsp;Total</td>
                                        <td class="text-right">
                                            {{ cashFormatter(grandTotals.qty) }}
                                        </td>
                                        <td class="text-right">
                                            {{ cashFormatter(grandTotals.pp) }}
                                        </td>
                                        <td class="text-right">
                                            {{ cashFormatter(grandTotals.sp) }}
                                        </td>

                                        <td class="text-right">
                                            {{ avgMarkup }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
    </div>
</template>

<script>
import SortButtons from "../../utilities/SortButtons.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchInventoryNav from "./BranchInventoryNav.vue";
export default {
    components: { BranchInventoryNav, Unauthorized, SortButtons },
    data() {
        return {
            show_loader: true,
            branch_data: [],
            department_data: [],
            inventory_totals: [],
            search_params: {
                branch_id: null,
                department_id: null,
            },
            sort_params: {
                sort_total_qty: "",
                sort_total_value_selling_price: "",
                sort_total_value_purchase_price: "",
                sort_profit_markup:""
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async concurrentApiReq() {
            this.show_loader ? this.showLoader() : "";
            await Promise.all([
                this.fetchInventory(),

                this.fetchBranch(),
                this.fetchDepartment(),
            ]);
            this.show_loader ? this.hideLoader() : false;
        },
        async fetchInventory() {
            const res = await this.getApi("data/inventory/inventoryTotals", {
                params: {
                    ...this.search_params,
                    ...this.sort_params,
                },
            });
            if (res.status == 200) {
                this.inventory_totals = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchBranch() {
            const res = await this.getApi("data/branch/fetch", "");

            this.branch_data = res.data;
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
    },

    computed: {
        /** Grand totals for qty and values */
        grandTotals() {
            return this.inventory_totals.reduce(
                (tot, row) => {
                    tot.qty += +row.total_qty || 0;
                    tot.sp += +row.total_value_selling_price || 0;
                    tot.pp += +row.total_value_purchase_price || 0;
                    return tot;
                },
                { qty: 0, sp: 0, pp: 0 }
            );
        },

        /** Average profit‑markup % across the rows */
        avgMarkup() {
            if (!this.inventory_totals.length) return 0;
            const total = this.inventory_totals.reduce(
                (sum, r) => sum + (+r.profit_markup || 0),
                0
            );
            return +(total / this.inventory_totals.length).toFixed(2);
        },
    },
};
</script>
