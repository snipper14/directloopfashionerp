<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row justify-content-left">
                    <v-btn
                        class="ma-2 v-btn-primary"
                        @click="$emit('dashboard-active')"
                        color="primary"
                        dark
                    >
                        <v-icon dark left> mdi-arrow-left </v-icon>
                        Back
                    </v-btn>
                </div>
                <div class="row">
                    <div class="card">
                        <div
                            class="card-header d-flex flex-row justify-content-between"
                        >
                            <h3>
                                <b>{{ details_data.user.name }}</b>
                            </h3>
                            <b
                                >From:
                                {{
                                    details_data.opening_time
                                        ? details_data.opening_time
                                        : "beginning"
                                }}
                                <br />
                                To: {{ details_data.closing_time }}</b
                            >
                            <h3>
                                <b
                                    >TOTAL BALANCE:
                                    {{ cashFormatter(balance_total) }}</b
                                >
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Guest</th>
                                            <th>Phone</th>
                                            <th scope="col">Room</th>
                                            <th scope="col">Package</th>
                                            <th>Price</th>
                                            <th>Tax</th>
                                            <th scope="col">Total</th>

                                            <th scope="col">Amt Paid</th>
                                            <th scope="col">Amt Pending</th>
                                            <th>Credit N.</th>
                                            <th>From</th>
                                            <th>to</th>
                                            <th>Checkout Date</th>
                                            <th>From</th>
                                            <th>Receptionist</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in resevation_data"
                                            :key="i"
                                            :class="{
                                                'pending-payment':
                                                    data.total -
                                                        data.amount_paid >
                                                    0,
                                            }"
                                        >
                                            <td>
                                                {{ data.name }}
                                            </td>
                                            <td>{{ data.phone }}</td>

                                            <td>
                                                {{ data.room.door_name }}
                                            </td>
                                            <td>
                                                {{ data.room_package.name }}
                                            </td>
                                            <td>
                                                {{ cashFormatter(data.price) }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.tax_amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ cashFormatter(data.total) }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.amount_paid
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.total -
                                                            data.amount_paid
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_note_total
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ data.from }}
                                            </td>
                                            <td>
                                                {{ data.to }}
                                            </td>
                                            <td>
                                                {{ data.checkout_date }}
                                            </td>
                                            <td>
                                                {{ data.guest_company.name }}
                                            </td>
                                            <td>
                                                {{ data.user.name }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                            <button
                                                class="btn btn-primary btn-sm"
                                            >
                                                Export Excel
                                            </button>
                                        </export-excel>

                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchExcel"
                                            worksheet="My Worksheet"
                                            type="csv"
                                            name="filename.xls"
                                        >
                                            <button
                                                class="btn btn-success btn-sm"
                                            >
                                                Export CSV
                                            </button>
                                        </export-excel>
                                    </div>
                                </div>
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
    props: ["details_data"],

    data() {
        return {
            resevation_data: [],
            order_data_details: null,
            modal: {
                details: false,
            },
            total_order: 0,
            total_tax: 0,
            balance_total: 0,
        };
    },
    mounted() {
        this.fetchBalancesDetails();
    },
    methods: {
        async fetchExcel() {
            var data = this.resevation_data;
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    data[i].user = data[i].user ? data[i].user.name : "";
                    data[i].guest_company = data[i].guest_company
                        ? data[i].guest_company.name
                        : "";

                    data[i].created_at = this.formatDateTime(
                        data[i].created_at
                    );
                }

                return data;
            } else {
            }
        },
        viewDetails(data) {
            this.total_order = data.receipt_total;
            this.total_tax = data.total_vat;
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fetchBalancesDetails() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/reception_balance/fetchBalancesDetails",

                this.details_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.resevation_data = res.data;
                this.balance_total = this.resevation_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.total;
                },
                0);
            } else {
                this.servo();
            }
        },
    },
};
</script>
