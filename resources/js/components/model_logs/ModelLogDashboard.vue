<template>
    <div class="log-container">
        <div class="row">
            <div class="col-md-3">
                <label for="datePicker" class="form-label"
                    >Filter From Date:</label
                >
                <div class="input-group date" id="datepicker">
                    <input
                        type="datetime-local"
                        class="form-control"
                        id="datePicker"
                        v-model="params.from"
                        placeholder="Select a date"
                    />
                    <span class="input-group-text">
                        <i class="bi bi-calendar"></i>
                        <!-- Bootstrap Icon (optional) -->
                    </span>
                </div>
            </div>
            <div class="col-md-3">
                <label for="datePicker" class="form-label"
                    >Filter To Date:</label
                >
                <div class="input-group date" id="datepicker">
                    <input
                        type="datetime-local"
                        class="form-control"
                        id="datePicker"
                        v-model="params.to"
                        placeholder="Select a date"
                    />
                    <span class="input-group-text">
                        <i class="bi bi-calendar"></i>
                        <!-- Bootstrap Icon (optional) -->
                    </span>
                </div>
            </div>
            <div class="filter-group search-group">
                <label>Search</label>
                <input
                    type="text"
                    v-model="params.search"
                    placeholder="Search logs..."
                    class="compact-input"
                />
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Search As User</label>
                    <treeselect
                        :load-options="fetchWaiter"
                        :options="waiter_select_data"
                        :auto-load-root-options="false"
                        v-model="params.user_id"
                        :multiple="false"
                        :show-count="true"
                        placeholder="Select User"
                    />
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Branch</th>
                        <th>Data</th>
                        <th>View</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id">
                        <td>{{ log.model_type.split("\\").pop() }}</td>
                        <td>{{ log.action }}</td>
                        <td>{{ log.user.name }}</td>
                        <td>{{ log.branch.branch }}</td>
                        <td class="data-cell">
                            <pre
                                class="formatted-json"
                                v-html="highlightJson(log.data)"
                            ></pre>
                        </td>
                        <td>
                            <button
                                class="btn btn-sm btn-info"
                                @click="showLog(log)"
                            >
                                View
                            </button>
                        </td>
                        <td>{{ formatDate(log.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="showDialog" class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <h5>Log Details (JSON)</h5>
                    <pre style="max-height: 400px; overflow: auto">{{
                        selectedLogFormatted
                    }}</pre>
                    <button class="btn btn-danger" @click="showDialog = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
        <div class="footer">
            <pagination
                v-if="logs !== null"
                v-bind:results="logs"
                v-on:get-page="fetchLogs"
            ></pagination>
            <span class="item-count">Items Count: {{ logs.total }}</span>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Pagination from "../utilities/Pagination.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: { Pagination, Treeselect },
    data() {
        return {
            logs: [],
            url: "/model-logs",
            params: {
                from: null,
                to: null,
                search: "",
            },
            isLoading: true,
            showDialog: false,
            selectedLog: null,
            waiter_select_data: null,
        };
    },
    mounted() {
        this.fetchLogs(1);
    },
    watch: {
        params: {
            handler() {
                this.isLoading = false;
                this.fetchLogs(1);
            },
            deep: true,
        },
    },
    methods: {
        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },

        showLog(log) {
            this.selectedLog = log;
            this.showDialog = true;
        },
        async fetchLogs(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/model_logs/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.logs = res.data;
            } else {
                this.form_error(res);
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleString();
        },
        formatJson(data) {
            try {
                const parsedData =
                    typeof data === "string" ? JSON.parse(data) : data;
                return JSON.stringify(parsedData, null, 2);
            } catch (error) {
                return data;
            }
        },
        highlightJson(data) {
            try {
                const parsedData =
                    typeof data === "string" ? JSON.parse(data) : data;
                const formatted = JSON.stringify(parsedData, null, 2);
                // Basic syntax highlighting for keys and values
                return formatted
                    .replace(
                        /"([^"]+)":/g,
                        '<span class="json-key">"$1":</span>'
                    )
                    .replace(
                        /: ("[^"]*")/g,
                        ': <span class="json-string">$1</span>'
                    )
                    .replace(
                        /: ([0-9]+(\.[0-9]+)?)/g,
                        ': <span class="json-number">$1</span>'
                    )
                    .replace(
                        /: (true|false|null)/g,
                        ': <span class="json-boolean">$1</span>'
                    );
            } catch (error) {
                return data;
            }
        },
        showLoader() {
            console.log("Loading...");
        },
        hideLoader() {
            console.log("Loaded.");
        },
        form_error(res) {
            console.error("Error:", res);
        },
    },
    computed: {
        selectedLogFormatted() {
            try {
                return JSON.stringify(
                    JSON.parse(this.selectedLog?.data || "{}"),
                    null,
                    2
                );
            } catch (e) {
                return this.selectedLog?.data || "{}";
            }
        },
    },
};
</script>

<style scoped>
.log-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Filter Row */
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    display: block;
}

.compact-input {
    width: 100%;
    padding: 0.5rem;
    font-size: 0.9rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.2s;
}

.compact-input:focus {
    border-color: #007bff;
}

.search-group {
    flex: 2;
}

/* Table */
.table-wrapper {
    overflow-x: auto;
}

.compact-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.compact-table th,
.compact-table td {
    padding: 0.5rem;
    border: 1px solid #e0e0e0;
    text-align: left;
}

.compact-table th {
    background-color: #f1f3f5;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    font-size: 0.8rem;
}

.compact-table tbody tr:hover {
    background-color: #f8f9fa;
}

.data-cell {
    max-width: 150px;
    word-break: break-word;
}

.formatted-json {
    margin: 0;
    padding: 0.25rem;
    font-size: 0.8rem; /* Smaller font for compactness */
    line-height: 1.3; /* Tighter line spacing */
    white-space: pre-wrap;
    background-color: #f8f9fa;
    border-radius: 4px;
    max-height: 100px; /* Limit height to prevent overflow */
    overflow-y: auto; /* Add scroll for long JSON */
}

/* Syntax Highlighting */
.json-key {
    color: #d73a49; /* Red for keys */
}

.json-string {
    color: #032f62; /* Blue for strings */
}

.json-number {
    color: #005cc5; /* Blue for numbers */
}

.json-boolean {
    color: #d73a49; /* Red for booleans */
}

/* Footer */
.footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.item-count {
    font-weight: 500;
}

.modal-mask {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-wrapper {
    width: 90%;
    max-width: 600px;
}
.modal-container {
    background: white;
    padding: 20px;
    border-radius: 6px;
    max-height: 80vh;
    overflow-y: auto;
}
</style>
