<template>
  <v-btn
    x-small
    @click="toggleSort"
  >
    {{ buttonText }}
  </v-btn>
</template>

<script>
export default {
  props: {
    initialSort: {
      type: String,
      default: 'ASC'
    },
    sortKey: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      currentSort: this.initialSort
    };
  },
  computed: {
    buttonText() {
      return this.currentSort === 'ASC' ? 'DESC' : 'ASC';
    }
  },
  methods: {
    toggleSort() {
      this.currentSort = this.currentSort === 'ASC' ? 'DESC' : 'ASC';
      this.$emit('update-sort', { key: this.sortKey, value: this.currentSort });
    }
  }
};
</script>

<style scoped>
/* Add any required styling here */
</style>
