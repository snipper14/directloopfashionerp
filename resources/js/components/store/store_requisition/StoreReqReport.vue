<template>
    <div class="row">
        <div class="col-md-2">
            <StoreReqNav />
        </div>
        <div class="col-md-10" v-if="isReadPermitted">
            <div class="row">
                <div class="col-md-10 border border-secondary">
                    <div>
                        <center>
                            <b><h3>Requisition List</h3></b>
                        </center>
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>

                                    <th>Date Due</th>
                                    <th scope="col">NO.</th>
                                    <th scope="col">Item Name</th>
                                   
                                    <th>Issued Qty</th>
                                    <th scope="col">Req. Qty </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in req_data.data"
                                    :key="i"
                                >
                                <td>
                                    {{data.date_due}}
                                </td>
                                    <td>
                                        {{ data.req_id }}
                                    </td>
                                    <td>
                                        {{
                                            data.stock
                                                ? data.stock.product_name
                                                : ""
                                        }}
                                    </td>
                                   
                                    <td>
                                        {{data.qty_issued}}
                                    </td>
                                    <td>
                                       {{data.qty}}
                                       
                                    </td>

                                  
                                </tr>
                            </tbody>
                        </table>
                           <pagination
                                        v-if="req_data !== null"
                                        v-bind:results="req_data"
                                        v-on:get-page="fetchCompletedRequisition"
                                    ></pagination>
                    </div>
                </div>
            </div>

            <!-- </form> -->
        </div>
        <div v-else><unauthorized /></div>
    </div>
</template>
<script>
import Pagination from '../../utilities/Pagination.vue';
import Unauthorized from "../../utilities/Unauthorized.vue";
import StoreReqNav from "./StoreReqNav.vue";
export default {
    components: { Unauthorized, StoreReqNav, Pagination },

    data() {
        return {
            req_data: null,
            to:null,
            from:null,
            search:''
        };
    },
    watch: {},
    mounted() {
        this.fetchCompletedRequisition(1);
    },
    methods: {
       
        async fetchCompletedRequisition(page) {
            const res = await this.getApi(
                "data/store_requisition/report",
                {
                    params: {
                        page,
                        search: this.search.length >= 2 ? this.search : "",
                        to: this.to,
                        from: this.from
                    }
                }
            );
            if (res.status === 200) {
                this.req_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.no-stock {
    background: #f57c00;
}
</style>
