<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Code. </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.code"
                                    autocomplete="new-user-street-address"
                                    placeholder="Code"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Product Name </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.product_name"
                                    autocomplete="new-user-street-address"
                                    placeholder="Product Name"
                                />
                            </div>
                            <div class="col-md-2 form-group">
                                <v-btn
                                    color="secondary"
                                    @click="search_results = []"
                                >
                                    Clear Search
                                </v-btn>
                            </div>
                            <div class="col-md-2 form-group">
                                <v-btn
                                    v-if="prescription_data.length > 0"
                                    color="primary"
                                    @click="printPrescription"
                                >
                                    Print Prescription
                                </v-btn>
                            </div>
                        </div>
                        <div class="col-md-12 border border-secondary">
                            <div class="table-responsive custom-table">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Repeat Pattern</th>
                                            <th scope="col">Note</th>
                                            <th scope="col">
                                                Prescription time
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in search_results"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                <textarea
                                                    class="form-control custom-text"
                                                    type="text"
                                                    v-model="data.product_name"
                                                />
                                            </td>

                                            <td>
                                                <input
                                                    class="form-control custom-text"
                                                    type="number"
                                                    v-model="data.qty"
                                                />
                                            </td>

                                            <td>
                                                <textarea
                                                    class="form-control"
                                                    type="text"
                                                    v-model="
                                                        data.repeat_pattern
                                                    "
                                                />
                                            </td>
                                            <td>
                                                <textarea
                                                    class="form-control"
                                                    type="text"
                                                    v-model="data.note"
                                                />
                                            </td>
                                            <td>
                                                <date-picker
                                                    type="datetime"
                                                    width="100px"
                                                    v-model="
                                                        data.prescription_date_time
                                                    "
                                                ></date-picker>
                                            </td>
                                            <td>
                                                <button
                                                    v-if="isWritePermitted"
                                                    class="btn btn-primary btn-sm"
                                                    @click="submitRecords(data)"
                                                >
                                                    Add
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <h4><b>Prescriptions</b></h4>
                            <div class="table-responsive custom-table">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Repeat Pattern</th>
                                            <th scope="col">Note</th>
                                            <th scope="col">
                                                Prescription Start Date
                                            </th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in prescription_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.product_name }}
                                            </td>

                                            <td>
                                                {{ data.qty }}
                                            </td>

                                            <td>
                                                {{ data.repeat_pattern }}
                                            </td>
                                            <td>
                                                {{ data.note }}
                                            </td>
                                            <td>
                                                {{
                                                    formatMonthDateTime(
                                                        data.prescription_date_time
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <v-btn
                                                    title="delete"
                                                    v-if="isDeletePermitted"
                                                    color="danger"
                                                    x-small
                                                    @click="
                                                        deleteRecords(data, i)
                                                    "
                                                >
                                                    Delete
                                                </v-btn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <PrintPrescriptionTemplate
            v-if="show_print"
            ref="PrintPrescriptionTemplate"
            :prescription_data="prescription_data"
            :patient_info="patient_info"
            :printed_date="printed_date"
            :user="user"
            :branch="branch"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import PrintPrescriptionTemplate from "./PrintPrescriptionTemplate.vue";
export default {
    components: { PrintPrescriptionTemplate },
    props: ["edit_data", "customer_id"],
    data() {
        return {
            patient_info: null,
            show_print: false,
            search_results: [],
            prescription_data: [],
            branch: {},
            user: {},
            printed_date: null,
            search_query: {
                code: "",
                product_name: "",
            },
        };
    },

    mounted() {
        this.printed_date = this.formatMonthDateTime(this.getDateTime());
        console.log(this.printed_date);
        this.branch = this.$store.state.branch;
        this.user = this.$store.state.user;
        this.fetchPrescription();
        console.log(JSON.stringify(this.edit_data));
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        async deleteRecords(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "DELETE",
                "data/medical/destroy/" + data.id,
                {}
            );
            this.hideLoader();
            if (res.status == 200) {
                this.prescription_data.splice(i, 1);
            }
        },
        async printPrescription() {
            const res = await this.getApi("data/medical/fetchPatient", {
                params: {
                    customer_id: this.customer_id,
                },
            });
            if (res.status == 200) {
                this.show_print = true;
                this.patient_info = res.data;
                setTimeout(() => {
                    this.$refs.PrintPrescriptionTemplate.printBill();
                    this.show_print = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async fetchPrescription() {
            this.showLoader();
            const res = await this.getApi("data/medical/fetchPrescription", {
                params: {
                    customer_id: this.customer_id,
                    entry_code: this.edit_data.entry_code,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.prescription_data = res.data;
            }
        },
        async submitRecords(data) {
            this.showLoader();
            const mergedObject = Object.assign({}, data, {
                customer_id: this.customer_id,
                entry_code: this.edit_data.entry_code,
            });
            const res = await this.callApi(
                "PUT",
                "data/medical/updatePrescription",
                mergedObject
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Added");
                this.prescription_data = res.data;
            } else {
                this.form_error(res);
            }
        },

        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    stock_id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    repeat_pattern: "",
                    note: "",
                    prescription_date_time: "",
                };
            });
            return modif;
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
.custom-text {
    font-size: 10px !important;
    font-weight: bold !important;
}
</style>
