<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Pending PO</b></h3>
                    </div>

                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Stock Name</th>
                                    <th scope="col">Supplier</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in pending_po_array"
                                    :key="i"
                                >
                                    <th scope="row">
                                        {{ data.stock.product_name }}
                                    </th>
                                    <td>{{ data.supplier.company_name }}</td>
                                    <td>
                                        {{ cashFormatter(data.unit_price) }}
                                    </td>
                                    <td>{{ cashFormatter(data.qty) }}</td>
                                    <td>
                                        {{ cashFormatter(data.sub_total) }}
                                    </td>
                                    <td><v-btn x-small color="danger" @click="deleteRecords(data,i)">Delete</v-btn></td>
                                </tr>
                            </tbody>
                        </table>
                        <v-btn color="primary" v-if="pending_po_array.length>0" x-large @click="completePO">COMPLETE PO</v-btn>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["pending_po_array"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods:{
        async completePO(){
             this.showLoader();
                const res = await this.callApi('post',"data/temp_po/completeOrder", {
                    
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.successNotif("completed  ");
                    this.$router.push("/po_management");
                } else{
                    this.form_error(res)
                }
        },
         async deleteRecords(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi('delete',"data/temp_po/destroy/"+data.id, {
                    
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.pending_po_array.splice(i, 1);
                }
            }
        },
    }
};
</script>
