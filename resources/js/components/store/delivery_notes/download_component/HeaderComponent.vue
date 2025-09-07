<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <img
                    style=" width: 8rem; height: 6rem;"
                    src="../../../../assets/images/logo.png"
                    class="img-logo"
                />
            </div>
            <div class="col-md-9" style="">
                <center>
                    <p
                        class="company"
                        style=" font-size: 3rem ;font-weight: bold"
                    >
                        {{ business_name }}
                    </p>
                </center>
            </div>
        </div>
    </div>
</template>

<script>
import { logo } from "../../../../assets/images/logo.png";

export default {
    props: ["business_name"],
    data() {
        return {
            logo: logo
        };
    },
    mounted() {
        console.log("Component mounted.");
    }
};
</script>
<style scoped></style>
