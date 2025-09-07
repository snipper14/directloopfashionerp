<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left>
                        mdi-arrow-left
                    </v-icon>
                    Back
                </v-btn>
                <h4></h4>
                <div class="card">
                    <div class="card-header">
                        <b>Supplier: </b>{{ data.company_name }}
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                  
                                    <th scope="col">Paid Amount</th>
                                    <th scope="col">Outstanding Amount</th>
                                    <th scope="col">Ref No</th>
                                    <th scope="col">Date Paid</th>
                                    <th scope="col">payment_details</th>
                                </tr>
                            </thead>
                            <tbody >
                               
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in payment_data"
                                    :key="i"
                                >
                               
                                    <td>
                                        {{ cashFormatter(data.amount_paid) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.outstanding_balance
                                            )
                                        }}
                                    </td>
                                    <td>{{ data.ref_no }}</td>
                                    <td>{{ data.date_paid }}</td>
                                    <td>{{ data.payment_details }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <notifications group="foo" />
    </div>
</template>

<script>

export default {
    props: ["data"],
    data() {
        return {
            
            payment_data: []
        };
    },

    mounted() {
       
        this.getStatements();
    },

    methods: {
        async getStatements() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_payment/fetch_details/" + this.data.supplier_id
            );
            this.hideLoader();
          
            if (res.status == 200) {
                this.payment_data = res.data.results;
            } else {
                this.swr("Server error");
            }
        }
    }
};
</script>
