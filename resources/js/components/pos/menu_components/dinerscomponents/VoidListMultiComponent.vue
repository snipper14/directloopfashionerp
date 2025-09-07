<template>
    <div class="container">
          <ul class="list-group">
        
  <li class="list-group-item" @click="onVoidAllSelect(data)" v-for="(data,i) in voided_data" :key="i">{{data.details}}</li>
 
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
        onVoidAllSelect(data){
            this.$emit('onVoidAllSelect',data)
        },
  
    },
};
</script>
