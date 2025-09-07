<template>
<div class="container ">
    <div class="col-md-12">
        <nav class="nav flex-column">
            <button
                :class="[
                    'branch' === this.$route.name ? 'nav-active' : ''
                ]"
                class="btn btn-primary btn-sm"
                @click="$router.push('branch')"
            >
                Branch
            </button>
            <hr>
            <button
                :class="[
                    'card_terminal_acc' === this.$route.name
                        ? 'nav-active'
                        : ''
                ]"
                class="btn btn-primary btn-sm"
                @click="$router.push('card_terminal_acc')"
            >
              Cashier Card Terminal Acc.
            </button>
            <hr>
          
          
            
        </nav>
    </div>
</div>
</template>

<script>
export default {
mounted() {}
};
</script>
<style scoped>
.flex-column button {
border-bottom: 2px solid #fff !important;
}
</style>
