<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div
                        class="card-header d-flex align-items-center justify-content-between"
                    >
                        <span><b>Inventory Logs</b></span>
                        <button
                            class="btn btn-outline-secondary btn-sm mr-2"
                            @click="fetchLogs(1)"
                            title="Refresh"
                        >
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <div class="form-group" style="max-width: 360px">
                            <label for=""> Location*</label>
                            <treeselect
                                width="300"
                                :load-options="fetchDepartment"
                                :options="department_data"
                                :auto-load-root-options="false"
                                v-model="department_id"
                                :multiple="true"
                                :show-count="true"
                                placeholder="Search Location "
                            />
                        </div>
                        <div class="input-group" style="max-width: 360px">
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Search by action or stock name..."
                            />
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="from"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="fetchLogs(1)"
                                    >Filter Date $ Time</v-btn
                                >
                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Datetime Filter</v-btn
                                >
                            </div>
                        </div>
                        <!-- Loading -->

                        <!-- Table -->
                        <div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped mb-0"
                                >
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="white-space: nowrap">
                                                Date
                                            </th>
                                            <th>ID</th>
                                            <th>Item Code</th>
                                            <th>Stock</th>
                                            <th>Change</th>
                                            <th>New Qty</th>
                                            <th>Action type</th>
                                            <th class="d-none d-md-table-cell">
                                                Dept
                                            </th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="log in logs.data"
                                            :key="log.id"
                                        >
                                            <td style="white-space: nowrap">
                                                {{
                                                    formatMonthDateTime(
                                                        log.created_at_nairobi
                                                    )
                                                }}
                                            </td>
                                            <td>{{ log.stock.id }}</td>
                                            <td>{{ log.stock.code }}</td>
                                            <td>
                                                <view-stock-inventory-modal
                                                    :product_name="
                                                        log.stock.product_name
                                                    "
                                                    :stock_id="log.stock.id"
                                                />
                                            </td>
                                            <td>
                                                <span
                                                    class="badge"
                                                    :class="
                                                        log.change_qty >= 0
                                                            ? 'badge-success'
                                                            : 'badge-danger'
                                                    "
                                                    >{{
                                                        cashFormatter(
                                                            log.change_qty
                                                        )
                                                    }}</span
                                                >
                                            </td>
                                            <td>
                                                {{ cashFormatter(log.new_qty) }}
                                            </td>
                                            <td class="text-right">
                                                {{ log.action_type }}
                                            </td>

                                            <td class="d-none d-md-table-cell">
                                                {{ log.department.department }}
                                            </td>
                                            <td>{{ log.user.name }}</td>
                                        </tr>

                                        <tr v-if="!logs.data.length">
                                            <td
                                                colspan="8"
                                                class="text-center p-4 text-muted"
                                            >
                                                No logs found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <pagination
                                v-if="logs !== null"
                                v-bind:results="logs"
                                v-on:get-page="fetchLogs"
                            ></pagination>
                            Items Count {{ logs.total }}
                            <!-- Pagination -->
                        </div>

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" x-small>
                                        Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-btn color="success" x-small>
                                        Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";
import ViewStockInventoryModal from "../utilities/ViewStockInventoryModal.vue";
/**
 * Assumptions:
 * - Axios is globally available as window.axios (Laravel Mix default) or imported separately.
 * - API endpoint is /api/inventory/logs (adjust `API_URL` if different).
 * - Backend returns a Laravel paginator JSON { data: [], current_page, last_page, from, to, total, ... }.
 */
export default {
    components: { ViewStockInventoryModal, Pagination },
    data() {
        return {
            // point this to your fetchInventoryLogs route
            logs: [],
            loading: true,
            from: null,
            to: null,
            search: "",
            department_id: null,
        };
    },
    mounted() {
        this.fetchLogs(1);
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.loading = false;
                this.fetchLogs(1);
            }, 500),
        },
        department_id: {
            handler: _.debounce(function (val, old) {
                this.loading = false;
                this.fetchLogs(1);
            }, 500),
        },
    },
    methods: {
          async fetchExcel() {
            this.loading ? this.showLoader() : this.hideLoader();
            const res = await this.getApi("data/inventory/fetchInventoryLogs", {
                params: {
                   
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                    search: this.search || undefined,
                    department_id: this.department_id || undefined,
                },
            });
            this.hideLoader();
            var data_array = [];
            res.data.map((array_item) => {
                data_array.push({
                    date: this.formatMonthDateTime(array_item.created_at),
                    id: array_item.stock.id,
                    item_code: array_item.stock.code,
                    stock: array_item.stock.product_name,
                    change: array_item.change_qty,
                    new_qty: array_item.new_qty,
                    action_type: array_item.action_type,
                    dept: array_item.department.department,
                    user: array_item.user.name,
                });
            });
            return data_array;
        },
        fetchAllData() {
            this.from = null;
            this.to = null;
            this.search = "";
            this.department_id = null;
            this.fetchLogs(1);
        },
        async fetchLogs(page) {
            this.loading ? this.showLoader() : this.hideLoader();
            const res = await this.getApi("data/inventory/fetchInventoryLogs", {
                params: {
                    page: page,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                    search: this.search || undefined,
                    department_id: this.department_id || undefined,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.logs = res.data;
            } else {
                this.form_error(res);
            }
        },

        // Debounced search input
    },
    computed: {
        // Compact pagination with ellipses
    },
};
</script>
