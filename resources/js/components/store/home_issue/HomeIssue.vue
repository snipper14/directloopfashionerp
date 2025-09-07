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
                        Home Issue Report</v-btn
                    >
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b> ISSUE STOCK TO HOME </b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-7 form-group">
                                        <label for="">Search Item</label>
                                        <input
                                            type="text"
                                            v-model="search_query.product_name"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-5 form-group">
                                        <label for="">Date Issue</label>
                                        <date-picker
                                            v-model="form_data.date_issued"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
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
                                                    Issue
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                qty: 1,
                purchase_price: 0,
                row_total: 0,
                date_issued: null,
            },
        };
    },
    mounted() {
        this.form_data.date_issued = this.getCurrentDate();
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
        viewReport() {
            this.$router.push("home_store_issue_report");
        },

        async issueStock(value) {
            this.form_data.qty = value.qty_issued;

            this.form_data.stock_id = value.id;
            this.form_data.unit_id = value.unit_id;

            this.form_data.purchase_price = value.purchase_price;

            this.form_data.row_total =
                this.form_data.purchase_price * this.form_data.qty;

            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/issue_home/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
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
    },
};
</script>
