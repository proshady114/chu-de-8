# Chủ đề 8: CRUD API kết hợp AI - WAMP64

Bộ file PHP đã chỉnh lại dựa trên bộ file `index.php`, `create.php`, `edit.php`, `delete.php`, `regenerate.php`, `ai_helper.php`, `db.php` do người dùng gửi. Có giao diện CRUD, API JSON và tính năng **AI tự động tóm tắt thực tế** (Gemini hoặc OpenAI tùy cấu hình), **không dùng tóm tắt giả**.

## 1. Cài đặt WAMP64

1. Giải nén thư mục thành `C:\wamp64\www\crud_ai_chude8\` (đừng tạo thư mục lồng nhau).
2. Khởi động WampServer, kiểm tra biểu tượng W màu xanh, Apache và MySQL đang chạy.
3. Mở `http://localhost/phpmyadmin/`, vào tab **Import** và nhập `database.sql`.
   - SQL mặc định tạo database **`quan_ly_tin_tuc`** và bảng **`posts`**.
   - Dùng `CREATE IF NOT EXISTS`, **không xóa dữ liệu cũ**.
   - Nếu đã có `quan_ly_tin_tuc.posts` với các cột `id`, `title`, `content`, `ai_summary` thì không cần import lại.
   - **Lưu ý:** Project cũ khác của bạn dùng database `crud_ai`, bảng `bai_viet`. Đó là CSDL khác, không tự ghép chung. Muốn sử dụng CSDL khác cần đổi code SQL tương ứng.
4. Mở `config.php`, nếu WAMP dùng MySQL port khác 3306, sửa `DB_PORT`; thay mật khẩu trong `DB_PASS` nếu có.

## 2. Chọn AI và cấu hình API key (KHÔNG chia sẻ key)

**Cách A - Gemini (mặc định, đúng các file bạn gửi):**

- Vào https://aistudio.google.com/api-keys tạo **key mới**. Key cũ trong tệp bạn gửi đã bị lộ, phải thu hồi.
- Trong `config.php`:

```php
define('AI_PROVIDER', 'gemini');
define('GEMINI_API_KEY', 'KEY_GEMINI_MOI_CUA_BAN');
define('GEMINI_MODEL', 'gemini-3.5-flash-lite');
```

**Cách B - OpenAI giống project cũ:**

- Vào https://platform.openai.com/api-keys tạo **OpenAI key mới** và kiểm tra hạn mức sử dụng / billing.
- Trong `config.php`:

```php
define('AI_PROVIDER', 'openai');
define('OPENAI_API_KEY', 'KEY_OPENAI_MOI_CUA_BAN');
define('OPENAI_MODEL', 'gpt-4o-mini');
```

API key Gemini và OpenAI **không dùng chung**. Key chỉ nằm trong `config.php` trên máy, **không chèn vào JavaScript và không commit lên GitHub**. `.gitignore` đã loại `config.php` khỏi các lần thêm file mới, nhưng **không tự xóa** file đã từng được Git theo dõi.

## 3. Sửa lỗi SSL `unable to get local issuer certificate` (cURL 60)

1. Tải CA bundle chuẩn tại https://curl.se/ca/cacert.pem
2. Đặt tệp vào `C:\wamp64\certs\cacert.pem` (tên file phải chính xác).
3. `ai_helper.php` tự sử dụng `CA_BUNDLE_PATH` trong `config.php` nếu file tồn tại. Tệp có thể nằm nơi khác, chỉ cần sửa `CA_BUNDLE_PATH`.
4. Nên cấu hình cả file `php.ini` mà Apache đang đọc:

```ini
curl.cainfo = "C:/wamp64/certs/cacert.pem"
openssl.cafile = "C:/wamp64/certs/cacert.pem"
```

5. Restart All Services trong WAMP64. Mở `test_api.php` để xem file `php.ini`, CA và gửi yêu cầu kiểm tra API. **Không tắt** `CURLOPT_SSL_VERIFYPEER` / `CURLOPT_SSL_VERIFYHOST`.
6. Nếu vẫn cURL 60 dù file CA đã được nhận, có thể do chứng chỉ từ phần mềm antivirus/proxy HTTPS. Cần xử lý CA gốc đáng tin cậy của mạng/thiết bị, không tắt bảo mật.

## 4. Chạy

- Giao diện: `http://localhost/crud_ai_chude8/index.php`
- Test API AI: `http://localhost/crud_ai_chude8/test_api.php` (bấm nút **Gửi thử**)
- JSON CRUD: `http://localhost/crud_ai_chude8/api/posts.php`

Trên trang chính, nhấn **Thêm bài viết** rồi nhập tiêu đề và nội dung. Khi lưu, PHP gửi văn bản tới API AI để tóm tắt; tóm tắt thành công sẽ lưu vào `posts.ai_summary`. Nếu AI lỗi, bài viết vẫn được lưu và trang hiển thị lý do. Nhấn **Tạo lại AI** ở bài đã lưu để thử lại, không cần xóa hoặc tạo thêm.

## 5. API CRUD JSON - thử nhanh bằng PowerShell Windows

### Xem danh sách (GET)

```powershell
Invoke-RestMethod -Uri 'http://localhost/crud_ai_chude8/api/posts.php' -Method Get
```

### Thêm bài viết và tóm tắt bằng AI (POST)

```powershell
$body = @{ title = 'Bài thử API'; content = 'Trí tuệ nhân tạo hỗ trợ con người học tập và làm việc hiệu quả.' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://localhost/crud_ai_chude8/api/posts.php' -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body))
```

### Sửa bài viết (PUT), ví dụ ID 1

```powershell
$body = @{ id = 1; title = 'Đã sửa'; content = 'Đây là nội dung được cập nhật.' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://localhost/crud_ai_chude8/api/posts.php' -Method Put -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body))
```

### Xóa bài viết (DELETE), ví dụ ID 1

```powershell
$body = @{ id = 1 } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://localhost/crud_ai_chude8/api/posts.php' -Method Delete -ContentType 'application/json' -Body $body
```

Chỉ thử DELETE với bài viết bạn thực sự muốn xóa. API JSON chỉ chấp nhận request từ localhost, chưa có xác thực người dùng; dự án phù hợp **học tập local** chứ **không an toàn để public**.

## 6. Nếu chưa tóm tắt được

- `cURL 60`: chứng chỉ SSL chưa được xác minh, xem bước 3.
- `HTTP 401/403`: key sai, hết quyền hoặc không thuộc dịch vụ đã chọn.
- `HTTP 404`: model không có sẵn cho project/key, sửa `GEMINI_MODEL` theo model được cấp quyền.
- `HTTP 429`: đã hết quota / tốc độ / billing; không tự sửa bằng cách retry liên tục.
- `HTTP 503`: máy chủ AI tạm quá tải, chương trình thử lại một lần. Không thể đảm bảo hết lỗi nếu nhà cung cấp vẫn lỗi.
- `HTTP 200` nhưng không có text: API không trả phần văn bản; đọc thông báo trên màn hình.

**Quan trọng:** Không thể xác nhận lời gọi API thật từ máy tạo project vì không sử dụng API key riêng của bạn và không có truy cập tới WAMP64 của bạn. Các file đã được kiểm tra cú pháp PHP trong môi trường tạo.
