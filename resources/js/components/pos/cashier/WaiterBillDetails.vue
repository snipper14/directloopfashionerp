<template>
    <div>
        <div class="col-12">
           <order-details-component
           :data="bill_data"
           />
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
    mdiArrowUpCircleOutline,
    mdiArrowDownCircleOutline,
} from "@mdi/js";
import OrderDetailsComponent from '../menu_components/dinerscomponents/OrderDetailsComponent.vue';
export default {
  components: { OrderDetailsComponent },
    props: ["bill_data"],
    data() {
        return {
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
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline,
            },
        };
    },
    mounted() {},
};
</script>

<style scoped>
.bill-active {
    background: #bec5b7 !important;
}
.bill-wrapper {
    height: 300px;
    overflow-x: hidden;
    overflow-y: auto;
}
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
</style>
