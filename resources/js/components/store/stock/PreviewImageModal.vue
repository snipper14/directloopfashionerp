<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        {{ details_data.product_name }}
                    </div>

                    <div class="card-body">
                        <img width="100%" :src="details_data.img_url" alt="" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["details_data"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted." + JSON.stringify(this.details_data));
    },
};
</script>
