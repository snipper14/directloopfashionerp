<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between">
                    <button
                        class="btn btn-primary"
                        @click="$emit('dashboard-active')"
                    >
                        <b>
                            <v-icon medium>{{ icons.mdiBackburger }}</v-icon>
                            BACK</b
                        >
                    </button>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3><b>Change Password</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for=""><b>Old Password</b></label>
                                    <input
                                        type="password"
                                        v-model="form_data.old_password"
                                        @focus="changeKeyboardOldPass"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""><b>New Password</b></label>
                                    <input
                                        type="password"
                                        v-model="form_data.new_password"
                                        @focus="changeKeyboardNewPass"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        ><b>Confirm Password</b></label
                                    >
                                    <input
                                        type="password"
                                        v-model="form_data.confirm_password"
                                        @focus="changeKeyboardConfirmPass"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <button
                                        class="btn btn-primary btn lg"
                                        @click="changePassword"
                                    >
                                        Change Password
                                    </button>
                                </div>
                            </div>
                            <div class="col-6">
                                <custom-board
                                    style="margin-top: 4px"
                                    @pressed="form_data.old_password = $event"
                                    v-if="numeric_keyboard.old_pass"
                                    :selfValue="form_data.old_password"
                                />
                                <custom-board
                                    style="margin-top: 4px"
                                    v-if="numeric_keyboard.new_pass"
                                    @pressed="form_data.new_password = $event"
                                    :selfValue="form_data.new_password"
                                />
                                <custom-board
                                    style="margin-top: 4px"
                                    v-if="numeric_keyboard.confirm_pass"
                                    @pressed="
                                        form_data.confirm_password = $event
                                    "
                                    :selfValue="form_data.confirm_password"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mdiBackburger } from "@mdi/js";
import CustomBoard from "./menu_components/dinerscomponents/CustomBoard.vue";
export default {
    components: { CustomBoard },
    data() {
        return {
            form_data: {
                confirm_password: "",
                new_password: "",
                old_password: "",
            },
            numeric_keyboard: {
                old_pass: true,
                new_pass: false,
                confirm_pass: false,
            },
            icons: {
                mdiBackburger,
            },
        };
    },
    mounted() {},
    methods: {
        async changePassword() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/admin/changePassword",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif(res.data.msg);
                this.logoutPOS();
            } else if (res.status === 400) {
                this.errorNotif(res.data.msg);
            } else {
                this.form_error(res);
            }
        },
        changeKeyboardOldPass() {
            this.changeKeyboardStatus("old_pass");
        },
        changeKeyboardNewPass() {
            this.changeKeyboardStatus("new_pass");
        },
        changeKeyboardConfirmPass() {
            this.changeKeyboardStatus("confirm_pass");
        },
        changeKeyboardStatus(paytype) {
            Object.keys(this.numeric_keyboard).forEach(
                (key) => (this.numeric_keyboard[key] = false)
            );
            this.numeric_keyboard[paytype] = true;
        },
    },
};
</script>
