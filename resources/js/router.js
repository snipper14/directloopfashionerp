import Router from "vue-router";
import Vue from "vue";
import VueRouter from "vue-router";

import home from "./components/home.vue";
import login from "./components/login.vue";
import AdminLogin from "./components/AdminLogin.vue";
import Logout from "./components/Logout.vue";
import AssignRoles from "./components/roles/AssignRoles.vue";
import CreateRoles from "./components/roles/CreateRoles.vue";
import UserDashboard from "./components/users/UserDashboard.vue";
import CreateUsers from "./components/users/CreateUsers.vue";
import DepartmentDashboard from "./components/departments/DepartmentDashboard.vue";
import BranchDashboard from "./components/branch/BranchDashboard.vue";
import StockDashboard from "./components/store/stock/StockDashboard.vue";
import MergedStockReport from "./components/store/stock/MergedStockReport.vue";
import ShelfAllocationDashboard from "./components/store/stock/ShelfAllocationDashboard.vue";
import TrashedProducts from "./components/store/stock/TrashedProducts.vue";
import ProductCategoryDashboard from "./components/store/stock/product_category/ProductCategoryDashboard.vue";
import CreateUnit from "./components/store/stock/unit/CreateUnit.vue";
import MenuProductDashboard from "./components/store/menu/menu_stock/MenuProductDashboard.vue";
import SupplierDashboard from "./components/suppliers/SupplierDashboard.vue";
import CustomerDashboard from "./components/customers/CustomerDashboard.vue";
import PurchaseDashboard from "./components/store/purchase/PurchaseDashboard.vue";
import OrderDeliveryDashboard from "./components/store/order_delivery/OrderDeliveryDashboard.vue";
import Tax from "./components/settings/Tax.vue";
import DeliveryNotesDashboard from "./components/store/delivery_notes/DeliveryNotesDashboard.vue";
import UnconfirmedOrdersDashboard from "./components/store/unconfirmed_orders/UnconfirmedOrdersDashboard.vue";
import SingleProductRequisitionDashboard from "./components/store/requisition/SingleProductRequisitionDashboard.vue";
import NewRequisition from "./components/store/requisition/NewRequisition.vue";
import AssemblyProductRequistionDashboard from "./components/store/requisition/AssemblyProductRequistionDashboard.vue";
import ApproveRequistion from "./components/store/requisition/ApproveRequistion.vue";
import DispatchReport from "./components/store/requisition/DispatchReport.vue";
import StockReturns from "./components/store/requisition/StockReturns.vue";
import ReturnsReport from "./components/store/requisition/ReturnsReport.vue";
import PurchaseReturns from "./components/store/purchase_returns/PurchaseReturns.vue";
import Floor from "./components/layout_builder/Floor.vue";
import AccountsReceivableDashboard from "./components/accounts/accounts_receivable/AccountsReceivableDashboard.vue";
import OrdersDashboard from "./components/accounts/accounts_receivable/orders/OrdersDashboard.vue";
import InvoiceDashboard from "./components/accounts/accounts_receivable/invoices/InvoiceDashboard.vue";
import DebtorsOpeningBalances from "./components/accounts/accounts_receivable/opening_balances/DebtorsOpeningBalances.vue";
import PaymentDashboard from "./components/accounts/accounts_receivable/payments/PaymentDashboard.vue";
import NewPayment from "./components/accounts/accounts_receivable/payments/NewPayment.vue";
import TransactionDashboard from "./components/accounts/transactions/TransactionDashboard.vue";
import InvoiceReports from "./components/accounts/accounts_receivable/receivable_reports/InvoiceReports.vue";
import PaymentReport from "./components/accounts/accounts_receivable/receivable_reports/PaymentReport.vue";
import CustomerLedger from "./components/accounts/accounts_receivable/receivable_reports/CustomerLedger.vue";
import NewSupplierInvoice from "./components/accounts/accounts_payable/NewSupplierInvoice.vue";
import SupplierInvoiceDashboard from "./components/accounts/accounts_payable/SupplierInvoiceDashboard.vue";
import SupplierPaymentsDashboard from "./components/accounts/accounts_payable/SupplierPaymentsDashboard.vue";
import SupplierInvoicePaymentReport from "./components/accounts/accounts_payable/reports/SupplierInvoicePaymentReport.vue";
import SupplierPaymentReports from "./components/accounts/accounts_payable/reports/SupplierPaymentReports.vue";
import SupplierDebts from "./components/accounts/accounts_payable/reports/SupplierDebts.vue";
import CreditDashboard from "./components/accounts/accounts_receivable/creditnotes/CreditDashboard.vue";
import EmployeeDashboard from "./components/payroll/employee_manager/employee/EmployeeDashboard.vue";
import EmployeeTypeDashBoard from "./components/payroll/employee_manager/employee_role/EmployeeTypeDashBoard.vue";
import ProductionDashboard from "./components/payroll/employee_production/ProductionDashboard.vue";
import ProductDashBoard from "./components/payroll/products/ProductDashBoard.vue";
import TimeLogDashboard from "./components/payroll/timelogs/TimeLogDashboard.vue";
import CasualTimeLogsDashboard from "./components/payroll/casuals/CasualTimeLogsDashboard.vue";
import LoanDashboard from "./components/payroll/loan/LoanDashboard.vue";
import AllowanceDashboard from "./components/payroll/allowance/AllowanceDashboard.vue";
import DeductionDashboard from "./components/payroll/deductions/DeductionDashboard.vue";
import StatutoryDashboard from "./components/payroll/statutory/StatutoryDashboard.vue";
import AdvanceDashboard from "./components/payroll/advance/AdvanceDashboard.vue";
import CommissionDashboard from "./components/payroll/commission/CommissionDashboard.vue";
import PayrollDashboard from "./components/payroll/payroll/PayrollDashboard.vue";
import PayslipsGenericReport from "./components/payroll/payroll/PayslipsGenericReport.vue";
import ViewPayslip from "./components/payroll/payroll/ViewPayslip.vue";
import PayslipGroupedReport from "./components/payroll/payroll/PayslipGroupedReport.vue";
import SummarisedPayslipReport from "./components/payroll/payroll/SummarisedPayslipReport.vue";
import Floor2 from "./components/layout_builder/Floor2.vue";
import AddIngredients from "./components/store/stock/stock_production/AddIngredients.vue";

import AddProductionCost from "./components/store/stock/stock_production/AddProductionCost.vue";
import RoomStandardDashboard from "./components/rooms/room_standard/RoomStandardDashboard.vue";
import RoomsDashboard from "./components/rooms/Rooms/RoomsDashboard.vue";
import RoomPackage from "./components/rooms/RoomPackage/RoomPackage.vue";
import RoomRates from "./components/rooms/RoomsRates/RoomRates.vue";
import LocationDashboard from "./components/locations/LocationDashboard.vue";
import TablesDashboard from "./components/locations/TablesDashboard.vue";

import posmenu from "./components/pos/posmenu.vue";
import DineIn from "./components/pos/menu_components/DineIn.vue";
import posnav from "./components/pos/posnav.vue";
import CreateOrders from "./components/pos/menu_components/CreateOrders.vue";
import PosLogout from "./components/pos/PosLogout.vue";
import BillsDashboard from "./components/pos/menu_components/bills/BillsDashboard.vue";
import ViewBills from "./components/pos/menu_components/bills/ViewBills.vue";
import SplitBillsDashboard from "./components/pos/menu_components/split_bills/SplitBillsDashboard.vue";
import DropDown from "./components/store/stock/stock_production/DropDown.vue";
import CashierDashboard from "./components/pos/cashier/CashierDashboard.vue";

import MergeBill from "./components/pos/menu_components/bills/MergeBill.vue";
import TransferTable from "./components/pos/menu_components/bills/TransferTable.vue";
import RoomBooking from "./components/accomodation/rooms/RoomBooking.vue";
import RoomSelect from "./components/accomodation/rooms/RoomSelect.vue";
import GuestDashboard from "./components/accomodation/guest/GuestDashboard.vue";
import CurrentRoomReservationsCalendar from "./components/accomodation/rooms/CurrentRoomReservationsCalendar.vue";
import CurrentReservationTabularReport from "./components/accomodation/rooms/accomodation_reports/CurrentReservationTabularReport.vue";
import RoomCheckout from "./components/accomodation/rooms/RoomCheckout.vue";
import HouseKeepingDashboard from "./components/accomodation/housekeeping/HouseKeepingDashboard.vue";
import HouseKeepingReport from "./components/accomodation/housekeeping/HouseKeepingReport.vue";
import RoomMaintenance from "./components/accomodation/housekeeping/RoomMaintenance.vue";
import RoomServiceSelect from "./components/pos/room_service/RoomServiceSelect.vue";
import RoomServiceOrder from "./components/pos/room_service/RoomServiceOrder.vue";
import RoomBills from "./components/pos/room_service/RoomBills.vue";
import POSReportDashboard from "./components/pos/pos_report/dine_in_reports/POSReportDashboard.vue";
import DailySalesItemsReport from "./components/pos/pos_report/dine_in_reports/DailySalesItemsReport.vue";
import ProfitLossSummary from "./components/pos/pos_report/dine_in_reports/ProfitLossSummary.vue";
import DineProductDetailsReport from "./components/pos/pos_report/DineProductDetailsReport.vue";
import RoomServicesSalesReport from "./components/pos/pos_report/room_service_reports/RoomServicesSalesReport.vue";
import AllSaleItemReport from "./components/pos/pos_report/AllSaleItemReport.vue";
import AccompanimentReports from "./components/pos/pos_report/AccompanimentReports.vue";
import VoidSaleRecord from "./components/pos/pos_report/VoidSaleRecord.vue";
import MergeRoomServiceBills from "./components/pos/room_service/MergeRoomServiceBills.vue";
import CreateTakeAwayOrders from "./components/pos/takeaway/CreateTakeAwayOrders.vue";
import TakeAwaySelectDept from "./components/pos/takeaway/TakeAwaySelectDept.vue";
import TakeAwayBills from "./components/pos/takeaway/TakeAwayBills.vue";
import TakeAwaySaleReport from "./components/pos/pos_report/TakeAwaySaleReport.vue";
import TakeAwayItemDetailsReport from "./components/pos/pos_report/TakeAwayItemDetailsReport.vue";
import EmployeeSaleSalaryDeductions from "./components/pos/pos_report/EmployeeSaleSalaryDeductions.vue";
import SalarySalesDeductionDashboard from "./components/payroll/salary_deduction/SalarySalesDeductionDashboard.vue";
import PendingCheckoutsReport from "./components/accomodation/rooms/PendingCheckoutsReport.vue";
import CashierOrders from "./components/pos/cashier/CashierOrders.vue";
import RoomServiceCashier from "./components/pos/cashier/RoomServiceCashier.vue";
import TakeAwayOrders from "./components/pos/cashier/TakeAwayOrders.vue";
import CashierBalanceDashboard from "./components/pos/cashier_balances/CashierBalanceDashboard.vue";
import CashierBalanceReport from "./components/pos/cashier_balances/CashierBalanceReport.vue";
import DirectPurchaseDashboard from "./components/store/direct_purchase/DirectPurchaseDashboard.vue";

import DirectOrderDeliveryDashboard from "./components/store/direct_purchse_delivery/DirectOrderDeliveryDashboard.vue";
import StockSplitingSettings from "./components/store/stock/spliting/StockSplitingSettings.vue";
import SplitProduct from "./components/store/stock/spliting/SplitProduct.vue";
import SplitProductReport from "./components/store/stock/spliting/SplitProductReport.vue";
import StockTakeReport from "./components/store/stock/StockTakeReport.vue";
import MergedBillsReport from "./components/pos/pos_report/merged_bill_report/MergedBillsReport.vue";
import SplitBillPOSReport from "./components/pos/pos_report/split_bill_report/SplitBillPOSReport.vue";
import LoginPos from "./components/LoginPos.vue";

import DirectOrderDeliveryReport from "./components/store/direct_purchse_delivery/DirectOrderDeliveryReport.vue";
import IssueProduct from "./components/store/issue_product/IssueProduct.vue";
import InternalIssueReport from "./components/store/issue_product/InternalIssueReport.vue";
import InternalIssueReportDetails from "./components/store/issue_product/InternalIssueReportDetails.vue";

import DirectGRNReport from "./components/store/direct_purchse_delivery/DirectGRNReport.vue";
import UpdateStock from "./components/store/update_stock/UpdateStock.vue";
import StockDeductionReport from "./components/store/update_stock/StockDeductionReport.vue";

import PhysicalStockUpdateReport from "./components/store/update_stock/PhysicalStockUpdateReport.vue";
import StockMovementReport from "./components/store/stock/stock_movt_report/StockMovementReport.vue";
import StockMovtLogs from "./components/store/stock/stock_movt_report/StockMovtLogs.vue";
import DepartmentStockMovt from "./components/store/stock/stock_movt_report/DepartmentStockMovt.vue";
import RoomServiceDeptSelect from "./components/pos/room_service/RoomServiceDeptSelect.vue";
import DepartmentStockMovtLogs from "./components/store/stock/stock_movt_report/DepartmentStockMovtLogs.vue";
import DailyAccomadationReport from "./components/accomodation/rooms/DailyAccomadationReport.vue";
import InhouestGuestReport from "./components/accomodation/rooms/InhouestGuestReport.vue";
import ReceptionBalanceDashboard from "./components/accomodation/accomodation_balances/ReceptionBalanceDashboard.vue";
import ReceptionBalancesReport from "./components/accomodation/accomodation_balances/ReceptionBalancesReport.vue";
import UnclearedSalesBills from "./components/pos/menu_components/bills/UnclearedSalesBills.vue";
import ClearWaitersBills from "./components/pos/cashier/ClearWaitersBills.vue";
import RoomBillPayments from "./components/pos/room_service/RoomBillPayments.vue";
import UnclearedRoomServiceSales from "./components/pos/room_service/UnclearedRoomServiceSales.vue";
import ClearWaiterRSPayments from "./components/pos/cashier/ClearWaiterRSPayments.vue";
import BookConference from "./components/conference/conference_booking/BookConference.vue";
import ConferenceBookingReport from "./components/conference/conference_booking/ConferenceBookingReport.vue";
import VoidSettings from "./components/pos/pos_settings/VoidSettings.vue";
import RoomTransferReport from "./components/accomodation/rooms/accomodation_reports/RoomTransferReport.vue";
import CreditNotesReport from "./components/accomodation/rooms/reservation_creditnote/CreditNotesReport.vue";
import DineCategoryReports from "./components/pos/pos_report/dine_in_reports/DineCategoryReports.vue";
import RoomServiceCategoryReport from "./components/pos/pos_report/room_service_reports/RoomServiceCategoryReport.vue";

import UserReportSummary from "./components/pos/pos_report/UserReportSummary.vue";
import AllSaleReports from "./components/pos/pos_report/AllSaleReports.vue";
import MpesaReport from "./components/pos/pos_report/MpesaReport.vue";
import GuestCompanyDashboard from "./components/accomodation/guest/GuestCompanyDashboard.vue";
import SystemReportsDashboard from "./components/reports/SystemReportsDashboard.vue";
import UserLoginDashboard from "./components/reports/UserLoginDashboard.vue";
import loginExternal from "./components/loginExternal.vue";
import StockProductionDashboard from "./components/store/stock/stock_production/StockProductionDashboard.vue";
import fronthome from "./components/fronthome.vue";
import TableBookings from "./components/table_bookings/TableBookingsReport.vue";
import EnquiriesReport from "./components/restaurant/home_components/EnquiriesReport.vue";
import OrderHome from "./components/restaurant/order/OrderHome.vue";
import ArchivedReport from "./components/pos/pos_settings/archived_sales/ArchivedReport.vue";
import StoreIssue from "./components/store/store_issue/StoreIssue.vue";
import MainStoreIssueReport from "./components/store/store_issue/MainStoreIssueReport.vue";
import MainStoreIssueDetails from "./components/store/store_issue/MainStoreIssueDetails.vue";
import TakeAway from "./components/takeaway/TakeAway.vue";
import CreateTakeawayOrders from "./components/takeaway/CreateTakeawayOrders.vue";

import TakeawayOrders from "./components/takeaway/TakeawayOrders.vue";
import Checkout from "./components/restaurant/Checkout.vue";
import OnlineOrders from "./components/online_orders/OnlineOrders.vue";
import KitchenIssueDashboard from "./components/store/kitchen_issue/KitchenIssueDashboard.vue";
import DrinksIssueDept from "./components/store/drinks_issue/DrinksIssueDept.vue";
import StoreRequisitionDashboard from "./components/store/store_requisition/StoreRequisitionDashboard.vue";
import StoreReqIssue from "./components/store/store_requisition/StoreReqIssue.vue";
import StoreReqReport from "./components/store/store_requisition/StoreReqReport.vue";
import ExpensesDashboard from "./components/accounts/expenses/ExpensesDashboard.vue";
import PettyCashDashboard from "./components/accounts/pettycash/PettyCashDashboard.vue";
import ArchivedSalesItems from "./components/pos/pos_settings/archived_sales/ArchivedSalesItems.vue";
import ArchivedGroupedItems from "./components/pos/pos_settings/archived_sales/ArchivedGroupedItems.vue";
import HomeIssue from "./components/store/home_issue/HomeIssue.vue";
import HomeIssueReport from "./components/store/home_issue/HomeIssueReport.vue";
import InventoryDashboard from "./components/inventory/InventoryDashboard.vue";
import RetailPOS from "./components/pos_retail/RetailPOS.vue";
import POSOrders from "./components/pos_retail/POSOrders.vue";
import CreditNoteDashboard from "./components/pos_retail/credit_pos/CreditNoteDashboard.vue";
import POSCreditReport from "./components/pos_retail/credit_pos/POSCreditReport.vue";
import RefundReport from "./components/pos_retail/credit_pos/RefundReport.vue";
import WasteManagementDashboard from "./components/store/stock/waste_management/WasteManagementDashboard.vue";
import WasteReport from "./components/store/stock/waste_management/WasteReport.vue";
import LedgerDashboard from "./components/accounts/general_ledger/LedgerDashboard.vue";
import LedgerAccountsDashboard from "./components/accounts/general_ledger/LedgerAccountsDashboard.vue";
import AccountStatementDashboard from "./components/accounts/general_ledger/AccountStatementDashboard.vue";
import AddLedgerEntries from "./components/accounts/general_ledger/AddLedgerEntries.vue";
import ChartAccountsDashboard from "./components/accounts/general_ledger/sub_accounts/ChartAccountsDashboard.vue";
import TrialBalance from "./components/accounts/ledger_reports/TrialBalance.vue";
import ExpensesPaymentReport from "./components/accounts/expenses/ExpensesPaymentReport.vue";
import CreditNoteInvoiceDash from "./components/accounts/accounts_payable/payable_creditnotes/CreditNoteInvoiceDash.vue";
import SupplierCreditNoteReport from "./components/accounts/accounts_payable/reports/SupplierCreditNoteReport.vue";
import AssetManagerDashbaord from "./components/accounts/asset_manager/AssetManagerDashboard.vue";
import AssetCategoryDashboard from "./components/accounts/asset_manager/AssetCategoryDashboard.vue";
import BalanceSheet from "./components/accounts/ledger_reports/BalanceSheet.vue";
import AssetAllocationDashbaord from "./components/accounts/asset_manager/asset_allocation/AssetAllocationDashbaord.vue";
import RevokeAllocation from "./components/accounts/asset_manager/asset_allocation/RevokeAllocation.vue";
import AssetMaintenanceDashboard from "./components/accounts/asset_manager/AssetMaintenanceDashboard.vue";
import ProfitLoss from "./components/accounts/ledger_reports/ProfitLoss.vue";
import EmployeeLeave from "./components/payroll/employee_manager/employee/EmployeeLeave.vue";
import ReceivableAgeingReport from "./components/accounts/general_ledger/ReceivableAgeingReport.vue";
import PayableAgeingReport from "./components/accounts/general_ledger/PayableAgeingReport.vue";
import QuotationDashboard from "./components/accounts/quotations/QuotationDashboard.vue";
import NewQuotation from "./components/accounts/quotations/NewQuotation.vue";
import ExecProfitLoss from "./components/accounts/executive_reports/ExecProfitLoss.vue";
import ExcecutiveInvoiceReport from "./components/accounts/executive_reports/ExcecutiveInvoiceReport.vue";
import ExecutiveProductSalesReport from "./components/accounts/executive_reports/ExecutiveProductSalesReport.vue";
import ExecutiveCummulativeProductSalesReport from "./components/accounts/executive_reports/ExecutiveCummulativeProductSalesReport.vue";
import ExcecUserBranchSaleReport from "./components/accounts/executive_reports/ExcecUserBranchSaleReport.vue";
import InvoiceSummarySaleReport from "./components/accounts/executive_reports/InvoiceSummarySaleReport.vue";
import GRNExecutiveReport from "./components/accounts/executive_reports/GRNExecutiveReport.vue";
import PriceGroupDashboard from "./components/store/stock/price_groups/PriceGroupDashboard.vue";
import StockConversionDashboard from "./components/store/stock/stock_conversion/StockConversionDashboard.vue";

import IssueConversionReport from "./components/store/stock/stock_conversion/IssueConversionReport.vue";
import StockDisassemblyDashboard from "./components/store/stock/stock_disassembly/StockDisassemblyDashboard.vue";
import LandingPage from "./components/LandingPage.vue";
import DirectCreditNoteDashboard from "./components/accounts/accounts_receivable/direct_creditnotes/DirectCreditNoteDashboard.vue";
import DirectCreditNoteReport from "./components/accounts/accounts_receivable/direct_creditnotes/DirectCreditNoteReport.vue";
import mainapp from "./components/mainapp.vue";
import GRNBatchReport from "./components/store/delivery_notes/GRNBatchReport.vue";
import EtimsStockDashboard from "./components/store/stock/etims_stock/EtimsStockDashboard.vue";

import ShelfDashboard from "./components/shelf/ShelfDashboard.vue";

import CardTerminals from "./components/branch/CardTerminals.vue";
import CardPaymentReport from "./components/pos/pos_report/CardPaymentReport.vue";
import SaleSummaryWithActitivityReport from "./components/pos/pos_report/SaleSummaryWithActitivityReport.vue";
import EtimsItemClassCodeDashboard from "./components/store/stock/etims_stock/item_class_code/EtimsItemClassCodeDashboard.vue";
import ItemTypeCodeDashboard from "./components/store/stock/etims_stock/item_type_code/ItemTypeCodeDashboard.vue";
import CountryOriginCode from "./components/store/stock/etims_stock/country_origin/CountryOriginCode.vue";
import PackagingCodeDashboard from "./components/store/stock/etims_stock/packagin_code/PackagingCodeDashboard.vue";
import EtimsAllSaleReport from "./components/pos/pos_report/EtimsAllSaleReport.vue";
import NonEtimsReport from "./components/pos/pos_report/NonEtimsReport.vue";
import InventoryBulkStockTake from "./components/inventory/InventoryBulkStockTake.vue";
import InventoryStockTakeReport from "./components/inventory/InventoryStockTakeReport.vue";
import StockMovementHistory from "./components/store/stock/StockMovementHistory.vue";
import LowStockDashboard from "./components/store/purchase/LowStockDashboard.vue";
import ReturnGRNReport from "./components/store/delivery_notes/ReturnGRNReport.vue";
import PatientsDashboard from "./components/clinic/patients/PatientsDashboard.vue";
import ArchivedSalesReport from "./components/pos/pos_report/ArchivedSalesReport.vue";
import BranchTransferDashboard from "./components/store/branch_transfer/BranchTransferDashboard.vue";
import BranchStockTransferReport from "./components/store/branch_transfer/BranchStockTransferReport.vue";
import ReceiveStockTransffered from "./components/store/branch_transfer/ReceiveStockTransffered.vue";
import ReceivableStockReport from "./components/store/branch_transfer/ReceivableStockReport.vue";
import RetailsReturnsDashboard from "./components/pos_retail/credit_pos/RetailsReturnsDashboard.vue";
import GenerateP9 from "./components/payroll/payroll/GenerateP9.vue";
import BranchInventoryComparisonReport from "./components/reports/inventory_reports/BranchInventoryComparisonReport.vue";
import VscuAllsalesEtimsReport from "./components/pos/pos_report/VscuAllsalesEtimsReport.vue";
import NonvscuEtimsSalesReport from "./components/pos/pos_report/NonvscuEtimsSalesReport.vue";
import CashierCloseShifZreport from "./components/pos/cashier/cashier_z_report/CashierCloseShifZreport.vue";
import CashierZReport from "./components/pos/cashier/cashier_z_report/CashierZReport.vue";
import CashDropDashboard from "./components/pos/cashier_balances/CashDropDashboard.vue";
import CashDropReport from "./components/pos/cashier_balances/CashDropReport.vue";
import PrintLabels from "./components/store/stock/print_labels/PrintLabels.vue";
import SelectCustomer from "./components/preorders/SelectCustomer.vue";
import PreorderReport from "./components/preorders/PreorderReport.vue";
import CompletedPreorderReport from "./components/preorders/CompletedPreorderReport.vue";
import DirectRetailCreditNote from "./components/retail_creditnote/directcredit/DirectRetailCreditNote.vue";
import DirectRetailCreditReport from "./components/retail_creditnote/directcredit/DirectRetailCreditReport.vue";
import PromosDashboard from "./components/retail_promos/PromosDashboard.vue";
import PromosReport from "./components/retail_promos/PromosReport.vue";
import NewMpesaDrops from "./components/pos/cashier_balances/mpesadrop/NewMpesaDrops.vue";
import MpesaDropsReport from "./components/pos/cashier_balances/mpesadrop/MpesaDropsReport.vue";
import AllSaleReportCollapsed from "./components/pos/pos_report/AllSaleReportCollapsed.vue";
import InvoiceProfitLoss from "./components/accounts/accounts_receivable/invoices/invoicereport/InvoiceProfitLoss.vue";
import InvoiceItemsGroupedReport from "./components/accounts/accounts_receivable/invoices/invoicereport/InvoiceItemsGroupedReport.vue";
import InvoiceRawItemsReport from "./components/accounts/accounts_receivable/invoices/invoicereport/InvoiceRawItemsReport.vue";
import DeadStockReport from "./components/pos/pos_report/DeadStockReport.vue";
import BranchInventoryReport from "./components/reports/inventory_reports/BranchInventoryReport.vue";
import ClosingStockReport from "./components/reports/inventory_reports/ClosingStockReport.vue";
import ModelLogDashboard from "./components/model_logs/ModelLogDashboard.vue";
import CustomerSalesReport from "./components/pos/pos_report/CustomerSalesReport.vue";
import VatReport from "./components/accounts/general_ledger/vat_report/VatReport.vue";
import ReverseSales from "./components/pos_retail/credit_pos/ReverseSales.vue";
import QbReconciliationDashboard from "./components/accounts/accounts_reconcialiation_qb/QbReconciliationDashboard.vue";
import QBReconsReport from "./components/accounts/accounts_reconcialiation_qb/QBReconsReport.vue";
import PurchaseAgeingReport from "./components/store/purchase/PurchaseAgeingReport";
import InventoryLogs from "./components/inventory/InventoryLogs.vue";
import LostTransferReport from "./components/store/branch_transfer/LostTransferReport.vue";
Vue.use(VueRouter);
//
const routes = [
    {
        path: "/lost_transfer_report",
        name: "lost_transfer_report",
        component: LostTransferReport,
        props: true,
    },
    {
        path: "/inventory_logs",
        name: "inventory_logs",
        component: InventoryLogs,
        props: true,
    },
    {
        path: "/purchase_ageing_report",
        name: "purchase_ageing_report",
        component: PurchaseAgeingReport,
        props: true,
    },
    {
        path: "/reconcialiation",
        name: "reconcialiation",
        component: QbReconciliationDashboard,
        props: true,
    },
    {
        path: "/recons_report",
        name: "recons_report",
        component: QBReconsReport,
        props: true,
    },
    {
        path: "/reverse_sales",
        name: "reverse_sales",
        component: ReverseSales,
        props: true,
    },
    {
        path: "/vat_report",
        name: "vat_report",
        component: VatReport,
        props: true,
    },
    {
        path: "/customer_sales_report",
        name: "customer_sales_report",
        component: CustomerSalesReport,
        props: true,
    },
    {
        path: "/closing_stock",
        name: "closing_stock",
        component: ClosingStockReport,
        props: true,
    },
    {
        path: "/model_logs",
        name: "model_logs",
        component: ModelLogDashboard,
        props: true,
    },
    {
        path: "/all_branches_inventory_report",
        name: "all_branches_inventory_report",
        component: BranchInventoryReport,
        props: true,
    },
    {
        path: "/raw_invoice_itemized_report",
        name: "raw_invoice_itemized_report",
        component: InvoiceRawItemsReport,
        props: true,
    },
    {
        path: "/invoice_items_report",
        name: "invoice_items_report",
        component: InvoiceItemsGroupedReport,
        props: true,
    },
    {
        path: "/deadstock_report",
        name: "deadstock_report",
        component: DeadStockReport,
        props: true,
    },
    {
        path: "/invoice_profitloss",
        name: "invoice_profitloss",
        component: InvoiceProfitLoss,
        props: true,
    },
    {
        path: "/collapsible_allsale_report",
        name: "collapsible_allsale_report",
        component: AllSaleReportCollapsed,
        props: true,
    },
    {
        path: "/mpesa_cashout_report",
        name: "mpesa_cashout_report",
        component: MpesaReport,
        props: true,
    },
    {
        path: "/mpesa_drops_report",
        name: "mpesa_drops_report",
        component: MpesaDropsReport,
        props: true,
    },
    {
        path: "/mpesa_drops",
        name: "mpesa_drops",
        component: NewMpesaDrops,
        props: true,
    },
    {
        path: "/promos",
        name: "promos",
        component: PromosDashboard,
        props: true,
    },
    {
        path: "/manage_promos",
        name: "manage_promos",
        component: PromosReport,
        props: true,
    },
    {
        path: "/directretail_creditnote_report",
        name: "directretail_creditnote_report",
        component: DirectRetailCreditReport,
        props: true,
    },
    {
        path: "/directretail_creditnote",
        name: "directretail_creditnote",
        component: DirectRetailCreditNote,
        props: true,
    },
    {
        path: "/ready_preorders",
        name: "ready_preorders",
        component: CompletedPreorderReport,
        props: true,
    },
    {
        path: "/new_preorders",
        name: "new_preorders",
        component: PreorderReport,
        props: true,
    },
    {
        path: "/merged_stock_report",
        name: "merged_stock_report",
        component: MergedStockReport,
        props: true,
    },
    {
        path: "/preorders",
        name: "preorders",
        component: SelectCustomer,
        props: true,
    },
    {
        path: "/print_labels",
        name: "print_labels",
        component: PrintLabels,
        props: true,
    },
    {
        path: "/cash_drops_reports",
        name: "cash_drops_reports",
        component: CashDropReport,
        props: true,
    },
    {
        path: "/cash_drops",
        name: "cash_drops",
        component: CashDropDashboard,
        props: true,
    },
    {
        path: "/closeshift_z_reporting",
        name: "closeshift_z_reporting",
        component: CashierZReport,
        props: true,
    },
    {
        path: "/closeshift_z_report",
        name: "closeshift_z_report",
        component: CashierCloseShifZreport,
        props: true,
    },
    {
        path: "/daily_itemized_report",
        name: "daily_itemized_report",
        component: DailySalesItemsReport,
        props: true,
    },
    {
        path: "/non_vscu_etims_report",
        name: "non_vscu_etims_report",
        component: NonvscuEtimsSalesReport,
        props: true,
    },
    {
        path: "/vscu_etims_report",
        name: "vscu_etims_report",
        component: VscuAllsalesEtimsReport,
        props: true,
    },
    {
        path: "/shelf_allocation",
        name: "shelf_allocation",
        component: ShelfAllocationDashboard,
        props: true,
    },
    {
        path: "/admin_login",
        name: "admin_login",
        component: AdminLogin,
        props: true,
    },
    {
        path: "/inventory_report",
        name: "inventory_report",
        component: BranchInventoryComparisonReport,
        props: true,
    },

    {
        path: "/generate_p9",
        name: "generate_p9",
        component: GenerateP9,
        props: true,
    },
    {
        path: "/retails_refunds_report",
        name: "retails_refunds_report",
        component: RefundReport,
        props: true,
    },
    {
        path: "/retail_returns",
        name: "retail_returns",
        component: RetailsReturnsDashboard,
        props: true,
    },
    {
        path: "/receivable_branch_transfer_report",
        name: "receivable_branch_transfer_report",
        component: ReceivableStockReport,
        props: true,
    },
    {
        path: "/receive_transferred_stock",
        name: "receive_transferred_stock",
        component: ReceiveStockTransffered,
        props: true,
    },
    {
        path: "/branch_transfer_report",
        name: "branch_transfer_report",
        component: BranchStockTransferReport,
        props: true,
    },
    {
        path: "/branch_transfer",
        name: "branch_transfer",
        component: BranchTransferDashboard,
        props: true,
    },
    {
        path: "/archived_sales_report",
        name: "archived_sales_report",
        component: ArchivedSalesReport,
        props: true,
    },
    {
        path: "/add_diagnosis",
        name: "add_diagnosis",
        component: PatientsDashboard,
        props: true,
    },
    {
        path: "/grn_return_report",
        name: "grn_return_report",
        component: ReturnGRNReport,
        props: true,
    },
    {
        path: "/low_stock",
        name: "low_stock",
        component: LowStockDashboard,
        props: true,
    },
    {
        path: "/stock_take_variation",
        name: "stock_take_variation",
        component: InventoryStockTakeReport,
        props: true,
    },
    {
        path: "/bulk_stock_take",
        name: "bulk_stock_take",
        component: InventoryBulkStockTake,
        props: true,
    },
    {
        path: "/stock_movement_history",
        name: "stock_movement_history",
        component: StockMovementHistory,
        props: true,
    },
    {
        path: "/non_etims_sales_report",
        name: "non_etims_sales_report",
        component: NonEtimsReport,
        props: true,
    },
    {
        path: "/etims_sales_report",
        name: "etims_sales_report",
        component: EtimsAllSaleReport,
        props: true,
    },
    {
        path: "/etims_item_packaging_code",
        name: "etims_item_packaging_code",
        component: PackagingCodeDashboard,
        props: true,
    },
    {
        path: "/etims_country_origin",
        name: "etims_country_origin",
        component: CountryOriginCode,
        props: true,
    },
    {
        path: "/etims_item_type_code",
        name: "etims_item_type_code",
        component: ItemTypeCodeDashboard,
        props: true,
    },
    {
        path: "/etims_items_class_code",
        name: "etims_items_class_code",
        component: EtimsItemClassCodeDashboard,
        props: true,
    },
    {
        path: "/sale_summary_with_activity",
        name: "sale_summary_with_activity",
        component: SaleSummaryWithActitivityReport,
        props: true,
    },
    {
        path: "/card_payment_report",
        name: "card_payment_report",
        component: CardPaymentReport,
        props: true,
    },
    {
        path: "/card_terminal_acc",
        name: "card_terminal_acc",
        component: CardTerminals,
        props: true,
    },
    {
        path: "/shelf",
        name: "shelf",
        component: ShelfDashboard,
        props: true,
    },
    {
        path: "/etims_stock_management",
        name: "etims_stock_management",
        component: EtimsStockDashboard,
        props: true,
    },
    {
        path: "/grn_batch_report",
        name: "grn_batch_report",
        component: GRNBatchReport,
        props: true,
    },
    {
        path: "/main_nav",
        name: "main_nav",
        component: mainapp,
        props: true,
    },
    {
        path: "/direct_credit_notes_report",
        name: "direct_credit_notes_report",
        component: DirectCreditNoteReport,
        props: true,
    },
    {
        path: "/direct_credit_notes",
        name: "direct_credit_notes",
        component: DirectCreditNoteDashboard,
        props: true,
    },
    {
        path: "/disassembly_production",
        name: "disassembly_production",
        component: StockDisassemblyDashboard,
        props: true,
    },
    {
        path: "/stock_conversion_report",
        name: "stock_conversion_report",
        component: IssueConversionReport,
        props: true,
    },
    {
        path: "/stock_conversion",
        name: "stock_conversion",
        component: StockConversionDashboard,
        props: true,
    },
    {
        path: "/price_groups",
        name: "price_groups",
        component: PriceGroupDashboard,
        props: true,
    },
    {
        path: "/grn_reports_summary",
        name: "grn_reports_summary",
        component: GRNExecutiveReport,
        props: true,
    },
    {
        path: "/invoice_summary_sales",
        name: "invoice_summary_sales",
        component: InvoiceSummarySaleReport,
        props: true,
    },
    {
        path: "/user_branch_invoice_sales_report",
        name: "user_branch_invoice_sales_report",
        component: ExcecUserBranchSaleReport,
        props: true,
    },
    {
        path: "/exec_cummulative_product_sales_report",
        name: "exec_cummulative_product_sales_report",
        component: ExecutiveCummulativeProductSalesReport,
        props: true,
    },
    {
        path: "/exec_product_sales_report",
        name: "exec_product_sales_report",
        component: ExecutiveProductSalesReport,
        props: true,
    },
    {
        path: "/exec_invoice_sale_report",
        name: "exec_invoice_sale_report",
        component: ExcecutiveInvoiceReport,
        props: true,
    },
    {
        path: "/executive_report",
        name: "executive_report",
        component: ExecProfitLoss,
        props: true,
    },
    {
        path: "/new_quotation",
        name: "new_quotation",
        component: NewQuotation,
        props: true,
    },
    {
        path: "/quotation",
        name: "quotation",
        component: QuotationDashboard,
        props: true,
    },
    {
        path: "/payable_ageing_report",
        name: "payable_ageing_report",
        component: PayableAgeingReport,
        props: true,
    },
    {
        path: "/receivable_ageing_report",
        name: "receivable_ageing_report",
        component: ReceivableAgeingReport,
        props: true,
    },
    {
        path: "/employee_leave",
        name: "employee_leave",
        component: EmployeeLeave,
        props: true,
    },
    {
        path: "/profit_loss",
        name: "profit_loss",
        component: ProfitLoss,
        props: true,
    },
    {
        path: "/asset_maintenance_dashboard",
        name: "asset_maintenance_dashboard",
        component: AssetMaintenanceDashboard,
        props: true,
    },
    {
        path: "/revoked_assets",
        name: "revoked_assets",
        component: RevokeAllocation,
        props: true,
    },
    {
        path: "/asset_allocation",
        name: "asset_allocation",
        component: AssetAllocationDashbaord,
        props: true,
    },
    {
        path: "/balance_sheet",
        name: "balance_sheet",
        component: BalanceSheet,
        props: true,
    },
    {
        path: "/asset_category",
        name: "asset_category",
        component: AssetCategoryDashboard,
        props: true,
    },
    {
        path: "/asset_manager",
        name: "asset_manager",
        component: AssetManagerDashbaord,
        props: true,
    },
    {
        path: "/supplier_credit_note_report",
        name: "supplier_credit_note_report",
        component: SupplierCreditNoteReport,
        props: true,
    },
    {
        path: "/supplier_credit_notes",
        name: "supplier_credit_notes",
        component: CreditNoteInvoiceDash,
        props: true,
    },
    {
        path: "/expenses_payment_report",
        name: "expenses_payment_report",
        component: ExpensesPaymentReport,
        props: true,
    },
    {
        path: "/trial_balance",
        name: "trial_balance",
        component: TrialBalance,
        props: true,
    },
    {
        path: "/chart_accounts_dashboard",
        name: "chart_accounts_dashboard",
        component: ChartAccountsDashboard,
        props: true,
    },
    {
        path: "/accounts_statement",
        name: "accounts_statement",
        component: AccountStatementDashboard,
        props: true,
    },
    {
        path: "/create_accounts",
        name: "create_accounts",
        component: LedgerAccountsDashboard,
        props: true,
    },
    {
        path: "/create_ledgers",
        name: "create_ledgers",
        component: AddLedgerEntries,
        props: true,
    },

    {
        path: "/general_ledger",
        name: "general_ledger",
        component: LedgerDashboard,
        props: true,
    },
    {
        path: "/waste_management",
        name: "waste_management",
        component: WasteManagementDashboard,
        props: true,
    },
    {
        path: "/waste_report",
        name: "waste_report",
        component: WasteReport,
        props: true,
    },
    {
        path: "/supplier_invoice_payment_report",
        name: "supplier_invoice_payment_report",
        component: SupplierInvoicePaymentReport,
        props: true,
    },
    {
        path: "/profit_loss_summary",
        name: "profit_loss_summary",
        component: ProfitLossSummary,
        props: true,
    },
    {
        path: "/pos_credit_notes",
        name: "pos_credit_notes",
        component: POSCreditReport,
        props: true,
    },
    {
        path: "/credit_pos",
        name: "credit_pos",
        component: CreditNoteDashboard,
        props: true,
    },
    {
        path: "/pos_orders",
        name: "pos_orders",
        component: POSOrders,
        props: true,
    },
    {
        path: "/retail_pos",
        name: "retail_pos",
        component: RetailPOS,
        props: true,
    },
    {
        path: "/inventory_management",
        name: "inventory_management",
        component: InventoryDashboard,
        props: true,
    },
    {
        path: "/home_store_issue_report",
        name: "home_store_issue_report",
        component: HomeIssueReport,
        props: true,
    },
    {
        path: "/home_issue",
        name: "home_issue",
        component: HomeIssue,
        props: true,
    },
    {
        path: "/archived_grouped_items_reports",
        name: "archived_grouped_items_reports",
        component: ArchivedGroupedItems,
        props: true,
    },
    {
        path: "/archived_items_reports",
        name: "archived_items_reports",
        component: ArchivedSalesItems,
        props: true,
    },
    {
        path: "/petty_cash",
        name: "petty_cash",
        component: PettyCashDashboard,
        props: true,
    },
    {
        path: "/expenses",
        name: "expenses",
        component: ExpensesDashboard,
        props: true,
    },
    {
        path: "/store_req_report",
        name: "store_req_report",
        component: StoreReqReport,
        props: true,
    },
    {
        path: "/store_requisition_issue",
        name: "store_requisition_issue",
        component: StoreReqIssue,
        props: true,
    },
    {
        path: "/store_requisition",
        name: "store_requisition",
        component: StoreRequisitionDashboard,
        props: true,
    },
    {
        path: "/issue_drinks",
        name: "issue_drinks",
        component: DrinksIssueDept,
        props: true,
    },
    {
        path: "/issue_kitchen",
        name: "issue_kitchen",
        component: KitchenIssueDashboard,
        props: true,
    },
    {
        path: "/online_order",
        name: "online_order",
        component: OnlineOrders,
        props: true,
    },
    {
        path: "/checkout",
        name: "checkout",
        component: Checkout,
        props: true,
    },
    {
        path: "/takeaway_orders",
        name: "takeaway_orders",
        component: TakeawayOrders,
        props: true,
    },
    {
        path: "/create_takeaway_orders",
        name: "create_takeaway_orders",
        component: CreateTakeawayOrders,
        props: true,
    },
    {
        path: "/takeaway_dept_chooser",
        name: "takeaway_dept_chooser",
        component: TakeAway,
        props: true,
    },
    {
        path: "/main_store_issue_items_report",
        name: "main_store_issue_items_report",
        component: MainStoreIssueDetails,
        props: true,
    },
    {
        path: "/main_store_issue_report",
        name: "main_store_issue_report",
        component: MainStoreIssueReport,
        props: true,
    },
    {
        path: "/issue_store",
        name: "issue_store",
        component: StoreIssue,
        props: true,
    },
    {
        path: "/archived_reports",
        name: "archived_reports",
        component: ArchivedReport,
        props: true,
    },
    {
        path: "/online_ordering",
        name: "online_ordering",
        component: OrderHome,
        props: true,
    },
    {
        path: "/contacts_enquiries",
        name: "contacts_enquiries",
        component: EnquiriesReport,
        props: true,
    },
    {
        path: "/table_bookings",
        name: "table_bookings",
        component: TableBookings,
        props: true,
    },
    {
        path: "/home_page",
        name: "home_page",
        component: fronthome,
        props: true,
    },
    {
        path: "/trashed_products",
        name: "trashed_products",
        component: TrashedProducts,
        props: true,
    },
    {
        path: "/production_management",
        name: "production_management",
        component: StockProductionDashboard,
        props: true,
    },
    {
        path: "/login_external",
        name: "login_external",
        component: loginExternal,
        props: true,
    },
    {
        path: "/user_logins",
        name: "user_logins",
        component: UserLoginDashboard,
        props: true,
    },
    {
        path: "/system_reports",
        name: "system_reports",
        component: SystemReportsDashboard,
        props: true,
    },
    {
        path: "/visitor_company",
        name: "visitor_company",
        component: GuestCompanyDashboard,
        props: true,
    },
    {
        path: "/all_sales_reports",
        name: "all_sales_reports",
        component: AllSaleReports,
        props: true,
    },
    {
        path: "/user_sale_summary",
        name: "user_sale_summary",
        component: UserReportSummary,
        props: true,
    },
    {
        path: "/rs_category_report",
        name: "rs_category_report",
        component: RoomServiceCategoryReport,
        props: true,
    },
    {
        path: "/dine_category_report",
        name: "dine_category_report",
        component: DineCategoryReports,
        props: true,
    },
    {
        path: "/credit_note_reports",
        name: "credit_note_reports",
        component: CreditNotesReport,
        props: true,
    },
    {
        path: "/room_transfer_reports",
        name: "room_transfer_reports",
        component: RoomTransferReport,
        props: true,
    },
    {
        path: "/pos_settings",
        name: "pos_settings",
        component: VoidSettings,
        props: true,
    },
    {
        path: "/conference_booking_report",
        name: "conference_booking_report",
        component: ConferenceBookingReport,
        props: true,
    },
    {
        path: "/conference_booking",
        name: "conference_booking",
        component: BookConference,
        props: true,
    },
    {
        path: "/clear_waiter_rs_payments",
        name: "clear_waiter_rs_payments",
        component: ClearWaiterRSPayments,
        props: true,
    },
    {
        path: "/uncleared_room_service_sales",
        name: "uncleared_room_service_sales",
        component: UnclearedRoomServiceSales,
        props: true,
    },
    {
        path: "/room_bill_payments",
        name: "room_bill_payments",
        component: RoomBillPayments,
        props: true,
    },
    {
        path: "/clear_waiter_bills",
        name: "clear_waiter_bills",
        component: ClearWaitersBills,
        props: true,
    },
    {
        path: "/uncleared_waiter_sales",
        name: "uncleared_waiter_sales",
        component: UnclearedSalesBills,
        props: true,
    },
    {
        path: "/create_users",
        name: "create_users",
        component: CreateUsers,
        props: true,
    },
    {
        path: "/reservation_balances_report",
        name: "reservation_balances_report",
        component: ReceptionBalancesReport,
        props: true,
    },
    {
        path: "/reservation_balances",
        name: "reservation_balances",
        component: ReceptionBalanceDashboard,
        props: true,
    },
    {
        path: "/daily_accommodation_report",
        name: "daily_accommodation_report",
        component: DailyAccomadationReport,
        props: true,
    },
    {
        path: "/inhouse_guest_report",
        name: "inhouse_guest_report",
        component: InhouestGuestReport,
        props: true,
    },
    {
        path: "/department_stock_movt_logs",
        name: "department_stock_movt_logs",
        component: DepartmentStockMovtLogs,
        props: true,
    },
    {
        path: "/department_stock_movt_report",
        name: "department_stock_movt_report",
        component: DepartmentStockMovt,
        props: true,
    },
    {
        path: "/room_service_select_dept",
        name: "room_service_select_dept",
        component: RoomServiceDeptSelect,
        props: true,
    },
    {
        path: "/takeaway_select_dept",
        name: "takeaway_select_dept",
        component: TakeAwaySelectDept,
        props: true,
    },
    {
        path: "/stock_movement_logs",
        name: "stock_movement_logs",
        component: StockMovtLogs,
        props: true,
    },
    {
        path: "/stock_movement_report",
        name: "stock_movement_report",
        component: StockMovementReport,
        props: true,
    },
    {
        path: "/physical_update_report",
        name: "physical_update_report",
        component: PhysicalStockUpdateReport,
        props: true,
    },
    {
        path: "/stock_deduction_report",
        name: "stock_deduction_report",
        component: StockDeductionReport,
        props: true,
    },
    {
        path: "/update_stock_balance",
        name: "update_stock_balance",
        component: UpdateStock,
        props: true,
    },
    {
        path: "/direct_grn",
        name: "direct_grn",
        component: DirectGRNReport,
        props: true,
    },
    {
        path: "/internal_issue_detailed_report",
        name: "internal_issue_detailed_report",
        component: InternalIssueReportDetails,
        props: true,
    },
    {
        path: "/internal_issue_report",
        name: "internal_issue_report",
        component: InternalIssueReport,
        props: true,
    },
    {
        path: "/issue_stock",
        name: "issue_stock",
        component: IssueProduct,
        props: true,
    },
    {
        path: "/direct_order_delivery_report",
        name: "direct_order_delivery_report",
        component: DirectOrderDeliveryReport,
        props: true,
    },
    {
        path: "/login_pos",
        name: "login_pos",
        component: LoginPos,
        props: true,
    },
    {
        path: "/split_bills_report",
        name: "split_bills_report",
        component: SplitBillPOSReport,
        props: true,
    },
    {
        path: "/merged_bills_report",
        name: "merged_bills_report",
        component: MergedBillsReport,
        props: true,
    },
    {
        path: "/stocktake_report",
        name: "stocktake_report",
        component: StockTakeReport,
        props: true,
    },
    {
        path: "/split_products_report",
        name: "split_products_report",
        component: SplitProductReport,
        props: true,
    },
    {
        path: "/split_product",
        name: "split_product",
        component: SplitProduct,
        props: true,
    },
    {
        path: "/split_settings",
        name: "split_settings",
        component: StockSplitingSettings,
        props: true,
    },
    {
        path: "/direct_order_delivery",
        name: "direct_order_delivery",
        component: DirectOrderDeliveryDashboard,
        props: true,
    },
    {
        path: "/direct_purchase",
        name: "direct_purchase",
        component: DirectPurchaseDashboard,
        props: true,
    },
    {
        path: "/balances_report",
        name: "balances_report",
        component: CashierBalanceReport,
        props: true,
    },
    {
        path: "/cashier_balances_dashboard",
        name: "cashier_balances_dashboard",
        component: CashierBalanceDashboard,
        props: true,
    },
    {
        path: "/takeaway_cashier",
        name: "takeaway_cashier",
        component: TakeAwayOrders,
        props: true,
    },
    {
        path: "/room_service_cashier",
        name: "room_service_cashier",
        component: RoomServiceCashier,
        props: true,
    },
    {
        path: "/cashier_order",
        name: "cashier_order",
        component: CashierOrders,
        props: true,
    },
    {
        path: "/fetch_pending_checkout",
        name: "fetch_pending_checkout",
        component: PendingCheckoutsReport,
        props: true,
    },
    {
        path: "/salary_sale_deduction",
        name: "salary_sale_deduction",
        component: SalarySalesDeductionDashboard,
        props: true,
    },
    {
        path: "/employee_sale_salary_deduction_report",
        name: "employee_sale_salary_deduction_report",
        component: EmployeeSaleSalaryDeductions,
        props: true,
    },
    {
        path: "/take_away_items_report",
        name: "take_away_items_report",
        component: TakeAwayItemDetailsReport,
        props: true,
    },
    {
        path: "/take_away_sales_report",
        name: "take_away_sales_report",
        component: TakeAwaySaleReport,
        props: true,
    },
    {
        path: "/takeaway_bills",
        name: "takeaway_bills",
        component: TakeAwayBills,
        props: true,
    },
    {
        path: "/new_takeaway_orders",
        name: "new_takeaway_orders",
        component: CreateTakeAwayOrders,
        props: true,
    },
    {
        path: "/merge_room_service_bills",
        name: "merge_room_service_bills",
        component: MergeRoomServiceBills,
        props: true,
    },
    {
        path: "/voided_sale_report",
        name: "voided_sale_report",
        component: VoidSaleRecord,
        props: true,
    },
    {
        path: "/items_report",
        name: "items_report",
        component: AllSaleItemReport,
        props: true,
    },
    {
        path: "/accompaniments_items_report",
        name: "accompaniments_items_report",
        component: AccompanimentReports,
        props: true,
    },

    {
        path: "/room_service_sales_report",
        name: "room_service_sales_report",
        component: RoomServicesSalesReport,
        props: true,
    },
    {
        path: "/diner_details_report",
        name: "diner_details_report",
        component: DineProductDetailsReport,
        props: true,
    },
    {
        path: "/pos_reports",
        name: "pos_reports",
        component: POSReportDashboard,
        props: true,
    },
    {
        path: "/room_bills",
        name: "room_bills",
        component: RoomBills,
        props: true,
    },
    {
        path: "/room_service_order",
        name: "room_service_order",
        component: RoomServiceOrder,
        props: true,
    },
    {
        path: "/room_service",
        name: "room_service",
        component: RoomServiceSelect,
        props: true,
    },
    {
        path: "/maintain_room",
        name: "maintain_room",
        component: RoomMaintenance,
        props: true,
    },
    {
        path: "/housekeeping_report",
        name: "housekeeping_report",
        component: HouseKeepingReport,
        props: true,
    },
    {
        path: "/housekeeping_dashboard",
        name: "housekeeping_dashboard",
        component: HouseKeepingDashboard,
        props: true,
    },
    {
        path: "/room_checkout",
        name: "room_checkout",
        component: RoomCheckout,
        props: true,
    },
    {
        path: "/current_reservations_tabular",
        name: "current_reservations_tabular",
        component: CurrentReservationTabularReport,
        props: true,
    },
    {
        path: "/current_reservations_calendar",
        name: "current_reservations_calendar",
        component: CurrentRoomReservationsCalendar,
        props: true,
    },
    {
        path: "/guest_manager",
        name: "guest_manager",
        component: GuestDashboard,
        props: true,
    },
    {
        path: "/room_select",
        name: "room_select",
        component: RoomSelect,
        props: true,
    },
    {
        path: "/reservation",
        name: "reservation",
        component: RoomBooking,
        props: true,
    },
    {
        path: "/transfer_table",
        name: "transfer_table",
        component: TransferTable,
        props: true,
    },
    {
        path: "/merge_bill",
        name: "merge_bill",
        component: MergeBill,
        props: true,
    },
    {
        path: "/cashier_dashboard",
        name: "cashier_dashboard",
        component: CashierDashboard,
        props: true,
    },
    {
        path: "/stock_dropdown",
        name: "stock_dropdown",
        component: DropDown,
        props: true,
    },

    {
        path: "/split_bills",
        name: "split_bills",
        component: SplitBillsDashboard,
        props: true,
    },
    {
        path: "/diner_bills",
        name: "diner_bills",
        component: ViewBills,
        props: true,
    },
    {
        path: "/bills",
        name: "bills",
        component: BillsDashboard,
        props: true,
    },
    {
        path: "/waiter_logout",
        name: "waiter_logout",
        component: PosLogout,
        props: true,
    },
    {
        path: "/create_dineorder",
        name: "create_dineorder",
        component: CreateOrders,
        props: true,
    },
    {
        path: "/dine_in",
        name: "dine_in",
        component: DineIn,
        props: true,
    },
    {
        path: "/posmenu",
        name: "posmenu",
        component: posnav,
        props: true,
    },

    {
        path: "/tables",
        name: "tables",
        component: TablesDashboard,
        props: true,
    },
    {
        path: "/locations",
        name: "locations",
        component: LocationDashboard,
        props: true,
    },
    {
        path: "/room_rates",
        name: "room_rates",
        component: RoomRates,
        props: true,
    },
    {
        path: "/accomodation_package",
        name: "accomodation_package",
        component: RoomPackage,
        props: true,
    },
    {
        path: "/rooms",
        name: "rooms",
        component: RoomsDashboard,
        props: true,
    },
    {
        path: "/rooms_standard_dashboard",
        name: "rooms_standard_dashboard",
        component: RoomStandardDashboard,
        props: true,
    },
    {
        path: "/create_ingredients",
        name: "create_ingredients",
        component: AddIngredients,
        props: true,
    },
    {
        path: "/add_production_cost",
        name: "add_production_cost",
        component: AddProductionCost,
        props: true,
    },
    {
        path: "/floor2",
        name: "floor2",
        component: Floor2,
        props: true,
    },
    {
        path: "/summarised_payslips",
        name: "summarised_payslips",
        component: SummarisedPayslipReport,
        props: true,
    },
    {
        path: "/grouped_payslips",
        name: "grouped_payslips",
        component: PayslipGroupedReport,
        props: true,
    },
    {
        path: "/view_payslip",
        name: "view_payslip",
        component: ViewPayslip,
        props: true,
    },
    {
        path: "/generic_payslips",
        name: "generic_payslips",
        component: PayslipsGenericReport,
        props: true,
    },
    {
        path: "/generate_payroll",
        name: "generate_payroll",
        component: PayrollDashboard,
        props: true,
    },
    {
        path: "/reward",
        name: "reward",
        component: CommissionDashboard,
        props: true,
    },
    {
        path: "/advance",
        name: "advance",
        component: AdvanceDashboard,
        props: true,
    },
    {
        path: "/statutory",
        name: "statutory",
        component: StatutoryDashboard,
        props: true,
    },
    {
        path: "/deductions",
        name: "deductions",
        component: DeductionDashboard,
        props: true,
    },
    {
        path: "/allowance",
        name: "allowance",
        component: AllowanceDashboard,
        props: true,
    },
    {
        path: "/loans",
        name: "loans",
        component: LoanDashboard,
        props: true,
    },
    {
        path: "/casuals_timelogs",
        name: "casuals_timelogs",
        component: CasualTimeLogsDashboard,
        props: true,
    },
    {
        path: "/time_logs",
        name: "time_logs",
        component: TimeLogDashboard,
        props: true,
    },
    {
        path: "/payroll_product",
        name: "payroll_product",
        component: ProductDashBoard,
        props: true,
    },
    {
        path: "/payroll_production",
        name: "payroll_production",
        component: ProductionDashboard,
        props: true,
    },
    {
        path: "/employee_roles",
        name: "employee_roles",
        component: EmployeeTypeDashBoard,
        props: true,
    },
    {
        path: "/employee_manager",
        name: "employee_manager",
        component: EmployeeDashboard,
        props: true,
    },
    {
        path: "/credit_notes",
        name: "credit_notes",
        component: CreditDashboard,
        props: true,
    },
    {
        path: "/supplier_debts",
        name: "supplier_debts",
        component: SupplierDebts,
        props: true,
    },
    {
        path: "/supplier_payment_report",
        name: "supplier_payment_report",
        component: SupplierPaymentReports,
        props: true,
    },

    {
        path: "/supplier_payments",
        name: "supplier_payments",
        component: SupplierPaymentsDashboard,
        props: true,
    },
    {
        path: "/supplier_invoices",
        name: "supplier_invoices",
        component: SupplierInvoiceDashboard,
        props: true,
    },
    {
        path: "/new_supplier_invoice",
        name: "new_supplier_invoice",
        component: NewSupplierInvoice,
        props: true,
    },
    {
        path: "/customer_ledger",
        name: "customer_ledger",
        component: CustomerLedger,
        props: true,
    },
    {
        path: "/payment_report",
        name: "payment_report",
        component: PaymentReport,
        props: true,
    },
    {
        path: "/invoice_reports",
        name: "invoice_reports",
        component: InvoiceReports,
        props: true,
    },
    {
        path: "/transaction",
        name: "transaction",
        component: TransactionDashboard,
        props: true,
    },
    {
        path: "/add_payment",
        name: "add_payment",
        component: NewPayment,
        props: true,
    },
    {
        path: "/payments",
        name: "payments",
        component: PaymentDashboard,
        props: true,
    },
    {
        path: "/new_opening_balances",
        name: "new_opening_balances",
        component: DebtorsOpeningBalances,
        props: true,
    },
    {
        path: "/invoices",
        name: "invoices",
        component: InvoiceDashboard,
        props: true,
    },
    {
        path: "/orders_dashboard",
        name: "orders_dashboard",
        component: OrdersDashboard,
        props: true,
    },
    {
        path: "/accounts_receivable",
        name: "accounts_receivable",
        component: AccountsReceivableDashboard,
        props: true,
    },
    {
        path: "/floor",
        name: "floor",
        component: Floor,
        props: true,
    },
    {
        path: "/purchase_returns",
        name: "purchase_returns",
        component: PurchaseReturns,
        props: true,
    },
    {
        path: "/return_report",
        name: "return_report",
        component: ReturnsReport,
        props: true,
    },
    {
        path: "/stock_returns",
        name: "stock_returns",
        component: StockReturns,
        props: true,
    },
    {
        path: "/dispatch_report",
        name: "dispatch_report",
        component: DispatchReport,
        props: true,
    },
    {
        path: "/approve_requisition",
        name: "approve_requisition",
        component: ApproveRequistion,
        props: true,
    },
    {
        path: "/assembly_product_requisition_dashboard",
        name: "assembly_product_requisition_dashboard",
        component: AssemblyProductRequistionDashboard,
        props: true,
    },
    {
        path: "/single_product_requisition_dashboard",
        name: "single_product_requisition_dashboard",
        component: SingleProductRequisitionDashboard,
        props: true,
    },
    {
        path: "/requisition",
        name: "requisition",
        component: NewRequisition,
        props: true,
    },
    {
        path: "/confirm_orders",
        name: "confirm_orders",
        component: UnconfirmedOrdersDashboard,
        props: true,
    },
    {
        path: "/delivery_notes",
        name: "delivery_notes",
        component: DeliveryNotesDashboard,
        props: true,
    },
    {
        path: "/order_delivery",
        name: "order_delivery",
        component: OrderDeliveryDashboard,
        props: true,
    },
    {
        path: "/tax",
        name: "tax",
        component: Tax,
        props: true,
    },
    {
        path: "/po_management",
        name: "po_management",
        component: PurchaseDashboard,
        props: true,
    },
    {
        path: "/customers",
        name: "customers",
        component: CustomerDashboard,
        props: true,
    },
    {
        path: "/suppliers",
        name: "suppliers",
        component: SupplierDashboard,
        props: true,
    },
    {
        path: "/menu_product_dashboard",
        name: "menu_product_dashboard",
        component: MenuProductDashboard,
        props: true,
    },

    {
        path: "/create_unit",
        name: "create_unit",
        component: CreateUnit,
        props: true,
    },
    {
        path: "/create_product_category",
        name: "create_product_category",
        component: ProductCategoryDashboard,
        props: true,
    },
    {
        path: "/stock_management",
        name: "stock_management",
        component: StockDashboard,
        props: true,
    },

    {
        path: "/branch",
        name: "branch",
        component: BranchDashboard,
        props: true,
    },
    {
        path: "/location",
        name: "location",
        component: DepartmentDashboard,
        props: true,
    },
    {
        path: "/user_dashboard",
        name: "user_dashboard",
        component: UserDashboard,
        props: true,
    },
    {
        path: "/assign_roles",
        name: "assign_roles",
        component: AssignRoles,
        props: true,
    },
    {
        path: "/roles",
        name: "roles",
        component: CreateRoles,
        props: true,
    },
    {
        path: "/",

        component: home,
        props: true,
    },
    {
        path: "/home",
        name: "home",
        component: home,
        props: true,
    },
    {
        path: "/login",
        name: "login",
        component: login,
        props: true,
    },
    {
        path: "/logout",
        name: "logout",
        component: Logout,
        props: true,
    },
];

export default new Router({
    //base: "/inceptialsystems",
    // base: "/grange_park",
    // base: "/demo_erp",
    // base: "/devierp_demo",
    // base: "/pharmafourltd",
    // base: "/lifecrest",
    //  base: "/horizon",
    // base: "/practice_mode2",
    //base: "/garmatek",
    //base: "/imagefirst",
    //base: "/erp001",
    // base:"/royalbeauty",
    //base:"/ashmeg",
    //   base:"/mzito",
    // base:"/erp_demo",
   // base: "/elevetus",
   //base: "/direct_loop_fashion",
    mode: "history",
    routes,
});
