<template>
    <div>
        <button @click="scrollPageUp" class="btn-scroll-up">
            <v-icon class="scroll-icon" medium>{{
                icons.mdiArrowUpCircleOutline
            }}</v-icon>
        </button>
        <button @click="scrollPageDown" class="btn-scroll-down">
            <v-icon class="scroll-icon" medium>{{
                icons.mdiArrowDownCircleOutline
            }}</v-icon>
        </button>
    </div>
</template>

<script>
import { mdiArrowUpCircleOutline, mdiArrowDownCircleOutline } from "@mdi/js";
export default {
    data() {
        return {
            icons: {
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline
            }
        };
    },
    mounted() {
        console.log("Component mounted.");
    }
};
</script>
