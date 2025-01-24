<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Afghanistan', 'country_code' => 'AF'],
            ['name' => 'Åland Islands', 'country_code' => 'AX'],
            ['name' => 'Albania', 'country_code' => 'AL'],
            ['name' => 'Algeria', 'country_code' => 'DZ'],
            ['name' => 'American Samoa', 'country_code' => 'AS'],
            ['name' => 'Andorra', 'country_code' => 'AD'],
            ['name' => 'Angola', 'country_code' => 'AO'],
            ['name' => 'Anguilla', 'country_code' => 'AI'],
            ['name' => 'Antarctica', 'country_code' => 'AQ'],
            ['name' => 'Antigua and Barbuda', 'country_code' => 'AG'],
            ['name' => 'Argentina', 'country_code' => 'AR'],
            ['name' => 'Armenia', 'country_code' => 'AM'],
            ['name' => 'Aruba', 'country_code' => 'AW'],
            ['name' => 'Australia', 'country_code' => 'AU'],
            ['name' => 'Austria', 'country_code' => 'AT'],
            ['name' => 'Azerbaijan', 'country_code' => 'AZ'],
            ['name' => 'Bahamas', 'country_code' => 'BS'],
            ['name' => 'Bahrain', 'country_code' => 'BH'],
            ['name' => 'Bangladesh', 'country_code' => 'BD'],
            ['name' => 'Barbados', 'country_code' => 'BB'],
            ['name' => 'Belarus', 'country_code' => 'BY'],
            ['name' => 'Belgium', 'country_code' => 'BE'],
            ['name' => 'Belize', 'country_code' => 'BZ'],
            ['name' => 'Benin', 'country_code' => 'BJ'],
            ['name' => 'Bermuda', 'country_code' => 'BM'],
            ['name' => 'Bhutan', 'country_code' => 'BT'],
            ['name' => 'Bolivia', 'country_code' => 'BO'],
            ['name' => 'Bosnia and Herzegovina', 'country_code' => 'BA'],
            ['name' => 'Botswana', 'country_code' => 'BW'],
            ['name' => 'Bouvet Island', 'country_code' => 'BV'],
            ['name' => 'Brazil', 'country_code' => 'BR'],
            ['name' => 'British Indian Ocean Territory', 'country_code' => 'IO'],
            ['name' => 'Brunei Darussalam', 'country_code' => 'BN'],
            ['name' => 'Bulgaria', 'country_code' => 'BG'],
            ['name' => 'Burkina Faso', 'country_code' => 'BF'],
            ['name' => 'Burundi', 'country_code' => 'BI'],
            ['name' => 'Cambodia', 'country_code' => 'KH'],
            ['name' => 'Cameroon', 'country_code' => 'CM'],
            ['name' => 'Canada', 'country_code' => 'CA'],
            ['name' => 'Cape Verde', 'country_code' => 'CV'],
            ['name' => 'Cayman Islands', 'country_code' => 'KY'],
            ['name' => 'Central African Republic', 'country_code' => 'CF'],
            ['name' => 'Chad', 'country_code' => 'TD'],
            ['name' => 'Chile', 'country_code' => 'CL'],
            ['name' => 'China', 'country_code' => 'CN'],
            ['name' => 'Christmas Island', 'country_code' => 'CX'],
            ['name' => 'Cocos (Keeling) Islands', 'country_code' => 'CC'],
            ['name' => 'Colombia', 'country_code' => 'CO'],
            ['name' => 'Comoros', 'country_code' => 'KM'],
            ['name' => 'Congo', 'country_code' => 'CG'],
            ['name' => 'Congo, The Democratic Republic of the', 'country_code' => 'CD'],
            ['name' => 'Cook Islands', 'country_code' => 'CK'],
            ['name' => 'Costa Rica', 'country_code' => 'CR'],
            ['name' => 'Cote D\'Ivoire', 'country_code' => 'CI'],
            ['name' => 'Croatia', 'country_code' => 'HR'],
            // Add the remaining countries here.
            // For example:
            ['name' => 'Cuba', 'country_code' => 'CU'],
            ['name' => 'Cyprus', 'country_code' => 'CY'],
            ['name' => 'Czech Republic', 'country_code' => 'CZ'],
            ['name' => 'Denmark', 'country_code' => 'DK'],
            // Continue with all the remaining countries...
        ];

        // Insert data into the countries table
        foreach ($countries as $country) {
            \DB::table('countries')->insert($country);
        }
    


       

    }
}
