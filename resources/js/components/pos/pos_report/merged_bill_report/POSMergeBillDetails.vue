<template>
    <div class="row">
        <div class="row">
            <div class="col-10 p-4">
                <div>Customer: {{ form_data.company_name }}</div>
                <div>Order No: {{ form_data.order_no }}</div>
                <div>Location: {{ form_data.location }}</div>
                <div>Table: {{ form_data.table }}</div>
                <br />

                <div
                    class="d-flex flex-column mt-2 mb-2"
                    v-for="(value, i) in order_data"
                    :key="i"
                    @click="cartClck(value)"
                >
                    <div class="row">
                        <div class="col-5">
                            <item-particulars-component
                            :data="value"
                            />
                        </div>
                        <div class="col-2">
                            <b> X </b> <b> {{ value.qty }}</b>
                        </div>
                        <div class="col-1">
                            <b> {{ cashFormatter(value.row_total) }}</b>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start">
                        <p class="mr-2">Unit Price /</p>

                        <p>{{ cashFormatter(value.price) }}</p>
                        <p class="ml-2">
                            Guest
                            <span class="badge badge-secondary">{{
                                value.no_guest
                            }}</span>
                        </p>
                        <p class="ml-2">
                            {{ value.notes }}
                        </p>
                    </div>
                    <div class="d-flex" v-if="value.merged_order_no">
                        <b style="color: green"
                            >Previous Order: {{ value.merged_order_no }}</b
                        >
                    </div>
                    <hr />
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <span>
                        <b>
                            TOTAL:
                            {{ cashFormatter(order_data_details.sum_total) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <span>
                        <b>
                            TAX:
                            {{ cashFormatter(order_data_details.sum_vat) }}</b
                        >
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus,
} from "@mdi/js";
import ItemParticularsComponent from '../../menu_components/dinerscomponents/ItemParticularsComponent.vue';
export default {
  components: { ItemParticularsComponent },
    props: ["order_data_details"],
    data() {
        return {
            order_data: [],
            branchInfo: "",
            userInfo: "",
            form_data: {
                order_no: "",
                company_name: "",
                table: "",
                cash_pay: 0,
                card_pay: 0,
                mpesa_pay: 0,
                credit_pay: 0,
            },

            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus,
            },
        };
    },

    mounted() {
        console.log(JSON.stringify(this.order_data_details));
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;

        this.fetchData();
    },
    methods: {
        printReceipt() {
            printJS("printReceipt", "html");
        },
        printBill() {
            this.$refs.printBillComponent.printBill();
        },
        async fetchData() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_order/fetchMergeBillDetails",
                {
                    params: {
                        order_no: this.order_data_details.order_no,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
                (this.form_data.order_no = this.order_data[0].order_no),
                    (this.form_data.company_name = this.order_data[0].customer
                        ? this.order_data[0].customer.company_name
                        : "");
                this.form_data.table = this.order_data[0].table
                    ? this.order_data[0].table.name
                    : "";
                this.form_data.location = this.order_data[0].location
                    ? this.order_data[0].location.name
                    : "";
            } else {
                this.servo();
            }
        },
    },
};
</script>
<style scoped>
.border {
    padding: 2rem !important;
}
.t-amount {
    color: #fff;
}
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printReceipt {
    visibility: hidden;
}
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}
.t-amount {
    color: #000;
    font-weight: 600;
}
</style>
