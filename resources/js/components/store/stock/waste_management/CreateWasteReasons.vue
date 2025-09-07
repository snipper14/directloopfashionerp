<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
            <!-- <ImportStock/> -->
            <div class="card">
                <div class="card-header"><h3>Wastes</h3></div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Waste Reasons *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.reasons"
                                />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <button
                                    type="button"
                                    v-if="isWritePermitted"
                                    class="btn btn-primary"
                                    @click="submitData()"
                                >
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Reasons</th>

                                    <th scope="col">Update</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in reasons_data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editMenuStock(data)"
                                        >
                                            {{ data.id }}</router-link
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            v-model="data.reasons"
                                            class="form-control"
                                        />
                                    </td>

                                    <td>
                                        <button
                                            to="#"
                                            @click="update(data, i)"
                                            class="btn btn-secondary"
                                            title="update"
                                        >
                                            Update
                                        </button>
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            v-if="isDeletePermitted"
                                            @click.native="
                                                deleteData(data.id, i)
                                            "
                                            title="delete"
                                            class="danger"
                                        >
                                            Delete
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    data() {
        return {
            value: null,
            reasons_data: [],
            form_data: {
                reasons: "",
            },
        };
    },
    components: {},
    mounted() {
        this.concurrentApiReq();
    },
    watch: {},
    methods: {
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/waste_reason/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.reasons_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async update(data) {
            this.showLoader();
            console.log(JSON.stringify(data));
            const res = await this.callApi(
                "put",
                "data/waste_reason/update",
                data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.fetchWasteReasons()]).then(
                function (results) {}
            );
            this.hideLoader();
        },

        async fetchWasteReasons() {
            const res = await this.getApi("data/waste_reason/fetch", {
                params: {},
            });

            if (res.status === 200) {
                this.reasons_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async submitData() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/waste_reason/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif(
                    " Records Details has been added successfully!"
                );
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.reasons_data = res.data;
                //  this.$emit("dashboard-active");
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
