<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Merged History</div>

                    <div class="card-body">
                          <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Created</th>
                                            <th>Code</th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Deleted Item</th>
                                            <th>Qty Merged</th>
                                           
                                            <th>User</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data"
                                            :key="i"
                                        >
                                        <td>{{formatMonthDateTime(data.created_at)}}</td>
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td
                                               
                                            >
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>
                                                {{
                                                    data.deleted_stock.name
                                                       
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.qty_merged
                                                    )
                                                }}
                                            </td>
                                           
                                            <td>
                                                {{ data.user.name }}
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
    props: ["info_data", "details_data"],
    data() {
        return {
            data: [],
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchMergedHist()
    },
    methods: {
        async fetchMergedHist() {
            const res = await this.getApi("data/merge_stock/fetch", {
                params: {
                    stock_id: this.details_data.id,
                },
            });
            if (res.status === 200) {
                this.data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
