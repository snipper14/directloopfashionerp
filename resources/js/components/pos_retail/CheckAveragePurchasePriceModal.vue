<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Average Price
                    </div>

                    <div class="card-body">
                        <span><h3><b v-if="average_price_data.res">{{ cashFormatter(average_price_data.res)}}</b><b v-else>Unavailable</b></h3></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["average_price_data"],
    data() {
        return {};
    },
    mounted() {
        
    },
};
</script>
