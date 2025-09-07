<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Tax Management</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="create_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                        </Button>

                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Tax Code</th>
                                    <th scope="col">Tax Percent</th>
                                     <th>Type</th>
                                    <th scope="col">Tax Rate Formulae</th>
                                   
                                    <th scope="col">Created At</th>

                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in tax_data"
                                    :key="i"
                                >
                                    <td>{{ data.tax_code }}</td>

                                    <td>
                                        {{ data.tax_rate }}
                                    </td>
                                    <td>
                                        {{ data.type }}
                                    </td>
                                    <td>
                                        {{ data.formulae }}
                                    </td>
                                    <td>{{ data.created_at }}</td>
                                    <td>
                                        <button
                                            class="btn btn-secondary custom-button"
                                            to="#"
                                            @click="updateDialogue(data)"
                                            v-if="isUpdatePermitted"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Modal v-model="create_modal" title="Add Role">
                            <div class="row">
                                <div class="col-9">
                                    <center>
                                        <v-progress-circular
                                            v-if="isLoading"
                                            indeterminate
                                            color="primary"
                                        ></v-progress-circular>
                                    </center>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Rate (%)</label>
                                        <input
                                            type="number"
                                            placeholder="Rate"
                                            v-model="form_data.tax_rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Code i.e (A,B)</label>
                                        <input
                                            type="text"
                                            placeholder="Code"
                                            v-model="form_data.tax_code"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Type (VAT)</label>
                                        <Select v-model="form_data.type">
                                            <Option
                                                v-for="(value, i) in tax_types"
                                                :key="i"
                                                :value="value.value"
                                            >
                                                {{ value.name }}
                                            </Option>
                                        </Select>
                                    </div>
                                </div>
                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        @click="saveInfo()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal v-model="update_modal" title="Update Role">
                            <div class="row">
                                <div class="col-9">
                                    <center>
                                        <v-progress-circular
                                            v-if="isLoading"
                                            indeterminate
                                            color="primary"
                                        ></v-progress-circular>
                                    </center>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Rate (%)</label>
                                        <input
                                            type="number"
                                            placeholder="Rate"
                                            v-model="update_form_data.tax_rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Code i.e (A,B)</label>
                                        <input
                                            type="text"
                                            placeholder="Code"
                                            v-model="update_form_data.tax_code"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Tax Type (VAT)</label>
                                        <Select v-model="update_form_data.type">
                                            <Option
                                                v-for="(value, i) in tax_types"
                                                :key="i"
                                                :value="value.value"
                                            >
                                                {{ value.name }}
                                            </Option>
                                        </Select>
                                    </div>
                                </div>
                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        @click="updateInfo()"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </Modal>
                    </div>
                </div>
                <center v-else>
                    <b style="color:red;font-size:1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    data() {
        return {
            update_modal: false,
            create_modal: false,
            isLoading: false,
            form_data: {
                tax_code: "",
                tax_rate: 0,
                formulae: 0,
                type:null,
            },
            tax_types: [
                { name: "VAT", value: "vat" },
                { name: "Catering Levy", value: "catering_levy" },
                { name: "Zero Rated", value: "zero_rated" },
                { name: "Service Charge", value: "service_charge" }
            ],
            update_form_data: {
                tax_code: "",
                tax_rate: 0,
                formulae: 0,
                id: null,
                type:null,
            },
            tax_data: []
        };
    },
    methods: {
        async saveInfo() {
            this.isLoading = true;
            this.form_data.formulae =
                100 / (100 + parseFloat(this.form_data.tax_rate));
            const res = await this.callApi(
                "post",
                "data/tax/create",
                this.form_data
            );
            this.isLoading = false;

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.create_modal = false;
                this.form_data.tax_code = "";
                this.form_data.tax_rate = 0;
                (this.form_data.formulae = 0), (this.tax_data = res.data);
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
        updateDialogue(data) {
            this.update_modal = true;
            this.update_form_data = data;
        },
        async updateInfo() {
            this.isLoading = true;
            this.update_form_data.formulae =
                100 / (100 + parseFloat(this.update_form_data.tax_rate));

            const res = await this.callApi(
                "put",
                "data/tax/update",
                this.update_form_data
            );
            this.isLoading = false;

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_modal = false;
                this.update_form_data.tax_code = "";
                this.update_form_data.tax_rate = 0;
                (this.update_form_data.formulae = 0),
                    (this.tax_data = res.data);
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
        async getData() {
            this.showLoader();
            const res = await this.getApi("data/tax/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                this.tax_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        }
    },
    mounted() {
        this.getData();
    }
};
</script>
