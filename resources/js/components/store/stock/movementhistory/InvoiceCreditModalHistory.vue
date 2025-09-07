<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Invoice Credit Note History</div>

                    <div class="card-body">
                        <div class="row">
                            - <p>User {{ data[0].user.name }}</p> 
                      
                            <br />
                          
                            <br />
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Item Price</th>
                                            <th scope="col">Qty</th>
                                            <th scope="col">total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.invoice.stock.code }}
                                            </td>
                                            <td    :class="{
                                                    current_stock:
                                                        data.invoice.stock_id ==
                                                        stock_id,
                                                }">
                                                {{ data.invoice.stock.name }}
                                            </td>

                                            <td>
                                                {{ cashFormatter(data.price) }}
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.line_total
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
        this.fetchCreditNoteDetails();
    },
    methods: {
        async fetchCreditNoteDetails() {
            this.showLoader();
            const res = await this.getApi(
                "data/credit/fetchByCreditNo/" + this.info_data.ref,
                {}
            );
            this.hideLoader();

            if (res.status === 200) {
                this.data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
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
