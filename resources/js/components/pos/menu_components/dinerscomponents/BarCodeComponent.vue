<template>
    <barcode v-model="data" :options="options"></barcode>
</template>

<script>
import barcode from 'tui-vue-barcode'

    export default {
        props:['data'],
           data(){
            return{
                 height: 80,
            }
        },
          components: {
        barcode
    },
        mounted() {
            console.log('Component mounted.')
        },
          computed: {
        options() {
            return {
                height: this.height,
            };
        },
    },
    }
</script>
