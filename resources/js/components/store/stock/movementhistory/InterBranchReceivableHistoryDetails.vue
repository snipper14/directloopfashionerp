<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">InterBranch Transfer Receivable Details</div>

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
                                             <th>Branch From</th>
                                              
                                            <th>Qty </th>
<th>Department</th>
                                           
                                            <th>User</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            class="small-tr"
                                              v-for="(data, i) in data.filter(row => row.stock_id === stock_id)"
                                            :key="i"
                                        >
                                        <td>{{(data.received_date)}}</td>
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td
                                            :class="{
                                                current_stock:
                                                    data.stock_id == stock_id,
                                            }"
                                        >
                                            {{ data.stock.name }}
                                        </td>
                                              <td>{{data.branch.branch}}</td>
                                     
                                       
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.qty
                                                    )
                                                }}
                                            </td>
                                           <td>{{data.department.department}}</td>
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
            stock_id: this.details_data.id,
        };
    },
    mounted() {
        console.log(JSON.stringify(this.info_data)
        );

        this.fetchTransferDetails()
    },
    methods: {
        async fetchTransferDetails() {
            this.showLoader()
            const res = await this.getApi("data/branch_stock_transfer/fetchReceivable", {
                params: {
                    stock_id: this.info_data.stock_id,
                    transfer_code:this.info_data.ref
                },
            });
            this.hideLoader()
            if (res.status === 200) {
                this.data = res.data;
            } else {
                this.form_error(res);
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
