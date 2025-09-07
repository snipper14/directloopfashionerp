<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'accounts_receivable' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('accounts_receivable')"
                >
                    Orders
                </button>
                <hr />
                <button
                    :class="[
                        'orders_dashboard' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('orders_dashboard')"
                >
                    New Orders
                </button>
                <hr />
                <button
                    :class="[
                        'invoices' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('invoices')"
                >
                    Invoices
                </button>
                <hr />
                  <button
                    :class="[
                        'raw_invoice_itemized_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('raw_invoice_itemized_report')"
                >
                    Raw Itemized Report
                </button>
                   <button
                    :class="[
                        'invoice_items_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('invoice_items_report')"
                >
                    Items Report
                </button>
                  <hr />
                  <button
                    :class="[
                        'invoice_profitloss' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('invoice_profitloss')"
                >
                    Invoices Profit/Loss
                </button>
                <hr />
                <button
                    :class="[
                        'new_opening_balances' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('new_opening_balances')"
                >
                    Add Opening Balance
                </button>
                <hr />
                <button
                    :class="[
                        'payments' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('payments')"
                >
                    Payments
                </button>
                <hr />
                <button
                    :class="[
                        'add_payment' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('add_payment')"
                >
                    Make Payment
                </button>
                <hr />

                <button
                    :class="[
                        'credit_notes' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('credit_notes')"
                >
                    Credit Notes
                </button>
                <hr />
                <button
                    :class="[
                        'direct_credit_notes' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_credit_notes')"
                >
                    Direct Credit Notes
                </button>
                <hr />
                  <button
                    :class="[
                        'direct_credit_notes_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_credit_notes_report')"
                >
                    Direct CreditNotes Report
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
