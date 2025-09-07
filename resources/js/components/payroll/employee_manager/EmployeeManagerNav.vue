<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b>Payroll</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                 :class="[
                        'employee_manager' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('employee_manager')"
                >
                    Employees
                </button>
                <hr />
                <button
                    :class="[
                        'employee_roles' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('employee_roles')"
                >
                    Employee Roles
                </button>
                <hr />

                <button
                :class="[
                        'employee_leave' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('employee_leave')"
                >
                    Add Leave
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
