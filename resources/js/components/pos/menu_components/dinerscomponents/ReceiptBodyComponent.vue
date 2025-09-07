<template>
    <table width="90%">
        <tr>
            <td>
                <div class="top_row">
                    <div style="width: 5%"><b> Qty</b></div>
                    <div style="max-width: 60%"><b style="font-size:1rem"> Particulars</b></div>
                    <div style="max-width: 30%">
                        <table>
                            <tr>
                                <td><b> @cost</b></td>

                                <td><b> Amt</b></td>
                            </tr>
                        </table>
                    </div>
                </div>
              
            </td>
        </tr>
        <tr>
             ================================================

        </tr>
        <tr v-for="(data, i) in data" :key="i">
            <td>
                <div class="top_row">
                    <div style="width: 5%">
                        <b> {{ data.qty.toFixed(2) }}</b>
                    </div>
                    <div style="max-width: 70%">
                        <item-particulars-component :data="data" />
                    </div>
                    <div style="max-width: 25%">
                        <table>
                            <tr>
                                <td>
                                    <b>
                                        {{
                                            (data.selling_price.toFixed(2))
                                        }}</b
                                    >
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>

                                <td>
                                    <b> {{ (data.row_amount.toFixed(2)) }}</b>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                 <div v-if="data.discount>0" style="width: 60%;margin-top:-4">
                                <b>Discount {{ data.discount }}(%)</b>
                            </div>
            </td>
        </tr>
        <tr>
             ================================================
             
        </tr>
    </table>
</template>

<script>
import ItemParticularsComponent from "./ItemParticularsComponent.vue";
export default {
    components: { ItemParticularsComponent },
    props: ["data"],
    data() {
        return {
            branchInfo: {},
        };
    },
    mounted() {
        this.branchInfo = this.$store.state.branch;
        console.log(">>>>item>>>"+JSON.stringify(this.data));
        
    },
};
</script>
<style scoped>
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printBill {
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
