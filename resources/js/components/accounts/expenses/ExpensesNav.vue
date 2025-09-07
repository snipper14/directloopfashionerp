<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'expenses' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('expenses')"
                >
                    Expenses
                </button>
                <hr />
                <button
                    :class="[
                        'expenses_payment_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('expenses_payment_report')"
                >
                   Expenses Payment Report
                </button>
                <hr />
               
            </nav>
        </div>
    </div>
</template>

<script></script>
