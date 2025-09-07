<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b> CR.</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'credit_pos' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('credit_pos')"
                >
                   Add Exchange
                </button>
                <hr />
                 <button
                    :class="[
                        'retail_returns' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('retail_returns')"
                >
                   Add Return
                </button>
                <hr />
                <button
                    :class="[
                        'reverse_sales' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('reverse_sales')"
                >
                  Reverse Sale
                </button>
                <hr />
                <button
                    :class="[
                        'pos_credit_notes' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('pos_credit_notes')"
                >
                    Return Reports 
                </button>

                <hr />
                 <button
                    :class="[
                        'retails_refunds_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('retails_refunds_report')"
                >
                    Refunds Reports 
                </button>

                <hr />
            
             
              
              
               
               
            </nav>
        </div>
    </div>
</template>

<script></script>
