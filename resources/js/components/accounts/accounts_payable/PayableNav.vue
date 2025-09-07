<template>
    <div class="container nopadding">
        <div class="col-md-12">
            <div>

            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'new_supplier_invoice' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('new_supplier_invoice')"
                >
                    New Invoice
                </button>
                <hr />
                <button
                    :class="[
                        'supplier_invoices' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('supplier_invoices')"
                >
                    View Invoices
                </button>
                <hr />
                 <button
                    :class="[
                        'supplier_credit_notes' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('supplier_credit_notes')"
                >
                    Supplier Credit Notes
                </button>
                <hr />
                <button
                    :class="[
                        'supplier_payments' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('supplier_payments')"
                >
                    Supplier Payment
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
