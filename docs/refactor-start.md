# Refactor Start

## Muc tieu

Refactor project PHP hien tai thanh mini ERP cho cong ty gia cong, thiet ke quang cao, theo huong:

- khong rewrite toan bo ngay
- uu tien tan dung module cu
- refactor theo tung giai doan
- giu he thong dang chay on dinh trong qua trinh chuyen doi

## Bat dau bang viec don gian nhat

Buoc dau tien nen lam la "dong bang cau truc hien tai" truoc khi sua code nghiep vu.

Ly do:

- it rui ro nhat
- khong lam gay he thong hien tai
- tao nen backlog refactor ro rang
- tranh sua lan man vao nhieu module cung luc

## 3 viec dau tien can lam

### 1. Chot module nao giu, module nao refactor, module nao bo dan

Uu tien giu:

- `QL_CLIENT`
- `QLKHO`
- `QL_Production_CMD`
- `QLNS`
- `QLDXM`
- `QLPC`
- `QLQT`

Chi giu y tuong, kha nang phai viet lai nhieu:

- `QLDA`
- `QLPT`

Bo dan vai tro kien truc cu:

- `base`
- `API` kieu `if ($_POST)`
- login procedural hien tai

### 2. Dung mot lop "core moi" chay song song voi code cu

Khong sua tung module cu ngay. Tao mot khung moi toi thieu:

- `public/index.php`
- `app/Core`
- `app/Modules`
- `config`
- `storage`

Code cu tam thoi coi la `legacy`.

### 3. Va bao mat toi thieu truoc

Can uu tien sua som:

- login noi chuoi SQL
- session/check auth
- cac API nhan `$_POST` truc tiep
- upload/delete file khong kiem soat chat

## Thu tu uu tien thuc te

1. Tai lieu hoa cau truc hien tai
2. Tao khung thu muc moi
3. Tach auth + db config dung chung
4. Boc module `QL_CLIENT`
5. Boc module `QLKHO`
6. Noi `Order -> Production -> Inventory`

## Dinh nghia MVP mini ERP cho ICONVINA

Phien ban mini ERP dau tien chi can du 6 nhom chuc nang:

- Khach hang
- Bao gia
- Don hang
- Lenh san xuat
- Vat tu va kho
- Cong no co ban

Chua can lam ngay:

- ke toan day du
- mua hang day du
- dashboard phuc tap
- mobile app

## Nguyen tac refactor

- Moi thay doi moi phai co ly do nghiep vu ro rang
- Module nao dang chay duoc thi boc lai truoc, khong pha ngay
- Tach logic, giao dien, va truy van DB ra khoi nhau
- Giam file "tong hop tat ca trong 1 file"
- Uu tien chuan hoa ten nghiep vu truoc khi toi uu code

## Viec tiep theo nen lam ngay sau tai lieu nay

Buoc ky thuat don gian nhat tiep theo:

1. Tao cau truc thu muc moi cho `app`, `public`, `config`, `storage`
2. Tao `public/index.php` lam entrypoint moi
3. Tao `config/database.php` de chuan hoa ket noi DB
4. Giu code cu trong `admin/modules` va route tam sang legacy

Neu lam 4 buoc nay truoc, cac dot refactor sau se de kiem soat hon rat nhieu.
