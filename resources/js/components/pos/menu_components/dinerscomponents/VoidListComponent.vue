<template>
    <div class="container">
          <ul class="list-group">
        
  <li class="list-group-item" @click="onVoidSelect(data)" v-for="(data,i) in voided_data" :key="i">{{data.details}}</li>
 
</ul>
    </div>
</template>

<script>
export default {
    props:['voided_data'],
    data() {
        return {
           
        };
    },
    mounted() {
       
     
    },
    methods:{
        onVoidSelect(data){
            this.$emit('onVoidSelect',data)
        },

    },
};
</script>
