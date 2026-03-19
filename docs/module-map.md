# Module Map

## Ban do module hien tai sang mini ERP tuong lai

| Module hien tai | Vai tro hien tai | Module ERP tuong lai | Huong xu ly |
| --- | --- | --- | --- |
| `QL_CLIENT` | Khach hang, don hang | `Customer`, `Quotation`, `SalesOrder` | Giu va refactor dan |
| `QLKHO` | Vat tu, component, nhap xuat, BOM | `Inventory`, `BOM`, `StockLedger` | Giu rat nhieu |
| `QL_Production_CMD` | Lenh san xuat, job con, tien do | `ProductionOrder`, `ProductionTask` | Giu va ket noi voi Order |
| `QLNS` | User, phong ban, quyen | `User`, `Role`, `Department`, `Employee` | Giu mot phan, chuan hoa lai |
| `QLDXM` | De xuat mua | `PurchaseRequest` | Giu nghiep vu, refactor code |
| `QLPC` | Phieu chi | `Expense`, `PaymentVoucher`, `Payable` | Giu nghiep vu, tach lai module |
| `QLPT` | Phieu thu | `Receipt`, `Receivable` | Co the viet lai nhieu |
| `QLQT` | Quy trinh xet duyet | `ApprovalWorkflow` | Giu y tuong, viet lai gon hon |
| `QLDA` | Du an | `Project` hoac `Job` | Giu y tuong, refactor manh |
| `DashBoard` | Thong ke, cong viec | `Dashboard` | Lam lai sau cung |
| `base` | Layout include | `Core Layout + Router` | Bo dan |
| `API` | API procedural | `Controllers + Services + API` | Bo dan |
| `login.php`, `userlogin.php` | Dang nhap | `Auth` | Can sua som |

## Luong nghiep vu ERP de xuat

### Ban hang va san xuat

`Customer` -> `Quotation` -> `SalesOrder` -> `Project/Job` -> `ProductionOrder` -> `Delivery` -> `Receivable`

### Mua hang va vat tu

`PurchaseRequest` -> `ApprovalWorkflow` -> `PaymentVoucher` -> `StockIn`

### Kho va san xuat

`Material` + `BOM` -> `StockOut` -> `ProductionProgress` -> `StockIn thanh pham/ban thanh pham`

## Uu tien refactor

### Uu tien 1

- `QL_CLIENT`
- `QLKHO`
- `QL_Production_CMD`

### Uu tien 2

- `QLDXM`
- `QLPC`
- `QLQT`

### Uu tien 3

- `QLNS`
- `QLDA`
- `QLPT`

## Ket luan

Neu can bat dau tu de nhat:

- dung sua nghiep vu ngay
- dung lai cau truc va module map truoc
- sau do moi tao khung `app/Core`, `app/Modules`, `public/index.php`
