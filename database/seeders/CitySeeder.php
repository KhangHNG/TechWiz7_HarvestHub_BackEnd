<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->locations() as $cityName => $districts) {
            $city = City::query()->updateOrCreate(['name' => $cityName]);

            foreach ($districts as $districtName => $wards) {
                $district = District::query()->updateOrCreate([
                    'city_id' => $city->id,
                    'name' => $districtName,
                ]);

                foreach ($wards as $wardName) {
                    Ward::query()->updateOrCreate([
                        'district_id' => $district->id,
                        'name' => $wardName,
                    ]);
                }
            }
        }
    }

    /**
     * @return array<string, array<string, array<int, string>>>
     */
    private function locations(): array
    {
        return [
            'Hà Nội' => [
                'Quận Ba Đình' => ['Phường Phúc Xá', 'Phường Trúc Bạch', 'Phường Vĩnh Phúc', 'Phường Cống Vị', 'Phường Liễu Giai', 'Phường Điện Biên', 'Phường Đội Cấn', 'Phường Kim Mã', 'Phường Giảng Võ'],
                'Quận Hoàn Kiếm' => ['Phường Hàng Bạc', 'Phường Hàng Bồ', 'Phường Hàng Đào', 'Phường Hàng Gai', 'Phường Lý Thái Tổ', 'Phường Phan Chu Trinh', 'Phường Trần Hưng Đạo', 'Phường Tràng Tiền', 'Phường Cửa Đông'],
                'Quận Tây Hồ' => ['Phường Bưởi', 'Phường Thụy Khuê', 'Phường Yên Phụ', 'Phường Tứ Liên', 'Phường Nhật Tân', 'Phường Quảng An', 'Phường Xuân La', 'Phường Phú Thượng'],
                'Quận Long Biên' => ['Phường Bồ Đề', 'Phường Gia Thụy', 'Phường Ngọc Lâm', 'Phường Phúc Đồng', 'Phường Sài Đồng', 'Phường Thạch Bàn', 'Phường Việt Hưng', 'Phường Long Biên', 'Phường Cự Khối'],
                'Quận Cầu Giấy' => ['Phường Nghĩa Đô', 'Phường Nghĩa Tân', 'Phường Mai Dịch', 'Phường Dịch Vọng', 'Phường Dịch Vọng Hậu', 'Phường Quan Hoa', 'Phường Yên Hòa', 'Phường Trung Hòa'],
                'Quận Đống Đa' => ['Phường Cát Linh', 'Phường Văn Miếu', 'Phường Quốc Tử Giám', 'Phường Láng Thượng', 'Phường Ô Chợ Dừa', 'Phường Khâm Thiên', 'Phường Thổ Quan', 'Phường Nam Đồng', 'Phường Trung Liệt'],
                'Quận Hai Bà Trưng' => ['Phường Nguyễn Du', 'Phường Bạch Đằng', 'Phường Phạm Đình Hổ', 'Phường Lê Đại Hành', 'Phường Đồng Nhân', 'Phường Bách Khoa', 'Phường Đồng Tâm', 'Phường Vĩnh Tuy', 'Phường Minh Khai'],
                'Quận Hoàng Mai' => ['Phường Thanh Trì', 'Phường Vĩnh Hưng', 'Phường Định Công', 'Phường Mai Động', 'Phường Tương Mai', 'Phường Đại Kim', 'Phường Tân Mai', 'Phường Hoàng Văn Thụ', 'Phường Giáp Bát'],
                'Quận Thanh Xuân' => ['Phường Nhân Chính', 'Phường Thượng Đình', 'Phường Khương Trung', 'Phường Khương Mai', 'Phường Thanh Xuân Trung', 'Phường Phương Liệt', 'Phường Hạ Đình', 'Phường Kim Giang'],
                'Quận Hà Đông' => ['Phường Nguyễn Trãi', 'Phường Mộ Lao', 'Phường Văn Quán', 'Phường Vạn Phúc', 'Phường Yết Kiêu', 'Phường Quang Trung', 'Phường La Khê', 'Phường Phúc La', 'Phường Hà Cầu'],
                'Quận Bắc Từ Liêm' => ['Phường Cổ Nhuế 1', 'Phường Cổ Nhuế 2', 'Phường Đông Ngạc', 'Phường Đức Thắng', 'Phường Thụy Phương', 'Phường Liên Mạc', 'Phường Minh Khai', 'Phường Phú Diễn'],
                'Quận Nam Từ Liêm' => ['Phường Mỹ Đình 1', 'Phường Mỹ Đình 2', 'Phường Cầu Diễn', 'Phường Mễ Trì', 'Phường Phú Đô', 'Phường Đại Mỗ', 'Phường Trung Văn', 'Phường Tây Mỗ'],
                'Huyện Sóc Sơn' => ['Thị trấn Sóc Sơn', 'Xã Phù Linh', 'Xã Tiên Dược', 'Xã Mai Đình', 'Xã Quang Tiến', 'Xã Hiền Ninh', 'Xã Thanh Xuân', 'Xã Tân Minh'],
                'Huyện Đông Anh' => ['Thị trấn Đông Anh', 'Xã Xuân Nộn', 'Xã Thuỵ Lâm', 'Xã Bắc Hồng', 'Xã Nguyên Khê', 'Xã Vĩnh Ngọc', 'Xã Hải Bối', 'Xã Tàm Xá'],
                'Huyện Gia Lâm' => ['Thị trấn Trâu Quỳ', 'Xã Đa Tốn', 'Xã Kiêu Kỵ', 'Xã Bát Tràng', 'Xã Kim Sơn', 'Xã Dương Xá', 'Xã Phú Thị', 'Xã Cổ Bi'],
                'Huyện Thanh Trì' => ['Thị trấn Văn Điển', 'Xã Tân Triều', 'Xã Thanh Liệt', 'Xã Tả Thanh Oai', 'Xã Hữu Hòa', 'Xã Tam Hiệp', 'Xã Tứ Hiệp', 'Xã Ngũ Hiệp'],
                'Thị xã Sơn Tây' => ['Phường Lê Lợi', 'Phường Quang Trung', 'Phường Ngô Quyền', 'Phường Sơn Lộc', 'Phường Xuân Khanh', 'Xã Đường Lâm', 'Xã Xuân Sơn', 'Xã Trung Sơn Trầm'],
            ],
            'Hồ Chí Minh' => [
                'Quận 1' => ['Phường Bến Nghé', 'Phường Bến Thành', 'Phường Nguyễn Thái Bình', 'Phường Phạm Ngũ Lão', 'Phường Cầu Ông Lãnh', 'Phường Cô Giang', 'Phường Nguyễn Cư Trinh', 'Phường Cầu Kho', 'Phường Đa Kao', 'Phường Tân Định'],
                'Quận 3' => ['Phường Võ Thị Sáu', 'Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 9', 'Phường 11', 'Phường 12', 'Phường 14'],
                'Quận 4' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 6', 'Phường 8', 'Phường 9', 'Phường 13', 'Phường 15', 'Phường 18'],
                'Quận 5' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 7', 'Phường 8', 'Phường 9', 'Phường 12'],
                'Quận 6' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 7', 'Phường 8', 'Phường 9', 'Phường 12'],
                'Quận 7' => ['Phường Tân Thuận Đông', 'Phường Tân Thuận Tây', 'Phường Tân Kiểng', 'Phường Tân Hưng', 'Phường Bình Thuận', 'Phường Tân Quy', 'Phường Phú Thuận', 'Phường Tân Phú', 'Phường Phú Mỹ'],
                'Quận 8' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 7', 'Phường 8', 'Phường 14', 'Phường 16'],
                'Quận 10' => ['Phường 1', 'Phường 2', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 8', 'Phường 9', 'Phường 12', 'Phường 13', 'Phường 15'],
                'Quận 11' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 5', 'Phường 7', 'Phường 8', 'Phường 10', 'Phường 11', 'Phường 14', 'Phường 16'],
                'Quận 12' => ['Phường Thạnh Xuân', 'Phường Thạnh Lộc', 'Phường Hiệp Thành', 'Phường Thới An', 'Phường Tân Chánh Hiệp', 'Phường An Phú Đông', 'Phường Tân Thới Hiệp', 'Phường Trung Mỹ Tây', 'Phường Tân Hưng Thuận', 'Phường Đông Hưng Thuận'],
                'Quận Bình Thạnh' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 5', 'Phường 6', 'Phường 11', 'Phường 12', 'Phường 13', 'Phường 14', 'Phường 19', 'Phường 21', 'Phường 25', 'Phường 26', 'Phường 27'],
                'Quận Gò Vấp' => ['Phường 1', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 7', 'Phường 8', 'Phường 10', 'Phường 12', 'Phường 14', 'Phường 15', 'Phường 16', 'Phường 17'],
                'Quận Phú Nhuận' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 7', 'Phường 8', 'Phường 9', 'Phường 10', 'Phường 11', 'Phường 13', 'Phường 15', 'Phường 17'],
                'Quận Tân Bình' => ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 6', 'Phường 7', 'Phường 8', 'Phường 9', 'Phường 10', 'Phường 11', 'Phường 12', 'Phường 13', 'Phường 14', 'Phường 15'],
                'Quận Tân Phú' => ['Phường Tân Sơn Nhì', 'Phường Tây Thạnh', 'Phường Sơn Kỳ', 'Phường Tân Quý', 'Phường Tân Thành', 'Phường Phú Thọ Hòa', 'Phường Phú Thạnh', 'Phường Phú Trung', 'Phường Hòa Thạnh', 'Phường Hiệp Tân', 'Phường Tân Thới Hòa'],
                'Quận Bình Tân' => ['Phường Bình Hưng Hòa', 'Phường Bình Hưng Hòa A', 'Phường Bình Hưng Hòa B', 'Phường Bình Trị Đông', 'Phường Bình Trị Đông A', 'Phường Bình Trị Đông B', 'Phường Tân Tạo', 'Phường Tân Tạo A', 'Phường An Lạc', 'Phường An Lạc A'],
                'Thành phố Thủ Đức' => ['Phường Linh Xuân', 'Phường Linh Trung', 'Phường Linh Chiểu', 'Phường Tam Bình', 'Phường Tam Phú', 'Phường Hiệp Bình Phước', 'Phường Hiệp Bình Chánh', 'Phường Linh Đông', 'Phường Bình Thọ', 'Phường Trường Thọ', 'Phường Hiệp Phú', 'Phường Tăng Nhơn Phú A', 'Phường Tăng Nhơn Phú B', 'Phường Long Thạnh Mỹ', 'Phường Long Bình', 'Phường Phước Long A', 'Phường Phước Long B'],
                'Huyện Củ Chi' => ['Thị trấn Củ Chi', 'Xã Phú Mỹ Hưng', 'Xã An Phú', 'Xã Trung Lập Thượng', 'Xã Phước Thạnh', 'Xã Tân Thạnh Đông', 'Xã Tân Thông Hội', 'Xã Thái Mỹ', 'Xã Tân Phú Trung', 'Xã Hòa Phú'],
                'Huyện Hóc Môn' => ['Thị trấn Hóc Môn', 'Xã Tân Hiệp', 'Xã Nhị Bình', 'Xã Đông Thạnh', 'Xã Tân Thới Nhì', 'Xã Thới Tam Thôn', 'Xã Xuân Thới Sơn', 'Xã Xuân Thới Đông', 'Xã Xuân Thới Thượng', 'Xã Bà Điểm'],
                'Huyện Bình Chánh' => ['Thị trấn Tân Túc', 'Xã Phạm Văn Hai', 'Xã Vĩnh Lộc A', 'Xã Vĩnh Lộc B', 'Xã Bình Lợi', 'Xã Lê Minh Xuân', 'Xã Tân Nhựt', 'Xã Tân Kiên', 'Xã An Phú Tây', 'Xã Hưng Long'],
                'Huyện Nhà Bè' => ['Thị trấn Nhà Bè', 'Xã Phước Kiển', 'Xã Phước Lộc', 'Xã Nhơn Đức', 'Xã Phú Xuân', 'Xã Long Thới', 'Xã Hiệp Phước'],
                'Huyện Cần Giờ' => ['Thị trấn Cần Thạnh', 'Xã Bình Khánh', 'Xã Tam Thôn Hiệp', 'Xã An Thới Đông', 'Xã Thạnh An', 'Xã Long Hòa', 'Xã Lý Nhơn'],
            ],
            'Đà Nẵng' => [
                'Quận Hải Châu' => ['Phường Thạch Thang', 'Phường Hải Châu 1', 'Phường Hải Châu 2', 'Phường Phước Ninh', 'Phường Hòa Thuận Tây', 'Phường Hòa Thuận Đông', 'Phường Nam Dương', 'Phường Bình Hiên', 'Phường Bình Thuận'],
                'Quận Thanh Khê' => ['Phường Tam Thuận', 'Phường Thanh Khê Tây', 'Phường Thanh Khê Đông', 'Phường Xuân Hà', 'Phường Tân Chính', 'Phường Chính Gián', 'Phường Vĩnh Trung', 'Phường Thạc Gián'],
                'Quận Sơn Trà' => ['Phường Thọ Quang', 'Phường Nại Hiên Đông', 'Phường Mân Thái', 'Phường An Hải Bắc', 'Phường Phước Mỹ', 'Phường An Hải Tây', 'Phường An Hải Đông'],
                'Quận Ngũ Hành Sơn' => ['Phường Mỹ An', 'Phường Khuê Mỹ', 'Phường Hoà Quý', 'Phường Hoà Hải'],
                'Quận Liên Chiểu' => ['Phường Hòa Hiệp Bắc', 'Phường Hòa Hiệp Nam', 'Phường Hòa Khánh Bắc', 'Phường Hòa Khánh Nam', 'Phường Hòa Minh'],
                'Quận Cẩm Lệ' => ['Phường Khuê Trung', 'Phường Hòa Phát', 'Phường Hòa An', 'Phường Hòa Thọ Tây', 'Phường Hòa Thọ Đông', 'Phường Hòa Xuân'],
                'Huyện Hòa Vang' => ['Xã Hòa Bắc', 'Xã Hòa Liên', 'Xã Hòa Ninh', 'Xã Hòa Sơn', 'Xã Hòa Nhơn', 'Xã Hòa Phú', 'Xã Hòa Phong', 'Xã Hòa Châu'],
            ],
            'Hải Phòng' => [
                'Quận Hồng Bàng' => ['Phường Quán Toan', 'Phường Hùng Vương', 'Phường Sở Dầu', 'Phường Thượng Lý', 'Phường Hạ Lý', 'Phường Minh Khai', 'Phường Trại Chuối', 'Phường Hoàng Văn Thụ'],
                'Quận Ngô Quyền' => ['Phường Máy Chai', 'Phường Máy Tơ', 'Phường Vạn Mỹ', 'Phường Cầu Tre', 'Phường Lạch Tray', 'Phường Đổng Quốc Bình', 'Phường Cầu Đất', 'Phường Lê Lợi'],
                'Quận Lê Chân' => ['Phường Cát Dài', 'Phường An Biên', 'Phường Lam Sơn', 'Phường An Dương', 'Phường Trần Nguyên Hãn', 'Phường Hồ Nam', 'Phường Trại Cau', 'Phường Dư Hàng'],
                'Quận Hải An' => ['Phường Đằng Giang', 'Phường Đằng Lâm', 'Phường Thành Tô', 'Phường Đằng Hải', 'Phường Nam Hải', 'Phường Cát Bi', 'Phường Tràng Cát'],
                'Quận Kiến An' => ['Phường Quán Trữ', 'Phường Lãm Hà', 'Phường Đồng Hòa', 'Phường Bắc Sơn', 'Phường Nam Sơn', 'Phường Ngọc Sơn', 'Phường Trần Thành Ngọ', 'Phường Văn Đẩu'],
                'Quận Đồ Sơn' => ['Phường Ngọc Xuyên', 'Phường Ngọc Hải', 'Phường Vạn Hương', 'Phường Vạn Sơn', 'Phường Minh Đức', 'Phường Bàng La', 'Phường Hợp Đức'],
                'Quận Dương Kinh' => ['Phường Hưng Đạo', 'Phường Anh Dũng', 'Phường Hải Thành', 'Phường Hòa Nghĩa', 'Phường Tân Thành', 'Phường Đa Phúc'],
                'Huyện Thủy Nguyên' => ['Thị trấn Núi Đèo', 'Thị trấn Minh Đức', 'Xã Lưu Kiếm', 'Xã Chính Mỹ', 'Xã Kênh Giang', 'Xã Hợp Thành', 'Xã Cao Nhân', 'Xã Mỹ Đồng'],
                'Huyện An Dương' => ['Thị trấn An Dương', 'Xã Lê Thiện', 'Xã Đại Bản', 'Xã An Hòa', 'Xã Hồng Phong', 'Xã Tân Tiến', 'Xã An Hưng', 'Xã An Hồng'],
                'Huyện An Lão' => ['Thị trấn An Lão', 'Xã Bát Trang', 'Xã Trường Thọ', 'Xã Trường Thành', 'Xã An Tiến', 'Xã Quang Hưng', 'Xã Quang Trung', 'Xã Quốc Tuấn'],
                'Huyện Kiến Thụy' => ['Thị trấn Núi Đối', 'Xã Đông Phương', 'Xã Thuận Thiên', 'Xã Hữu Bằng', 'Xã Đại Đồng', 'Xã Ngũ Phúc', 'Xã Kiến Quốc', 'Xã Thanh Sơn'],
                'Huyện Tiên Lãng' => ['Thị trấn Tiên Lãng', 'Xã Đại Thắng', 'Xã Tiên Cường', 'Xã Tự Cường', 'Xã Tiên Tiến', 'Xã Quyết Tiến', 'Xã Khởi Nghĩa', 'Xã Tiên Thanh'],
                'Huyện Vĩnh Bảo' => ['Thị trấn Vĩnh Bảo', 'Xã Vĩnh Long', 'Xã Giang Biên', 'Xã Vĩnh Phong', 'Xã Cộng Hiền', 'Xã Vĩnh Hòa', 'Xã Hùng Tiến', 'Xã Tân Hưng'],
                'Huyện Cát Hải' => ['Thị trấn Cát Bà', 'Thị trấn Cát Hải', 'Xã Nghĩa Lộ', 'Xã Đồng Bài', 'Xã Hoàng Châu', 'Xã Văn Phong', 'Xã Phù Long', 'Xã Gia Luận'],
            ],
            'Cần Thơ' => [
                'Quận Ninh Kiều' => ['Phường Cái Khế', 'Phường An Hòa', 'Phường Thới Bình', 'Phường An Nghiệp', 'Phường An Cư', 'Phường Tân An', 'Phường An Phú', 'Phường Xuân Khánh', 'Phường Hưng Lợi', 'Phường An Khánh'],
                'Quận Bình Thủy' => ['Phường Bình Thủy', 'Phường Trà An', 'Phường Trà Nóc', 'Phường Thới An Đông', 'Phường An Thới', 'Phường Bùi Hữu Nghĩa', 'Phường Long Hòa', 'Phường Long Tuyền'],
                'Quận Cái Răng' => ['Phường Lê Bình', 'Phường Hưng Phú', 'Phường Hưng Thạnh', 'Phường Ba Láng', 'Phường Thường Thạnh', 'Phường Phú Thứ', 'Phường Tân Phú'],
                'Quận Ô Môn' => ['Phường Châu Văn Liêm', 'Phường Thới Hòa', 'Phường Thới Long', 'Phường Long Hưng', 'Phường Thới An', 'Phường Phước Thới', 'Phường Trường Lạc'],
                'Quận Thốt Nốt' => ['Phường Thốt Nốt', 'Phường Thới Thuận', 'Phường Thuận An', 'Phường Tân Lộc', 'Phường Trung Nhứt', 'Phường Thạnh Hòa', 'Phường Trung Kiên', 'Phường Tân Hưng'],
                'Huyện Phong Điền' => ['Thị trấn Phong Điền', 'Xã Nhơn Ái', 'Xã Giai Xuân', 'Xã Tân Thới', 'Xã Trường Long', 'Xã Mỹ Khánh', 'Xã Nhơn Nghĩa'],
                'Huyện Cờ Đỏ' => ['Thị trấn Cờ Đỏ', 'Xã Trung An', 'Xã Trung Thạnh', 'Xã Thạnh Phú', 'Xã Thới Hưng', 'Xã Đông Hiệp', 'Xã Đông Thắng', 'Xã Thới Đông'],
                'Huyện Vĩnh Thạnh' => ['Thị trấn Thanh An', 'Thị trấn Vĩnh Thạnh', 'Xã Thạnh Mỹ', 'Xã Vĩnh Trinh', 'Xã Thạnh Quới', 'Xã Thạnh An', 'Xã Thạnh Tiến', 'Xã Vĩnh Bình'],
                'Huyện Thới Lai' => ['Thị trấn Thới Lai', 'Xã Thới Thạnh', 'Xã Tân Thạnh', 'Xã Xuân Thắng', 'Xã Đông Bình', 'Xã Đông Thuận', 'Xã Thới Tân', 'Xã Trường Thắng'],
            ],
        ];
    }
}
