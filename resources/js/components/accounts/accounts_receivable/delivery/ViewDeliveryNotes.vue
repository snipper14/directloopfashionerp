<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>
                <h4>Delivery Notes /</h4>
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <p>
                                <b>Client / </b>{{ data.customer.company_name }}
                            </p>
                        </div>
                        <div>
                            <p><b>Invoice No / </b>{{ data.invoice_no }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Delivery No</th>
                                    <th scope="col">Invoice</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Total Items</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in delivery_note_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.delivery_no }}
                                    </td>
                                    <td>
                                        {{ data.invoice_no }}
                                    </td>
                                    <td>
                                        {{ data.delivery_date }}
                                    </td>
                                    <td>
                                        {{ data.user ? data.user.name : "" }}
                                    </td>

                                    <td>{{ data.qty_total }}</td>

                                    <td>
                                        <button
                                            v-if="isWritePermitted"
                                            @click="
                                                printDeliveryNote(
                                                    data.delivery_no
                                                )
                                            "
                                            class="btn btn-secondary custom-button"
                                            color="primary"
                                            dark
                                        >
                                            Print Delivery Note
                                        </button>
                                        <button
                                            v-if="isDeletePermitted"
                                            @click="
                                                deleteNote(data.delivery_no, i)
                                            "
                                            class="btn btn-danger custom-button"
                                            color="error"
                                            dark
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
  <PrintDeliveryTemplate
            v-if="print_d_note"
            ref="PrintDeliveryTemplate"
            :delivery_details="delivery_details"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import PrintDeliveryTemplate from './PrintDeliveryTemplate.vue';
export default {
  components: { PrintDeliveryTemplate },
    props: ["data"],
    data() {
        return {
            delivery_note_data: [],
              delivery_details: [],
            print_d_note: false,
        };
    },
    mounted() {
        this.deliveryNote();
    },
    methods: {
         async printDeliveryNote(delivery_no) {
            this.showLoader();
            const res = await this.getApi("data/delivery/fetchByDnote", {
                params: {
                    delivery_no: delivery_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.print_d_note = true;
                this.delivery_details = res.data;
                setTimeout(() => {
                    this.$refs.PrintDeliveryTemplate.printBill();
                    this.print_d_note = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async deleteNote(note_no, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "delete",
                    "data/delivery/destroy/" + note_no
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.s("Deleted");
                    this.delivery_note_data.splice(i, 1);
                } else {
                    this.swr("error occured");
                }
            }
        },
        async printDeliveryNoted(delivery_no) {
            this.showLoader();
            try {
                await axios({
                    url: "data/delivery/downLoadNote/" + delivery_no,
                    method: "GET",
                    responseType: "blob", // important
                }).then((response) => {
                    this.hideLoader();
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", delivery_no + ".pdf");
                    document.body.appendChild(link);
                    link.click();
                });
            } catch (e) {
                this.hideLoader();
                this.swr(
                    "Server error occured try again later or contact admin"
                );
            }
        },
        async deliveryNote() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/delivery/invoiceDeliveryNotes",
                {
                    customer_id: this.data.customer_id,
                    invoice_no: this.data.invoice_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.delivery_note_data = res.data.results;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
