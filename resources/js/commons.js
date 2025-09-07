import { mapGetters } from "vuex";
import Swal from "sweetalert2/dist/sweetalert2.js";
export default {
    data() {
        return {
            isLoader: false,
            loader: null,
            init_height: 80,
            app_name: "Beyond Business ERP",
            // app_name: "Devi360  ERP",

            support_no: "+2540722495262",
           company_name: "Beyond Business ERP",
            // company_name: "Develiz Solutions ",
            sort_params: {},
            till_no: "",
         
            base_url: "http://127.0.0.1:5000/",
    
            price_group_select_data: null,
            accounts_select_data: null,
            terminal_select_data: null,
            item_classcode_select: null,
            item_type_select: null,
            item_origin_select: null,
            item_packaging_select: null,
            supplier_select_data: null,
            isExpired: false,
            expirationDate: "2026-09-07",
            check_inventory_modal: false,
            inventory_data: [],
            expense_category_select:null,
            customer_select_data:null,
            branch_select_data:null,
            main_subaccounts_select_data:null,
             department_data: null,
        };
    },
    created(){
        this.checkExpiration()
    },
    methods: {
           async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

           

            if (res.status == 200) {
                this.department_data =  res.data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.department,
                };
            });
            } else {
                this.swr("Server error occurred");
            }
        },
          async fetchLedgerSubAccounts() {
            const res = await this.getApi("data/main_ledger_account/fetch", {
                params: { search: "" },
            });

            if (res.status === 200) {
                const data = res.data;
                console.log(JSON.stringify(data));

                this.main_subaccounts_select_data = data.map((item) => ({
                   id: item.id,
                    label: `${this.formatLedgerNo(item.account_no)}.${item.name}`,
                                 
                  raw: item, // store full object for later use
                }));
                console.log(JSON.stringify(this.main_subaccounts_select_data));
                
            } else {
                this.form_error(res);
            }
        },

         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
 formatLedgerNo(acc) {
    if (!acc) return '';
    const parts = String(acc).trim().split('-');
    if (!parts.length) return acc;

    // If the first segment is a single digit, append two zeros (e.g., "6" -> "600")
    if (parts[0].length === 1) {
      parts[0] = parts[0] + '00';
    }
    return parts.join('-');
  },
        async fetchBranch() {
          
            const res = await this.getApi("data/branch/fetch", {});
        
            if (res.status == 200) {
                this.branch_select_data =    res.data.map((obj) => {
                    
                    
                    return {
                        id: obj.id,
                        label: obj.branch,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        
        formatCategory(category) {
    const map = {
      asset: 'Assets (1)',
      liability: 'Liability (2)',
      equity: 'Equity (3)',
      expense: 'Expenses (5)',
      income: 'Income (4)',
      accrued_expenses: 'Expenses (6)',
      other_expenses:' Other Expenses (7)',


    };
    return map[category] || category;
  },
        async fetchCustomer() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/customers/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.customer_select_data = res.data.results.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.company_name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCategory() {
            const res = await this.getApi("data/expense_type/fetch", {});

            if (res.status == 200) {
                this.expense_category_select = res.data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            } else {
                this.swr("Server error occurred");
            }
        },
        async checkInventory(data) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: data.id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
        sortParameter(key, stat) {
            Object.keys(this.sort_params).forEach((k) => {
                this.sort_params[k] = null;
            });

            this.sort_params[key] = stat;
        },

        async fetchSupplier() {
            const res = await this.getApi("data/suppliers/fetch", {});

            if (res.status == 200) {
                this.supplier_select_data = res.data.results.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.company_name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchPackaging() {
            const res = await this.getApi(
                "data/etims_item_packaging_code/fetch",
                {}
            );

            if (res.status == 200) {
                this.item_packaging_select = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchOrigin() {
            const res = await this.getApi(
                "data/etims_country_origin_code/fetch",
                {}
            );

            if (res.status == 200) {
                this.item_origin_select = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchItemType() {
            const res = await this.getApi(
                "data/etims_item_type_code/fetch",
                {}
            );

            if (res.status == 200) {
                this.item_type_select = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchItemClassCode() {
            const res = await this.getApi(
                "data/etims_item_class_code/fetch",
                {}
            );

            if (res.status == 200) {
                this.item_classcode_select = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCashierAccounts() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/card_terminal/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.terminal_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.account.account,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchPriceGroup() {
            const res = await this.getApi("data/price_groups/fetch", {});

            if (res.status == 200) {
                this.price_group_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.price_group,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        showLoader() {
            this.loader = this.$loading.show({
                // Optional parameters
                container: this.fullPage ? null : this.$refs.formContainer,
                canCancel: true,
            });
            //   setTimeout(() => {
            //     this.loader.hide()
            //   },10000)
        },
         showLoaderSticky() {
            this.loader = this.$loading.show({
                // Optional parameters
                container: this.fullPage ? null : this.$refs.formContainer,
                canCancel: false,
            });
            //   setTimeout(() => {
            //     this.loader.hide()
            //   },10000)
        },
        hideLoader() {
            if (this.loader) this.loader.hide();
        },

        async callApi(method, url, dataObj) {
            try {
                return await axios({
                    method: method,
                    url: url,
                    data: dataObj,
                });
            } catch (e) {
                if (e.response.status == 401 || e.response.status == 419) {
                    //     this.$router.push("logout");
                    window.location.reload();
                    return;
                }
                return e.response;
            }
        },
        async fetchAccounts() {
            this.isLoading ? this.showLoader() : "";//data/ledger_account/fetch
            const res = await this.getApi("data/branch_accounts/fetchBranchAccounts", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.accounts_select_data = this.modifyAccountSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchBranchAccounts() {
            this.isLoading ? this.showLoader() : "";//data/ledger_account/fetch
            const res = await this.getApi("data/ledger_account/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.accounts_select_data = this.modifyAccountSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyAccountSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.account,
                };
            });
            return modif;
        },
        checkExpiration() {
            const currentDate = this.getCurrentDate();
         

            if (currentDate > this.expirationDate) {
                this.isExpired = true;
            }
        },
        async getApi(url, dataObj) {
            if(!this.isExpired){
            var finalObj = {};
            if (dataObj !== undefined) {
                if (dataObj.params !== undefined) {
                    var obj = { currentRoute: this.$route.name };
                    finalObj = {
                        params: Object.assign(obj, dataObj.params),
                    };
                } else {
                    finalObj = dataObj;
                }
            }
            this.isLoader = true;
            try {
                return await axios.get(url, finalObj);
            } catch (e) {
                if (e.response.status == 401 || e.response.status == 419) {
                    //this.$router.push("logout");
                    window.location.reload();
                    return;
                }
                return e.response;
            }
        }
        },
        logoutPOS() {
            this.$router.push("waiter_logout");
            // window.location.reload();
        },
        scrollPageDown() {
            window.scrollTo(0, this.init_height);
            this.init_height += 80;
        },
        scrollPageUp() {
            window.scrollTo(0, 0);
            this.init_height = 80;
        },

        i(desc, title = "Hey") {
            success;
        },
        s(desc, title = "Great!") {
            this.$notify({
                group: "foo",
                title: title,
                text: desc,
                timer: 3000,
            });
        },
        w(desc, title = "Oops!") {
            this.$notify({
                group: "foo",
                title: title,
                text: desc,
                type: "warn",
            });
        },
        e(desc, title = "Oops!") {
            this.$notify({
                group: "foo",
                title: title,
                text: desc,
                type: "error",
                timer: 5000,
            });
        },
        swr(desc, title = "Oops") {
            this.errorNotif(desc);
        },
        errorNotif(message) {
            Swal.fire({
                position: "top-end",
                icon: "warning",
                title: message,
                showConfirmButton: false,
                timer: 2500,
            });
        },
        errorNotifN(message) {
            Swal.fire({
                position: "top-end",
                icon: "warning",
                title: message,
                showConfirmButton: false,
                timer: null,
            });
        },
        successNotif(message) {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: message,
                showConfirmButton: false,
                timer: 2500,
            });
        },
        async deleteDialogue() {
            const shouldDelete = await Swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes Delete it!",
                cancelButtonText: "No, Keep it!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                dangerMode: true,
            }).then((result) => {
                if (result.value) {
                    return result.value;
                } else {
                    this.$swal(
                        "Cancelled",
                        "Your record is still intact",
                        "info"
                    );
                }
            });
            return shouldDelete;
        },
        async confirmDialogue(msg) {
            const shouldDelete = await Swal.fire({
                title: "Are you sure?",
                text: msg,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes !",
                cancelButtonText: "No, !",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                dangerMode: true,
            }).then((result) => {
                if (result.value) {
                    return result.value;
                } else {
                    // this.$swal('Cancelled', 'Your record is still intact', 'info')
                }
            });
            return shouldDelete;
        },
        scrollTop() {
            window.scrollTo(0, 0);
        },
        servo() {
            this.swr("Server error pls reload the page!!!!");
        },
        calculateTax(tax_rate, amount) {
            var tax_amount =
                parseFloat(amount) -
                parseFloat(amount) * (100 / (100 + parseFloat(tax_rate)));

            return tax_amount;
        },
        setTime(dateStr, hours, minutes) {
            let date = new Date(dateStr);
            date.setHours(hours, minutes, 0, 0);
            return this.formatDateTime(date);
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        formatYear(input) {
            return input ? this.$moment(input).format("YYYY") : "";
        },
        formatDate(input) {
            return input ? this.$moment(input).format("YYYY-MM-DD") : "";
        },

        formatDateTime(input) {
            return input
                ? this.$moment(input).format("YYYY-MM-DD HH:mm:ss")
                : "";
        },
        formatMonthDateTime(input) {
            return input
                ? this.$moment(input).format("MMMM D, YYYY HH:mm:ss")
                : "";
        },
        formatDateToDDMMYYYY(input) {
            return input ? this.$moment(input).format("MMMM D, YYYY ") : "";
        },
        isPDF(url) {
            url = url.toLowerCase();
            const isPDF = url.endsWith(".pdf");
            return isPDF;
        },
        getCurrentDate() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, "0");
            var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
            var yyyy = today.getFullYear();

            today = yyyy + "-" + mm + "-" + dd;
            return today;
        },
        calculateProfitPercentage(sellingPrice, buyingPrice) {
            // if (buyingPrice <= 0) {
            //     console.error("Buying price must be greater than zero");
            //     return 0;
            // }

            const profit = sellingPrice - buyingPrice;
            const profitPercentage = ((profit / sellingPrice) * 100).toFixed(2);
            return profitPercentage;
            //return Math.round(profitPercentage);
        },
        round(num) {
            return +(Math.round(num + "e+2") + "e-2");
        },
        getDateTime() {
            var CurrentDateUnixTimestamp = this.$moment().unix();

            var time = this.$moment
                .unix(CurrentDateUnixTimestamp)
                .format("YYYY-MM-DD HH:mm:ss");
            return time;
        },
        getCurrentTime() {
            var CurrentDateUnixTimestamp = this.$moment().unix();

            var time = this.$moment
                .unix(CurrentDateUnixTimestamp)
                .format("HH:mm");
            return time;
        },
        getTimeStamp() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, "0");
            var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
            var yyyy = today.getFullYear();
            var time = new Date();
            var timestamp =
                time.getHours() +
                "_" +
                time.getMinutes() +
                "_" +
                time.getSeconds();
            today = yyyy + "_" + mm + "_" + dd;
            return today + "_" + timestamp;
        },
        async serverError() {
            const shouldDelete = await Swal.fire({
                title: "Server Error?",
                text: "Server error occured visit the page after a while",
                icon: "warning",
                confirmButtonText: "Yes Go Back!",
                dangerMode: true,
            });
            return shouldDelete;
        },
        userSelect(user_data) {
            let modif = user_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        goBack() {
            this.$router.back();
        },
        sleep(ms) {
            new Promise((resolve) => setTimeout(resolve, ms));
        },
        form_error(res) {
            if (res.status == 422) {
                for (let i in res.data.errors) {
                    this.e(res.data.errors[i][0]);
                }
            } else {
                this.swr("Server error try again later");
            }
        },
        successAlert(body) {
            swal("Poof!", body, "success");
        },
        logoutConfirmation() {
            Swal.fire("Auto Logout?", "Are you  still around?", "question");
        },
        checkUserPermission(key) {
            if (!this.userPermission) return true;
            let isPermitted = false;
            for (let parent of this.userPermission) {
                for (let d of parent.children) {
                    if (this.$route.name == d.name) {
                        if (d[key]) {
                            isPermitted = true;
                            break;
                        } else {
                            break;
                        }
                    }
                }
            }

            return isPermitted;
        },
        setActive: function (active, component) {
            Object.keys(active).forEach((key) => (this.active[key] = false));
            this.active[component] = true;
        },
         formatNumber(num) {
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        cashFormatter(num) {
            return Number(num).toLocaleString();
        },
        cashRounded(number) {
            const roundedNumber = Math.round(number);
            const formattedNumber = roundedNumber.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            return formattedNumber;
        },

        async getAllEmployees() {
            this.showLoader();
            const res = await this.getApi("data/employee/getAll", "");
            this.hideLoader();
            return res.data.results;
        },
        getUniqueId() {
            return (
                new Date().getTime().toString(36) +
                new Date().getUTCMilliseconds() +
                this.$store.state.user.id
            ).toUpperCase();
        },
    },

    computed: {
        ...mapGetters({
            userPermission: "getUserPermission",
        }),
        isReadPermitted() {
            return this.checkUserPermission("read");
        },
        isAdmin() {
            return this.checkUserPermission("admin");
        },
        isWritePermitted() {
            return this.checkUserPermission("write");
        },
        isUpdatePermitted() {
            return this.checkUserPermission("update");
        },
        isApprovePermitted() {
            return this.checkUserPermission("approve");
        },
        isDownloadPermitted() {
            return this.checkUserPermission("download");
        },
        isDisplayPermitted() {
            return this.checkUserPermission("display");
        },
        isDeletePermitted() {
            return this.checkUserPermission("delete");
        },
    },
};
