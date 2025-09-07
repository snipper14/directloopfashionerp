import Vue from "vue";
import Vuex from "vuex";
import createPersistedState from "vuex-persistedstate";
Vue.use(Vuex);

export default new Vuex.Store({
    plugins: [createPersistedState()],
    state: {
        conuter: 1000,
        deleteModalObj: {
            showDeleteModal: false,
            deleteUrl: "",
            data: null,
            deletingIndex: -1,
            isDeleted: false
        },
        waiter_list:[],
        orderArray:[],
        user: false,
        userPermission: null,
        branch: null,
        department: null,
        dine_department_data: null,
        takeaway_department_data: null,
        roomservice_department_data: null,
        req_id: "",
        order_type: "",
        order_no_takeaway: null,
        location_data: "",
        order_no: "",
        order_no2: "",
        room_data: "",
        room_service_data: null,
        order_no_merge: null,
        room_service_mb_order_no: null,
        order_data:[],
        promo_data:[],
        customer_data:{
            customer_name:"",
            customer_id:null
        },
    },
    getters: {
        getCounter(state) {
            return state.conuter;
        },
        getDeleteModalObj(state) {
            return state.deleteModalObj;
        },

        getUserPermission(state) {
            return state.userPermission;
        },

        getUser(state) {
            return state.user;
        },

        getBranch(state) {
            return state.branch;
        },
        getDepartment(state) {
            return state.department;
        },

        getReqId(state) {
            return state.req_id;
        },
        getOrderData(state) {
            return state.order_data;
        },
        getCustomerData(state){  return state.customer_data;}
    },

    mutations: {
        setPromoData(state,data){
            state.promo_data=data
        },
        setCustomerData(state,data){
            state.customer_data=data
        },
        setOrderData(state,data){
            state.order_data=data
        },
        setWaiterList(state, data){
            state.waiter_list = data;
        },
        setCartArray(state, data) {
            state.orderArray = data;
        },
        setRoomServiceDepartment(state, data) {
            state.roomservice_department_data = data;
        },
        setTakeAwayDepartment(state, data) {
            state.takeaway_department_data = data;
        },
        setDineDepartment(state, data) {
            state.dine_department_data = data;
        },
        setOrderNoTakeAway(state, data) {
            state.order_no_takeaway = data;
        },
        setRSMBOrderNo(state, data) {
            state.room_service_mb_order_no = data;
        },
        setRoomServiceData(state, data) {
            state.room_service_data = data;
        },
        setRoomData(state, data) {
            state.room_data = data;
        },
        changeTheCounter(state, data) {
            state.conuter += data;
        },
        setDeleteModal(state, data) {
            const deleteModalObj = {
                showDeleteModal: false,
                deleteUrl: "",
                data: null,
                deletingIndex: -1,
                isDeleted: data
            };
            state.deleteModalObj = deleteModalObj;
        },
        setDeletingModalObj(state, data) {
            state.deleteModalObj = data;
        },
        setUpdateUser(state, data) {
            state.user = data;
        },
        setOrderType(state, data) {
            state.order_type = data;
        },
        setOrderNoMerge(state, data) {
            state.order_no_merge = data;
        },
        setOrderNo(state, data) {
            state.order_no = data;
        },
        setOrderNo2(state, data) {
            state.order_no2 = data;
        },

        setLocation(state, data) {
            state.location_data = data;
        },
        setUserPermission(state, data) {
            state.userPermission = data;
        },
        setBranch(state, data) {
            state.branch = data;
        },
        setDepartment(state, data) {
            state.department = data;
        },
        setReqId(state, data) {
            state.req_id = data;
        }
    },

    actions: {
        changeCounterAction({ commit }, data) {
            commit("changeTheCounter", data);
        }
    }
});
