<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Physical Increment History</div>

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
                                            <th>Qty </th>
                                           
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
                                                    cashFormatter(
                                                        data.qty
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
        console.log("Component mounted."+JSON.stringify(this.details_data));
        this.fetchMergedHist()
    },
    methods: {
        async fetchMergedHist() {
            const res = await this.getApi("data/stock_update/fetchIncrementByTimesmtamp", {
                params: {
                    ref: this.info_data.ref
                 
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
