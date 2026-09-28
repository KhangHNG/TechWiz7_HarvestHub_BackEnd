<?php

namespace Tests\Unit;

use App\Support\ImageUrls;
use PHPUnit\Framework\TestCase;

class ImageUrlsTest extends TestCase
{
    public function test_wikimedia_card_thumb_keeps_the_same_file_and_uses_the_card_width(): void
    {
        $url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Beeswax.jpg/960px-Beeswax.jpg';

        $this->assertSame(
            'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Beeswax.jpg/500px-Beeswax.jpg',
            ImageUrls::resize($url, ImageUrls::CARD_WIDTH),
        );
    }

    public function test_wikimedia_tiff_uses_the_jpeg_rendition(): void
    {
        $url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Choy_sum_for_sale.tif/lossless-page1-960px-Choy_sum_for_sale.tif.png';

        $this->assertSame(
            'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Choy_sum_for_sale.tif/500px-Choy_sum_for_sale.tif.jpg',
            ImageUrls::resize($url, ImageUrls::CARD_WIDTH),
        );
    }

    public function test_wikimedia_original_stays_for_the_gallery_and_shrinks_for_a_card(): void
    {
        $url = 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Rice_Grains_.png';

        $this->assertSame($url, ImageUrls::resize($url, ImageUrls::GALLERY_WIDTH));
        $this->assertSame(
            'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Rice_Grains_.png/500px-Rice_Grains_.png',
            ImageUrls::resize($url, ImageUrls::CARD_WIDTH),
        );
    }

    public function test_cloudinary_url_asks_for_a_compressed_width(): void
    {
        $url = 'https://res.cloudinary.com/dn6rs5y6t/image/upload/v1790611501/products/vaemvqlrxlyd039xrhuk.jpg';

        $this->assertSame(
            'https://res.cloudinary.com/dn6rs5y6t/image/upload/f_auto,q_auto,w_500,c_limit/v1790611501/products/vaemvqlrxlyd039xrhuk.jpg',
            ImageUrls::resize($url, ImageUrls::CARD_WIDTH),
        );
    }

    public function test_cloudinary_transform_is_replaced_instead_of_stacked(): void
    {
        $url = 'https://res.cloudinary.com/dn6rs5y6t/image/upload/f_auto,q_auto,w_500,c_limit/v1790611501/products/vaemvqlrxlyd039xrhuk.jpg';

        $this->assertSame(
            'https://res.cloudinary.com/dn6rs5y6t/image/upload/f_auto,q_auto,w_960,c_limit/v1790611501/products/vaemvqlrxlyd039xrhuk.jpg',
            ImageUrls::resize($url, ImageUrls::GALLERY_WIDTH),
        );
    }

    public function test_other_addresses_stay_unchanged(): void
    {
        $url = 'https://cdn.example.com/products/lettuce.jpg';

        $this->assertSame($url, ImageUrls::resize($url, ImageUrls::CARD_WIDTH));
    }
}
