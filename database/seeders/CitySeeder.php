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
        Ward::query()->delete();
        District::query()->delete();
        City::query()->delete();

        foreach ($this->locations() as $cityName => $districts) {
            $city = City::query()->create(['name' => $cityName]);

            foreach ($districts as $districtName => $wards) {
                $district = District::query()->create([
                    'city_id' => $city->id,
                    'name' => $districtName,
                ]);

                foreach ($wards as $wardName) {
                    Ward::query()->create([
                        'district_id' => $district->id,
                        'name' => $wardName,
                    ]);
                }
            }
        }
    }

    /**
     * English city, district, and ward names for Vietnam and India.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    private function locations(): array
    {
        return [
            'Ho Chi Minh City' => [
                'District 1' => ['Ben Nghe Ward', 'Ben Thanh Ward', 'Nguyen Thai Binh Ward', 'Da Kao Ward'],
                'District 3' => ['Vo Thi Sau Ward', 'Ward 1', 'Ward 2', 'Ward 4'],
                'District 6' => ['Ward 1', 'Ward 2', 'Ward 8', 'Ward 12'],
                'Binh Thanh District' => ['Ward 1', 'Ward 2', 'Ward 13', 'Ward 25'],
                'Thu Duc City' => ['Linh Xuan Ward', 'Linh Trung Ward', 'Hiep Phu Ward', 'Long Binh Ward'],
                'Cu Chi District' => ['Cu Chi Town', 'Tan Thanh Dong Commune', 'Hoa Phu Commune', 'Thai My Commune'],
            ],
            'Hanoi' => [
                'Ba Dinh District' => ['Dien Bien Ward', 'Doi Can Ward', 'Kim Ma Ward', 'Ngoc Ha Ward'],
                'Hoan Kiem District' => ['Hang Dao Ward', 'Hang Bac Ward', 'Ly Thai To Ward', 'Trang Tien Ward'],
                'Hai Ba Trung District' => ['Nguyen Du Ward', 'Bach Khoa Ward', 'Le Dai Hanh Ward', 'Minh Khai Ward'],
                'Cau Giay District' => ['Dich Vong Ward', 'Nghia Do Ward', 'Yen Hoa Ward', 'Trung Hoa Ward'],
                'Dong Anh District' => ['Dong Anh Town', 'Nguyen Khe Commune', 'Vinh Ngoc Commune', 'Hai Boi Commune'],
            ],
            'Da Nang' => [
                'Hai Chau District' => ['Hai Chau 1 Ward', 'Hai Chau 2 Ward', 'Thach Thang Ward', 'Binh Thuan Ward'],
                'Son Tra District' => ['Man Thai Ward', 'Tho Quang Ward', 'An Hai Bac Ward', 'Phuoc My Ward'],
                'Thanh Khe District' => ['Tam Thuan Ward', 'Tan Chinh Ward', 'Vinh Trung Ward', 'Xuan Ha Ward'],
                'Ngu Hanh Son District' => ['My An Ward', 'Khue My Ward', 'Hoa Quy Ward', 'Hoa Hai Ward'],
            ],
            'Hai Phong' => [
                'Ngo Quyen District' => ['Le Loi Ward', 'May Chai Ward', 'Lach Tray Ward', 'Cau Dat Ward'],
                'Le Chan District' => ['An Bien Ward', 'Cat Dai Ward', 'Ho Nam Ward', 'Tran Nguyen Han Ward'],
                'Hong Bang District' => ['Hoang Van Thu Ward', 'Minh Khai Ward', 'Quan Toan Ward', 'Hung Vuong Ward'],
                'Hai An District' => ['Cat Bi Ward', 'Dang Hai Ward', 'Dang Lam Ward', 'Nam Hai Ward'],
            ],
            'Can Tho' => [
                'Ninh Kieu District' => ['Cai Khe Ward', 'An Hoa Ward', 'Xuan Khanh Ward', 'Hung Loi Ward'],
                'Cai Rang District' => ['Hung Phu Ward', 'Le Binh Ward', 'Ba Lang Ward', 'Hung Thanh Ward'],
                'Binh Thuy District' => ['Binh Thuy Ward', 'Tra Noc Ward', 'Long Hoa Ward', 'An Thoi Ward'],
                'O Mon District' => ['Chau Van Liem Ward', 'Thoi Long Ward', 'Phuoc Thoi Ward', 'Long Hung Ward'],
            ],
            'Da Lat' => [
                'Da Lat Central' => ['Ward 1', 'Ward 2', 'Ward 3', 'Ward 4'],
                'Da Lat East' => ['Ward 7', 'Ward 8', 'Ward 9', 'Ward 11'],
                'Da Lat West' => ['Ward 5', 'Ward 6', 'Ward 10', 'Ward 12'],
                'Lac Duong District' => ['Lac Duong Town', 'Da Sar Commune', 'Da Nhim Commune', 'Lat Commune'],
            ],
            'Mumbai' => [
                'South Mumbai' => ['Fort Ward', 'Colaba Ward', 'Churchgate Ward', 'Marine Lines Ward'],
                'Mumbai City' => ['Dadar Ward', 'Worli Ward', 'Byculla Ward', 'Parel Ward'],
                'Western Suburbs' => ['Bandra Ward', 'Andheri Ward', 'Juhu Ward', 'Goregaon Ward'],
                'Eastern Suburbs' => ['Kurla Ward', 'Ghatkopar Ward', 'Chembur Ward', 'Mulund Ward'],
            ],
            'New Delhi' => [
                'Central Delhi' => ['Chandni Chowk Ward', 'Connaught Place Ward', 'Karol Bagh Ward', 'Paharganj Ward'],
                'New Delhi District' => ['Chanakyapuri Ward', 'Lodhi Colony Ward', 'Sarojini Nagar Ward', 'Khan Market Ward'],
                'South Delhi' => ['Hauz Khas Ward', 'Greater Kailash Ward', 'Saket Ward', 'Mehrauli Ward'],
                'North Delhi' => ['Civil Lines Ward', 'Model Town Ward', 'Rohini Ward', 'Pitampura Ward'],
            ],
            'Bengaluru' => [
                'Bengaluru Central' => ['Kalasipalya Ward', 'Shivajinagar Ward', 'Chickpet Ward', 'Gandhinagar Ward'],
                'Bengaluru South' => ['Jayanagar Ward', 'JP Nagar Ward', 'Banashankari Ward', 'Basavanagudi Ward'],
                'Bengaluru North' => ['Malleswaram Ward', 'Yeshwanthpur Ward', 'Hebbal Ward', 'RT Nagar Ward'],
                'Bengaluru East' => ['Indiranagar Ward', 'Whitefield Ward', 'KR Puram Ward', 'Marathahalli Ward'],
            ],
            'Chennai' => [
                'Chennai Central' => ['Koyambedu Ward', 'T Nagar Ward', 'Egmore Ward', 'Park Town Ward'],
                'Chennai North' => ['Tondiarpet Ward', 'Royapuram Ward', 'Washermanpet Ward', 'Korukkupet Ward'],
                'Chennai South' => ['Adyar Ward', 'Mylapore Ward', 'Guindy Ward', 'Velachery Ward'],
                'Chennai West' => ['Ambattur Ward', 'Anna Nagar Ward', 'Vadapalani Ward', 'Kodambakkam Ward'],
            ],
            'Kolkata' => [
                'Kolkata Central' => ['Lindsay Street Ward', 'Bowbazar Ward', 'Esplanade Ward', 'Park Street Ward'],
                'North Kolkata' => ['Shyambazar Ward', 'Bagbazar Ward', 'Sovabazar Ward', 'Hatibagan Ward'],
                'South Kolkata' => ['Ballygunge Ward', 'Gariahat Ward', 'Jadavpur Ward', 'Tollygunge Ward'],
                'East Kolkata' => ['Salt Lake Ward', 'New Town Ward', 'Beliaghata Ward', 'Phoolbagan Ward'],
            ],
            'Hyderabad' => [
                'Hyderabad Central' => ['Charminar Ward', 'Laad Bazaar Ward', 'Abids Ward', 'Sultan Bazaar Ward'],
                'Secunderabad' => ['Secunderabad Ward', 'Trimulgherry Ward', 'Maredpally Ward', 'Bowenpally Ward'],
                'Hyderabad West' => ['Banjara Hills Ward', 'Jubilee Hills Ward', 'Mehdipatnam Ward', 'Tolichowki Ward'],
                'Hyderabad East' => ['Uppal Ward', 'LB Nagar Ward', 'Dilsukhnagar Ward', 'Malakpet Ward'],
            ],
        ];
    }
}
