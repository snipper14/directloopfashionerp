<template>
    <div >
        <v-app>
                <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Receive Stock</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="">Receive Location*</label>
                                <Select
                                    v-model="form_data.received_department_id"
                                >
                                    <Option
                                        v-for="(value, i) in dept_data"
                                        :key="i"
                                        :value="value.id"
                                    >
                                        {{ value.department }}
                                    </Option>
                                </Select>
                            </div>

                            <div class="col-md-3 form-group d-flex flex-column">
                                <label> Receive Date *</label
                                ><date-picker
                                    v-model="form_data.received_date"
                                    valueType="format"
                                ></date-picker>
                            </div>

                            <div class="col-md-4">
                                <v-btn
                                    v-if="transfer_data.length > 0"
                                    @click="receiveAll()"
                                    color="primary"
                                    >Receive All</v-btn
                                >
                            </div>
                            <div class="col-md-3">
                                <input
                                    type="text"
                                    placeholder="Search.."
                                    class="form-control small-input"
                                    v-model="params.search"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-primary custom-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th scope="col">Transfer Date</th>
                                        <th>Item</th>
                                        <th>Item Code</th>
                                        <th scope="col">Transfer Code</th>
                                        <th>Reference Code</th>
                                        <th>Origin Branch</th>
                                        <th>Dest. Branch</th>
                                        <th scope="col">Issuer</th>
                                        <th>Qty Issued</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(data, i) in transfer_data"
                                        :key="i"
                                    >
                                        <td>{{ data.id }}</td>

                                        <td>
                                            {{ data.transfer_date }}
                                        </td>
                                        <td>{{ data.stock.product_name }}</td>
                                        <td>{{ data.stock.code }}</td>
                                        <td>{{ data.transfer_code }}</td>
                                        <td>{{ data.reference_code }}</td>
                                        <td>{{ data.branch.branch }}</td>
                                        <td>{{ data.branch_to.branch }}</td>
                                        <td>{{ data.user.name }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                style="width: 100px"
                                                class="form-control"
                                                v-model="data.qty"
                                            />
                                        </td>
                                        <td>
                                            <v-btn
                                                @click="
                                                    receiveTransfer(data, i)
                                                "
                                                color="info"
                                                x-small
                                                >Receive</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </v-app>
    
    </div>
</template>

<script>
export default {
    props: ["details_data"],
    data() {
        return {
            dept_data: null,
            transfer_data: [],
            params: {
                search: "",
                transfer_code: this.details_data.transfer_code,
            },
            form_data: {
                received_department_id: null,

                received_date: null,
            },
        };
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    mounted() {
        this.form_data.received_date = this.getCurrentDate();
        this.concurrentApiReq(1);
    },
    methods: {
           async receiveTransfer(value, i) {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/branch_stock_transfer/receiveTransfer",
                {
                    ...value,
                    ...this.form_data,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Received");
                this.transfer_data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
      
        async concurrentApiReq(page) {
            this.showLoader();
            await Promise.all([
                this.fetchDept(),
                this.fetchUnreceivedTransfer(),
            ]);
            this.hideLoader();
        },
        async fetchUnreceivedTransfer() {
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchUnreceivedTransfer",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                this.transfer_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        stock_id: obj.stock_id,
                        transfer_date: obj.transfer_date,
                        stock: obj.stock,
                        transfer_code: obj.transfer_code,
                        reference_code: obj.reference_code,
                        qty: obj.qty,
                        user: obj.user,
                        branch_to: obj.branch_to,
                        branch: obj.branch,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async receiveAll() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/branch_stock_transfer/receiveAll",
                { ...this.form_data, transfer_data: this.transfer_data }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Saved");
                this.$emit('dismiss-modal')
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
