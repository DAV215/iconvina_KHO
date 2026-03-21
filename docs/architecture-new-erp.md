# New ERP Architecture

## Kien truc

He thong moi duoc dung theo huong `modular monolith`:

- `app/Core`: infrastructure chung
- `app/Modules`: nghiep vu
- `config`: cau hinh
- `public`: entrypoint
- `database`: migration va schema
- `storage`: runtime

## Module nghiep vu goc

- `Auth`
- `Customer`
- `Quotation`
- `Order`
- `Production`
- `Inventory`
- `Accounting`

## Luong nghiep vu

`Customer -> Quotation -> Order -> Production -> Inventory -> Accounting`

## Nguyen tac ky thuat

- khong tron HTML, SQL, business logic vao cung file
- controller chi tiep nhan request va tra response
- service xu ly rule nghiep vu
- repository truy cap DB qua PDO prepared statements
- migration la nguon su that cho schema

## Tinh trang hien tai

Da co:

- entrypoint moi
- autoload PSR-4
- config app/database
- env template
- router toi thieu
- request/response wrapper
- database connection wrapper
- scaffold cho cac module ERP chinh
- migration runner
- schema ERP v1

## Viec tiep theo bat buoc

1. Tao seed admin dau tien
2. CRUD that cho `Customer`
3. CRUD that cho `Quotation`
4. Chuyen `Quotation -> SalesOrder`
5. Noi `SalesOrder -> ProductionOrder`
