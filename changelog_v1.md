# Nhật ký thay đổi - Schema Pro (Phiên bản tùy chỉnh v1)

## [1.1.13] - 21/01/2026
### Tổng hợp các tính năng chính của Phiên bản 1

### 🚀 Tính năng mới & Cải tiến
- **Hệ thống đánh giá sao tùy chỉnh (Thay thế KK Star Ratings)**: 
    - Tích hợp hệ thống đánh giá sao trực tiếp, loại bỏ việc sử dụng plugin "KK Star Ratings".
    - Cho phép cấu hình màu sắc ngôi sao, kích thước và vị trí hiển thị (Đầu bài viết, Cuối bài viết, Cả hai hoặc sử dụng Shortcode thủ công).
    - Hỗ trợ đa dạng các loại bài viết (Bài viết, Sản phẩm, Trang).
- **Tự động tạo Schema FAQ (Câu hỏi thường gặp)**:
    - Giới thiệu khả năng tự động nhận diện FAQ từ nội dung bài viết.
    - Hỗ trợ nhiều định dạng: Cặp tiêu đề/đoạn văn, danh sách DL/DT/DD và định dạng Q/A in đậm.
    - Thêm bộ lọc `wp_schema_pro_auto_faq_questions` để hỗ trợ tích hợp FAQ được tạo bởi AI.
- **Các loại Schema mới**:
    - **FAQPage**: Hỗ trợ cho các trang FAQ chuyên dụng và FAQ tự động trích xuất.
    - **CollectionPage**: Tối ưu hóa cho các trang lưu trữ và trang danh mục.
    - **ItemList**: Cấu trúc danh sách nâng cao cho danh mục sản phẩm và kho lưu trữ.
- **Tự động hóa cho WooCommerce**:
    - Tự động tạo Schema Sản phẩm (Product) và Danh mục cho các cửa hàng WooCommerce.
- **Tương thích với Yoast SEO**:
    - Thêm tính năng tự động vô hiệu hóa các mã Schema trùng lặp do Yoast SEO tạo ra để cải thiện sức khỏe SEO.

### 🛠️ Khả năng cốt lõi
- **Hỗ trợ đoạn trích nổi bật (Rich Snippets)**: Bài viết (Article), Sách (Book), Khóa học (Course), Sự kiện (Event), Tuyển dụng (Job Posting), Doanh nghiệp địa phương (Local Business), Cá nhân (Person), Sản phẩm (Product), Công thức nấu ăn (Recipe), Đánh giá (Review), Dịch vụ (Service), Ứng dụng phần mềm (Software Application) và Đối tượng video (Video Object).
- **Tích hợp Sơ đồ tri thức (Knowledge Graph)**: Thiết lập đại diện trang web (Công ty/Cá nhân), Logo và các hồ sơ mạng xã hội.
- **Schema toàn trang**: 
    - Hỗ trợ danh sách Breadcrumb (Đường dẫn trang).
    - Kích hoạt hộp tìm kiếm Sitelinks Search Box.
    - Tích hợp các thành phần điều hướng trang (Site Navigation Element).
    - Schema chuyên dụng cho trang Giới thiệu (About) và Liên hệ (Contact).
- **Ánh xạ linh hoạt**: Liên kết các trường Schema với các trường tùy chỉnh (Custom Fields) của WordPress, ACF hoặc các giá trị cố định.

### 🔧 Cải tiến kỹ thuật
- **Hiệu suất**: Tối ưu hóa việc thực thi mã để hiển thị Schema nhanh hơn ở phía người dùng.
- **Kiểm soát vị trí**: Tùy chọn xuất mã JSON-LD trong thẻ `<head>` hoặc `<footer>`.
- **Giao diện quản trị**: Trang cài đặt hiện đại với các tab trực quan và trình hướng dẫn thiết lập (Setup Wizard).

---
*Bản nhật ký này tổng hợp các tính năng hợp nhất của plugin Schema Pro được tùy chỉnh cho ketsatphugiaan.vn.*
