<template>
    <div class="col-12" id="print_doc" data-app>
        <div style="border: 2px solid black">
            <receipt-header />
            <hr />

            <b>Date {{ report_date }} </b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>

            <p style="padding: 0px; margin: 0px">
                <b>DAY BLANK STOCK TAKE SHEET</b>
            </p>
            <br /><br />
        </div>

        <div
            class="table-responsive"
            style="padding: 0px; margin: 0px; height: 0.1rem"
        >
            <table
                class="table table-sm table-striped table-bordered"
                style="width: 90%"
            >
                <thead>
                    <th style="padding: 0px; margin: 0px; width: 10%">Code</th>

                    <th style="padding: 0px; margin: 0px; width: 50%">Item</th>
                    <th></th>
                    <th>Selling Price</th>
                    <th>Spoilages</th>
                    <th>Physical Balance</th>
                </thead>
            </table>
        </div>
        <br />
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <tbody>
                    <tr
                        style="padding: 0px; margin: 0px; height: 0.1rem"
                        class="small-tr"
                        v-for="(data, i) in category_data"
                        :key="i"
                    >
                        <table>
                            <td>
                                <b> {{ data.category }}</b>
                            </td>

                            <tr
                                style="height: 2px"
                                class="small-tr"
                                v-for="(data_stock, i) in data.stock"
                                :key="i"
                            >
                                <td
                                    style="
                                        padding: 0px;
                                        margin: 0px;
                                        width: 10%;
                                    "
                                >
                                    {{ data_stock.code }}
                                </td>
                                <td
                                    style="
                                        padding: 0px;
                                        margin: 0px;
                                        width: 50%;
                                    "
                                >
                                    {{ data_stock.name }}
                                </td>
                                <td style="width: 30%"></td>
                                <td
                                    style="padding: 0px; margin: 0px; width: 5%"
                                >
                                    {{
                                        cashFormatter(data_stock.selling_price)
                                    }}
                                </td>
                                <td
                                    style="padding: 0px; margin: 0px; width: 5%"
                                >
                                    _________________
                                </td>
                                <td
                                    style="padding: 0px; margin: 0px; width: 5%"
                                >
                                    _________________
                                </td>
                            </tr>
                        </table>
                    </tr>
                    <tr></tr>
                </tbody>
            </table>
            <div class="row mt-3">
                <table style="width: 100%">
                    <tr>
                        <td><b> Processed By</b></td>
                        <td>
                            <b> ------------------------------------</b>
                        </td>
                    </tr>
                    <tr>
                        <td><b> Approved By</b></td>
                        <td>
                            <b> ------------------------------------- </b>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    props: ["category_data", "report_date"],
    data() {
        return {};
    },
    components: {
        ReceiptHeader,
    },

    methods: {
        printDoc: function () {
            printJS("print_doc", "html");
            // this.value = value;
        },
    },
    mounted() {},
};
</script>

<style scoped>
#print_doc {
    visibility: hidden;
}
</style>
