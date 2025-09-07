<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'requisition' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('requisition')"
                >
                    New Requisition
                </button>
                <hr />
                <button
                    :class="[
                        'single_product_requisition_dashboard' ===
                        this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="
                        $router.push('single_product_requisition_dashboard')
                    "
                >
                    Single Items
                </button>
                <hr />
                <button
                    :class="[
                        'assembly_product_requisition_dashboard' ===
                        this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="
                        $router.push('assembly_product_requisition_dashboard')
                    "
                >
                    Assembly Items
                </button>
                <hr />
                <button
                    :class="[
                        'approve_requisition' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('approve_requisition')"
                >
                    Dispatch Requistion
                </button>
                <hr />
                <button
                    :class="[
                        'dispatch_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('dispatch_report')"
                >
                    Dispatch Report
                </button>
                <hr />
                <button
                    :class="[
                        'stock_returns' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_returns')"
                >
                    Stock Returns
                </button>
                <hr />
                <button
                    :class="[
                        'return_report' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('return_report')"
                >
                    Returns Report
                </button>
            </nav>
        </div>
    </div>
</template>

<script></script>
