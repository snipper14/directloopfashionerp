<template>
  <div>
    <ul class="pagination">
      <li >
        <a @click="goToPage(pagination.pagination.current_page - 1)" href="#">Previous</a>
      </li>
      
    
      
      <li >
        <a @click="goToPage(pagination.pagination.current_page + 1)" href="#">Next</a>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    pagination: Object, // Pass the pagination data as a prop
  },
  mounted(){
   console.log(JSON.stringify(this.pagination.pagination));
  },
  computed: {
    totalPageCount() {
      return Array.from({ length: this.pagination.pagination.total_pages }, (_, index) => index + 1);
    },
  },
  methods: {
    goToPage(page) {
        console.log(page+"====");
     // if (page >= 1 && page <= this.pagination.total_pages && page !== this.pagination.current_page) {
        // Trigger an event or update the parent component with the selected page
        this.$emit('page-change', page);
     // }
    },
  },
};
</script>

<style scoped>
.pagination {
  display: flex;
  list-style: none;
  padding: 0;
}

.pagination li {
  margin-right: 5px;
}

.pagination a {
  text-decoration: none;
  padding: 5px 10px;
  border: 1px solid #ccc;
  cursor: pointer;
}

.pagination .active a {
  background-color: #007BFF;
  color: #fff;
}

.pagination .disabled a {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>