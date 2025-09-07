<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="row">
                <div class="col-3 ml-3">
                    <v-btn
                        :loading="btn_loading"
                        small
                        color="info"
                        dark
                        @click="viewReport()"
                    >
                        Issue Report</v-btn
                    >
                </div>
                <div class="col-3 ml-3">
                    <v-btn
                        :loading="btn_loading"
                        small
                        color="warning"
                        dark
                        @click="goToHome()"
                    >
                        Home Issue
                    </v-btn>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b> ISSUE STOCK TO MAIN STORE</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Search Item</label>
                                    <input
                                        type="text"
                                        v-model="search_query.product_name"
                                        class="form-control"
                                    />
                                </div>
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Avail Qty</th>
                                            <th scope="col">Unit</th>
                                            <th>Issued Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(value, i) in search_results"
                                            :key="i"
                                        >
                                            <td>{{ value.product_name }}</td>

                                            <td>{{ value.main_store_qty }}</td>
                                            <td>{{ value.unit }}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.qty_issued"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-primary btn-sm"
                                                    @click="issueStock(value)"
                                                >
                                                    Add
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <fieldset class="border">
                                    <legend class="text-center">Issue</legend>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex">
                                                <div class="form-group mr-2">
                                                    <label>Issue No: </label>
                                                    <input
                                                        type="text"
                                                        disabled
                                                        v-model="
                                                            form_data.issue_no
                                                        "
                                                        class="form-control"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">Store Qty</th>

                                            <th>Issue Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(value, i) in issue_data"
                                            :key="i"
                                        >
                                            <td>{{ value.product_name }}</td>

                                            <td>{{ value.opening_stock }}</td>

                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.qty_issued"
                                                    class="form-control"
                                                />
                                            </td>

                                            <td>
                                                <button
                                                    class="btn btn-danger btn-sm"
                                                    @click="
                                                        deleteData(value.id, i)
                                                    "
                                                >
                                                    remove
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <v-btn
                                    :loading="btn_loading"
                                    small
                                    color="primary"
                                    dark
                                    @click="completeIssueMainStore()"
                                >
                                    Complete Issue</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            search_results: [],
            search_query: {
                product_name: "",
                code: "",
            },
            issue_data: [],
            btn_loading: false,
            form_data: {
                stock_id: null,
                unit_id: null,
                qty_issued: 1,
                mapping_value: 1,
                purchase_price: 0,
                issue_no: "",
                department_id: null,
                unit: "",
                row_total: 0,
                final_qty: 0,
                opening_stock: 0,
                product_department_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        goToHome() {
            this.$router.push("home_issue");
        },
        viewReport() {
            this.$router.push("main_store_issue_report");
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/main_store_issue/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.issue_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async completeIssueMainStore() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/main_store_issue/completeIssue",
                {
                    issue_data: this.issue_data,
                    issue_no: this.form_data.issue_no,
                    department_id: this.form_data.department_id,
                }
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("Compeleted");
                this.$router.push("main_store_issue_report");
            } else {
                this.form_error(res);
            }
        },
        async downloadIssueNote() {
            this.btn_loading = true;
            axios({
                url: "data/main_store_issue/completeIssue",

                data: {
                    issue_data: this.issue_data,
                    issue_no: this.form_data.issue_no,
                    department_id: this.form_data.department_id,
                },
                method: "POST",
                responseType: "blob", // important
            })
                .then((response) => {
                    this.btn_loading = false;
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute(
                        "download",
                        this.form_data.issue_no + ".pdf"
                    );
                    document.body.appendChild(link);
                    link.click();
                    this.$router.push("main_store_issue_report");
                })
                .catch((error) => {
                    this.btn_loading = false;

                    if (error.response.status == 422) {
                        this.errorNotif("Some items don't have enough qty");
                        return;
                    } else {
                        this.servo();
                    }
                });
        },

        async completeIssue() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/main_store_issue/completeIssue",
                {
                    issue_data: this.issue_data,
                    issue_no: this.form_data.issue_no,
                    department_id: this.form_data.department_id,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully issued");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = 0)
                );
                this.form_data.qty_issued = 1;
                this.form_data.department_id = null;
                this.issue_data = [];
                this.form_data.issue_no = res.data;
                this.search_results = [];
            } else {
                this.form_error(res);
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchIssueNo()]);
            this.hideLoader();
        },

        async fetchIssueNo() {
            const res = await this.getApi(
                "data/main_store_issue/fetchIssueNo",
                ""
            );

            if (res.status == 200) {
                if (res.data.issue_data.length > 0) {
                    this.form_data.issue_no = res.data.issue_data[0].issue_no;
                    this.form_data.department_id =
                        res.data.issue_data[0].department_id;
                    this.issue_data = this.modifyIssueData(res.data.issue_data);
                } else {
                    this.form_data.issue_no = res.data.issue_no;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async issueStock(value) {
            this.form_data.qty_issued = value.qty_issued;

            this.form_data.stock_id = value.id;
            this.form_data.unit_id = value.unit_id;

            this.form_data.purchase_price = value.purchase_price;
            this.form_data.unit = value.unit;
            this.form_data.row_total =
                this.form_data.purchase_price * this.form_data.qty_issued;
            this.form_data.final_qty = parseFloat(
                this.form_data.qty_issued
            ).toFixed(2);
            this.form_data.opening_stock = value.main_store_qty;

            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/main_store_issue/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.issue_data = this.modifyIssueData(res.data);
            } else {
                this.form_error(res);
            }
        },

        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },

        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    unit_id: obj.unit_id,
                    qty: 1,
                    department_id: obj.department_id,
                    code: obj.code,
                    mapping_value: obj.mapping_value,
                    product_name: obj.product_name,
                    purchase_price: obj.purchase_price,
                    unit: obj.unit.name,
                    tax_rate: obj.tax_dept.tax_rate,
                    main_store_qty: obj.main_store_qty,
                    qty_issued: 1,
                };
            });
            return modif;
        },
        modifyIssueData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    unit_id: obj.unit_id,
                    qty_issued: obj.qty_issued,
                    stock_id: obj.stock_id,
                    branch_id: obj.branch_id,
                    user_id: obj.user_id,
                    date_issued: obj.date_issued,
                    code: obj.code,

                    issue_no: obj.issue_no,
                    product_name: obj.stock.product_name,
                    purchase_price: obj.purchase_price,
                    unit: obj.unit.name,
                    row_total: obj.row_total,

                    opening_stock: obj.opening_stock,
                };
            });
            return modif;
        },
    },
};
</script>
