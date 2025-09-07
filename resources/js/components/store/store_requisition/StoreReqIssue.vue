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
                                    <th>Avail Qty</th>
                                    <th>Issued Qty</th>
                                    <th scope="col">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in req_data"
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
                                    <td
                                        :class="[
                                            data.stock.main_store_qty < data.qty
                                                ? 'no-stock'
                                                : '',
                                        ]"
                                    >
                                        {{ data.stock.main_store_qty }}
                                    </td>
                                    <td>
                                        {{data.qty_issued}}
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            v-model="data.qty"
                                        />
                                    </td>

                                    <td>
                                        <v-btn
                                            small
                                            v-if="isUpdatePermitted"
                                            color="secondary"
                                            darl
                                            @click="issueItem(data, i)"
                                        >
                                            issue
                                        </v-btn>
                                        <router-link
                                            v-if="isDeletePermitted"
                                            class="danger"
                                            to="#"
                                            @click.native="
                                                deleteData(data.req_id, i)
                                            "
                                        >
                                            Delete
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- </form> -->
        </div>
        <div v-else><unauthorized /></div>
    </div>
</template>
<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import StoreReqNav from "./StoreReqNav.vue";
export default {
    components: { Unauthorized, StoreReqNav },

    data() {
        return {
            req_data: null,
        };
    },
    watch: {},
    mounted() {
        this.fetchCompletedRequisition();
    },
    methods: {
         async deleteData(req_id, i) {
         
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/store_requisition/destroy/" + req_id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.req_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async issueItem(data, i) {
           
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/store_requisition/issueItem",
                data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Successfully issued");
                this.req_data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
        async fetchCompletedRequisition() {
            this.showLoader();
            const res = await this.getApi("data/store_requisition/fetch");
            this.hideLoader();
            if (res.status === 200) {
                this.req_data = res.data;
            } else {
                this.form_error(res);
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
