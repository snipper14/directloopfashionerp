<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Dispatch/Issue History</div>

                    <div class="card-body">
                        <div class="row">
                            <p>User {{ data[0].user.name }}</p>
                           
                            <br />

                            <br />
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Issue No</th>
                                            <th>Date</th>

                                            <th>Code</th>
                                            <th>ItemName</th>

                                            <th>Qty Item Issued</th>
                                            <th>Final Qty Item Issued</th>

                                            <th>Item c.P</th>

                                            <th>User</th>
                                            <th>Issed to</th>
                                            <th>Origin</th>
                                            <th>Dispatch To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.issue_no }}
                                            </td>
                                            <td>
                                                {{ data.date_issued }}
                                            </td>

                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td   :class="{
                                                    current_stock:
                                                        data.stock_id ==
                                                        stock_id,
                                                }">
                                                {{ data.stock.product_name }}
                                            </td>

                                            <td>
                                                {{ data.qty_issued }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.final_qty
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ data.purchase_price }}
                                            </td>
                                            <td>
                                                {{ data.user.name }}
                                            </td>
                                            <td>
                                                {{ data.issued_staff.name }}
                                            </td>
                                            <td>
                                                {{
                                                    data.source_department
                                                        .department
                                                }}
                                            </td>
                                            <td>
                                                {{ data.department.department }}
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
    props: ["info_data"],
    data() {
        return {
            data: [],
            stock_id: null,
        };
    },
    mounted() {
        console.log("Component mounted." + JSON.stringify(this.info_data));
        this.stock_id = this.info_data.stock_id;
        this.getSalesData();
    },
    methods: {
        async getSalesData() {
            const res = await this.getApi(
                "data/internal_issue/fetchDispatchDetails",
                {
                    params: {
                        issue_no: this.info_data.ref,
                    },
                }
            );
            if (res.status === 200) {
                this.data = res.data;
            }
        },
    },
};
</script>
<style scoped>
.current_stock {
    color: rgb(255, 145, 0);
}
</style>
