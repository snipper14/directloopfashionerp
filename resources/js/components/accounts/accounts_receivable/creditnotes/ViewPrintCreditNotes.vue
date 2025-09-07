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
                    <v-icon dark left>
                        mdi-arrow-left
                    </v-icon>
                    Back
                </v-btn>
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <h4>Credit Notes Items</h4>

                        <button
                        
                            v-if="data.approved == 1 && isWritePermitted"
                            @click="printCreditNote()"
                            class="btn btn-primary button-custom"
                        >
                            Download Credit Note
                        </button>
                    </div>

                    Credit Note No: {{ credit_data[0].credit_no }}
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                 <th scope="col">Item</th>
                                <th scope="col">Item Price</th>
                                <th scope="col">Qty</th>
                                <th scope="col">total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in credit_data"
                                :key="i"
                            >
                               <td>
                                    {{ data.invoice.stock.code }}
                                </td>
                                <td>
                                    {{ data.invoice.stock.name }}
                                </td>
                            

                                <td>
                                    {{ cashFormatter(data.price) }}
                                </td>
                                <td>
                                    {{ data.qty }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.line_total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <add-credit-note
            v-if="active.add_credit_note"
            v-on:dashboard-active="setActiveRefresh"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
export default {
    props: ["data"],
    data() {
        return {
            active: {
                dashboard: true,
                add_credit_note: false
            },

            credit_data: [],

            search: "",

            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {},
    mounted() {
       
        this.fetchCreditNoteDetails();
    },

    methods: {
        async printCreditNote() {
            this.showLoader();
            axios({
                url: "data/credit/generateCredit/" + this.data.credit_no,
                method: "GET",
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", this.data.credit_no + ".pdf");
                document.body.appendChild(link);
                link.click();
            });
        },
        async fetchCreditNoteDetails() {
            this.showLoader();
            const res = await this.getApi(
                "data/credit/fetchByCreditNo/" + this.data.credit_no,
                {}
            );
            this.hideLoader();

            if (res.status === 200) {
                this.credit_data = res.data.results;
            
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
