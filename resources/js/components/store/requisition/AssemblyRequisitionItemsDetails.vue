<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
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
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h3>Req No: {{ req_data.req_id }}</h3>

                        <button
                            v-if="isDownloadPermitted"
                            @click="downloadReq()"
                            class="btn btn-secondary btn-sm"
                        >
                            Dowload Requisition Form
                        </button>
                          <button
                            class="btn btn-primary btn-sm"
                            v-if="
                                isApprovePermitted && req_data.approved == '0'
                            "
                            to="#"
                            @click="approveRequest()"
                        >Approve</button>
                    </div>
                    <table
                        class="table table-sm table-striped table-bordered mt-3"
                    >
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Qty</th>
                                <th>Raw Material Code</th>
                                <th>Raw Material</th>
                                <th scope="col"><b> QTY STOCK</b></th>
                                <th>Raw Material Qty</th>
                                <th>Raw Material Price</th>

                                <th scope="col">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in req_data_array"
                                :key="i"
                                :class="{
                                    'low-prod':
                                        data.ingredient.qty <
                                        data.ingredient_qty
                                }"
                            >
                                <td>
                                    {{ data.stock ? data.stock.code : "" }}
                                </td>
                                <td>
                                    {{
                                        data.stock
                                            ? data.stock.product_name
                                            : ""
                                    }}
                                </td>
                                <td>{{ data.qty }}</td>
                                <td>
                                    {{
                                        data.ingredient
                                            ? data.ingredient.code
                                            : ""
                                    }}
                                </td>
                                <td>
                                    {{
                                        data.ingredient
                                            ? data.ingredient.product_name
                                            : ""
                                    }}
                                </td>
                                <td>
                                    <b>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.qty
                                                : ""
                                        }}</b
                                    >
                                </td>
                                <td>
                                    {{ cashFormatter(data.ingredient_qty) }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.ingredient_price) }}
                                </td>

                                <td>
                                    {{ cashFormatter(data.sub_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b>Total Items</b></td>
                                <td>
                                    <b>{{ cashFormatter(total_qty) }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b>Total Amount </b></td>
                                <td>
                                    <b>{{ cashFormatter(total_amount) }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["req_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            search_results: [],
            taxes: 0,
            total_amount: 0,
            total_qty: 0,
            req_data_array: []
        };
    },
    watch: {
        req_data_array: {
            deep: true,
            handler() {
                this.total_amount = this.req_data_array.reduce(function(
                    sum,
                    val
                ) {
                    return sum + val.sub_total;
                },
                0);
                this.total_qty = this.req_data_array.reduce(function(qty, val) {
                    return qty + val.ingredient_qty;
                }, 0);
            }
        }
    },

    mounted() {
        this.fetchRequisition();
    },

    methods: {
          async approveRequest() {
            const shouldDelete = await this.confirmDialogue(
                "Are sure this can't be undone!!!"
            );
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/assembly_requisition/approve",
                    {
                        req_id: this.req_data.req_id
                    }
                );

                if (res.status === 200) {
                    this.s("Approved "); 
                       this.$emit("dashboard-active");
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async downloadReq() {
            this.showLoader();
            const dataObj = {
                req_id: this.req_data.req_id
            };
            axios({
                url: "data/assembly_requisition/downloadReq",
                method: "POST",
                data: dataObj,
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.req_data.req_id + "_RQ" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                this.$emit("navigate");
            });
        },
        async fetchRequisition() {
            this.showLoader();
            const res = await this.getApi(
                "data/assembly_requisition/fetchByReqId",
                {
                    params: {
                        req_id: this.req_data.req_id
                    }
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.req_data_array = res.data;
            }
        }
    }
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
.low-prod {
    background: #ff8a80 !important;
}
</style>
