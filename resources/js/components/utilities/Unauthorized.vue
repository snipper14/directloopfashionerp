<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                  

                    <div class="card-body">
                       <center ><b style="color:red;font-size:1.2rem">YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b>
                       <br>
                       <button class="btn btn-secondary" @click="goBack()">GO BACK</button>
                       </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
         
        },
        methods:{
            goBack(){
                this.$router.back()
            }
        },
    }
</script>
