<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'store_requisition' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('store_requisition')"
                >
                    New Requisition
                </button>
                <hr />
                <button
                    :class="[
                        'store_requisition_issue' ===
                        this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="
                        $router.push('store_requisition_issue')
                    "
                >
                    Issue Items
                </button>
                <hr />
               
                
              
             
              
                <hr />
                <button
                    :class="[
                        'store_req_report' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('store_req_report')"
                >
                    Requisition Report
                </button>
            </nav>
        </div>
    </div>
</template>

<script></script>
