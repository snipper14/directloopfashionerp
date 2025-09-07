<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Waste Details</div>

                    <div class="card-body">
                          <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Wasted On</th>
                                            <th>Code</th>
                                            <th scope="col">Item</th>
                                               <th>Waste Reason</th>
                                            <th>Qty </th>
                                        
                                            <th>User</th>
                                            <th>Staff</th>
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
                                         <td>{{data.waste_reason.reasons}}</td>
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
                                            <td>{{data.staff.name}}</td>
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
        console.log(JSON.stringify(this.info_data)
        );
        
        this.fetchWasteItem()
    },
    methods: {
        async fetchWasteItem() {
            this.showLoader()
            const res = await this.getApi("data/waste_record/fetchWasteItem", {
                params: {
                    stock_id: this.info_data.stock_id,
                    ref_no:this.info_data.ref
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
