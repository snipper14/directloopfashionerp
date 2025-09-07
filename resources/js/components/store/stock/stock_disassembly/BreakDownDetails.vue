<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><b>Disassembly Report for {{details_data.stock.product_name}}</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <center></center>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Cost</th>
                                        <th>Qty</th>
                                        <th>Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            value, i
                                        ) in production_data_details"
                                        :key="i"
                                    >
                                        <th>
                                            {{ value.child_stock.product_name }}
                                        </th>
                                        <td scope="row">
                                            {{
                                                cashFormatter(value.child_cost)
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(value.child_qty) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.child_totalcost
                                                )
                                            }}
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
</template>

<script>
export default {
    props: ["details_data"],
    data() {
        return {
            production_data_details: [],
        };
    },
    mounted() {
        console.log(JSON.stringify(this.details_data));
        console.log("Component mounted.");
        this.fetchDetails();
    },
    methods: {
        async fetchDetails() {
            this.showLoader();
            const res = await this.getApi(
                "data/stock_disassembly/fetchDetails",
                {
                    params: {
                        production_no: this.details_data.production_no,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.production_data_details = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
