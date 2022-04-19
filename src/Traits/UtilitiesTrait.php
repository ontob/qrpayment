<?php

namespace Ontob\QrPayment\Traits;

trait UtilitiesTrait
{
    public function isValidIBAN($iban)
    {
        if (empty($iban)) {
            return false;
        }

        $iban = strtolower($iban);
        $countries = [
            'al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16,
            'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28,
            'cz' => 24, 'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18,
            'fr' => 27, 'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18,
            'gt' => 28, 'hu' => 28, 'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27,
            'jo' => 30, 'kz' => 20, 'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21,
            'lt' => 20, 'lu' => 20, 'mk' => 19, 'mt' => 31, 'mr' => 27, 'mu' => 30,
            'mc' => 27, 'md' => 24, 'me' => 22, 'nl' => 18, 'no' => 15, 'pk' => 24,
            'ps' => 29, 'pl' => 28, 'pt' => 25, 'qa' => 29, 'ro' => 24, 'sm' => 27,
            'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19, 'es' => 24, 'se' => 24,
            'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23, 'gb' => 22, 'vg' => 24
        ];
        $chars = [
            'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16,
            'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23,
            'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29, 'u' => 30,
            'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35
        ];

        if (strlen($iban) != $countries[ substr($iban, 0, 2) ]) {
            return false;
        }

        $movedChar = substr($iban, 4) . substr($iban, 0, 4);
        $movedCharArray = str_split($movedChar);
        $newString = "";

        foreach ($movedCharArray as $k => $v) {
            if (!is_numeric($movedCharArray[$k])) {
                $movedCharArray[$k] = $chars[$movedCharArray[$k]];
            }
            $newString .= $movedCharArray[$k];
        }

        if (function_exists("bcmod")) {
            return bcmod($newString, '97') == 1;
        }

        // http://au2.php.net/manual/en/function.bcmod.php#38474
        $x = $newString;
        $y = "97";
        $take = 5;
        $mod = "";

        do {
            $a = (int)$mod . substr($x, 0, $take);
            $x = substr($x, $take);
            $mod = $a % $y;
        } while (strlen($x));

        return (int)$mod == 1;
    }

    public function isValidBIC($swiftbic)
    {
        $regexp = '/^[A-Z]{6,6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3,3}){0,1}$/i';
        return (bool) preg_match($regexp, $swiftbic);
    }

    public function isValidCurrency($currency_code)
    {
        return array_key_exists($currency_code, $this->getCurrencyCodes());
    }

    public function isValidCountryCode($country_code)
    {
        return array_key_exists($country_code, $this->getCountryCodes());
    }

    public function isValidPhone($phone)
    {
        $phone = str_replace('+', '00', $phone);
        return preg_match("/(\+|00)?\d{9,12}/", $phone) ? true : false;
    }

    public function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) ? true : false;
    }

    public function getCurrencyCodes()
    {
        return [
            "AED" => "United Arab Emirates dirham",
            "AFN" => "Afghan afghani",
            "ALL" => "Albanian lek",
            "AMD" => "Armenian dram",
            "ANG" => "Netherlands Antillean guilder",
            "AOA" => "Angolan kwanza",
            "ARS" => "Argentine peso",
            "AUD" => "Australian dollar",
            "AWG" => "Aruban florin",
            "AZN" => "Azerbaijani manat",
            "BAM" => "Bosnia and Herzegovina convertible mark",
            "BBD" => "Barbados dollar",
            "BDT" => "Bangladeshi taka",
            "BGN" => "Bulgarian lev",
            "BHD" => "Bahraini dinar",
            "BIF" => "Burundian franc",
            "BMD" => "Bermudian dollar",
            "BND" => "Brunei dollar",
            "BOB" => "Boliviano",
            "BRL" => "Brazilian real",
            "BSD" => "Bahamian dollar",
            "BTN" => "Bhutanese ngultrum",
            "BWP" => "Botswana pula",
            "BYN" => "New Belarusian ruble",
            "BYR" => "Belarusian ruble",
            "BZD" => "Belize dollar",
            "CAD" => "Canadian dollar",
            "CDF" => "Congolese franc",
            "CHF" => "Swiss franc",
            "CLF" => "Unidad de Fomento",
            "CLP" => "Chilean peso",
            "CNY" => "Renminbi|Chinese yuan",
            "COP" => "Colombian peso",
            "CRC" => "Costa Rican colon",
            "CUC" => "Cuban convertible peso",
            "CUP" => "Cuban peso",
            "CVE" => "Cape Verde escudo",
            "CZK" => "Czech koruna",
            "DJF" => "Djiboutian franc",
            "DKK" => "Danish krone",
            "DOP" => "Dominican peso",
            "DZD" => "Algerian dinar",
            "EGP" => "Egyptian pound",
            "ERN" => "Eritrean nakfa",
            "ETB" => "Ethiopian birr",
            "EUR" => "Euro",
            "FJD" => "Fiji dollar",
            "FKP" => "Falkland Islands pound",
            "GBP" => "Pound sterling",
            "GEL" => "Georgian lari",
            "GHS" => "Ghanaian cedi",
            "GIP" => "Gibraltar pound",
            "GMD" => "Gambian dalasi",
            "GNF" => "Guinean franc",
            "GTQ" => "Guatemalan quetzal",
            "GYD" => "Guyanese dollar",
            "HKD" => "Hong Kong dollar",
            "HNL" => "Honduran lempira",
            "HRK" => "Croatian kuna",
            "HTG" => "Haitian gourde",
            "HUF" => "Hungarian forint",
            "IDR" => "Indonesian rupiah",
            "ILS" => "Israeli new shekel",
            "INR" => "Indian rupee",
            "IQD" => "Iraqi dinar",
            "IRR" => "Iranian rial",
            "ISK" => "Icelandic króna",
            "JMD" => "Jamaican dollar",
            "JOD" => "Jordanian dinar",
            "JPY" => "Japanese yen",
            "KES" => "Kenyan shilling",
            "KGS" => "Kyrgyzstani som",
            "KHR" => "Cambodian riel",
            "KMF" => "Comoro franc",
            "KPW" => "North Korean won",
            "KRW" => "South Korean won",
            "KWD" => "Kuwaiti dinar",
            "KYD" => "Cayman Islands dollar",
            "KZT" => "Kazakhstani tenge",
            "LAK" => "Lao kip",
            "LBP" => "Lebanese pound",
            "LKR" => "Sri Lankan rupee",
            "LRD" => "Liberian dollar",
            "LSL" => "Lesotho loti",
            "LYD" => "Libyan dinar",
            "MAD" => "Moroccan dirham",
            "MDL" => "Moldovan leu",
            "MGA" => "Malagasy ariary",
            "MKD" => "Macedonian denar",
            "MMK" => "Myanmar kyat",
            "MNT" => "Mongolian tögrög",
            "MOP" => "Macanese pataca",
            "MRO" => "Mauritanian ouguiya",
            "MUR" => "Mauritian rupee",
            "MVR" => "Maldivian rufiyaa",
            "MWK" => "Malawian kwacha",
            "MXN" => "Mexican peso",
            "MXV" => "Mexican Unidad de Inversion",
            "MYR" => "Malaysian ringgit",
            "MZN" => "Mozambican metical",
            "NAD" => "Namibian dollar",
            "NGN" => "Nigerian naira",
            "NIO" => "Nicaraguan córdoba",
            "NOK" => "Norwegian krone",
            "NPR" => "Nepalese rupee",
            "NZD" => "New Zealand dollar",
            "OMR" => "Omani rial",
            "PAB" => "Panamanian balboa",
            "PEN" => "Peruvian Sol",
            "PGK" => "Papua New Guinean kina",
            "PHP" => "Philippine peso",
            "PKR" => "Pakistani rupee",
            "PLN" => "Polish złoty",
            "PYG" => "Paraguayan guaraní",
            "QAR" => "Qatari riyal",
            "RON" => "Romanian leu",
            "RSD" => "Serbian dinar",
            "RUB" => "Russian ruble",
            "RWF" => "Rwandan franc",
            "SAR" => "Saudi riyal",
            "SBD" => "Solomon Islands dollar",
            "SCR" => "Seychelles rupee",
            "SDG" => "Sudanese pound",
            "SEK" => "Swedish krona",
            "SGD" => "Singapore dollar",
            "SHP" => "Saint Helena pound",
            "SLL" => "Sierra Leonean leone",
            "SOS" => "Somali shilling",
            "SRD" => "Surinamese dollar",
            "SSP" => "South Sudanese pound",
            "STD" => "São Tomé and Príncipe dobra",
            "SVC" => "Salvadoran colón",
            "SYP" => "Syrian pound",
            "SZL" => "Swazi lilangeni",
            "THB" => "Thai baht",
            "TJS" => "Tajikistani somoni",
            "TMT" => "Turkmenistani manat",
            "TND" => "Tunisian dinar",
            "TOP" => "Tongan paʻanga",
            "TRY" => "Turkish lira",
            "TTD" => "Trinidad and Tobago dollar",
            "TWD" => "New Taiwan dollar",
            "TZS" => "Tanzanian shilling",
            "UAH" => "Ukrainian hryvnia",
            "UGX" => "Ugandan shilling",
            "USD" => "United States dollar",
            "UYI" => "Uruguay Peso en Unidades Indexadas",
            "UYU" => "Uruguayan peso",
            "UZS" => "Uzbekistan som",
            "VEF" => "Venezuelan bolívar",
            "VND" => "Vietnamese đồng",
            "VUV" => "Vanuatu vatu",
            "WST" => "Samoan tala",
            "XAF" => "Central African CFA franc",
            "XCD" => "East Caribbean dollar",
            "XOF" => "West African CFA franc",
            "XPF" => "CFP franc",
            "XXX" => "No currency",
            "YER" => "Yemeni rial",
            "ZAR" => "South African rand",
            "ZMW" => "Zambian kwacha",
            "ZWL" => "Zimbabwean dollar"
        ];
    }

    public function getCountryCodes()
    {
        return [
            'ABW'=>'Aruba',
            'AFG'=>'Afghanistan',
            'AGO'=>'Angola',
            'AIA'=>'Anguilla',
            'ALA'=>'Åland Islands',
            'ALB'=>'Albania',
            'AND'=>'Andorra',
            'ARE'=>'United Arab Emirates',
            'ARG'=>'Argentina',
            'ARM'=>'Armenia',
            'ASM'=>'American Samoa',
            'ATA'=>'Antarctica',
            'ATF'=>'French Southern Territories',
            'ATG'=>'Antigua and Barbuda',
            'AUS'=>'Australia',
            'AUT'=>'Austria',
            'AZE'=>'Azerbaijan',
            'BDI'=>'Burundi',
            'BEL'=>'Belgium',
            'BEN'=>'Benin',
            'BES'=>'Bonaire, Sint Eustatius and Saba',
            'BFA'=>'Burkina Faso',
            'BGD'=>'Bangladesh',
            'BGR'=>'Bulgaria',
            'BHR'=>'Bahrain',
            'BHS'=>'Bahamas',
            'BIH'=>'Bosnia and Herzegovina',
            'BLM'=>'Saint Barthélemy',
            'BLR'=>'Belarus',
            'BLZ'=>'Belize',
            'BMU'=>'Bermuda',
            'BOL'=>'Bolivia, Plurinational State of',
            'BRA'=>'Brazil',
            'BRB'=>'Barbados',
            'BRN'=>'Brunei Darussalam',
            'BTN'=>'Bhutan',
            'BVT'=>'Bouvet Island',
            'BWA'=>'Botswana',
            'CAF'=>'Central African Republic',
            'CAN'=>'Canada',
            'CCK'=>'Cocos (Keeling) Islands',
            'CHE'=>'Switzerland',
            'CHL'=>'Chile',
            'CHN'=>'China',
            'CIV'=>'Côte d\'Ivoire',
            'CMR'=>'Cameroon',
            'COD'=>'Congo, the Democratic Republic of the',
            'COG'=>'Congo',
            'COK'=>'Cook Islands',
            'COL'=>'Colombia',
            'COM'=>'Comoros',
            'CPV'=>'Cape Verde',
            'CRI'=>'Costa Rica',
            'CUB'=>'Cuba',
            'CUW'=>'Curaçao',
            'CXR'=>'Christmas Island',
            'CYM'=>'Cayman Islands',
            'CYP'=>'Cyprus',
            'CZE'=>'Czech Republic',
            'DEU'=>'Germany',
            'DJI'=>'Djibouti',
            'DMA'=>'Dominica',
            'DNK'=>'Denmark',
            'DOM'=>'Dominican Republic',
            'DZA'=>'Algeria',
            'ECU'=>'Ecuador',
            'EGY'=>'Egypt',
            'ERI'=>'Eritrea',
            'ESH'=>'Western Sahara',
            'ESP'=>'Spain',
            'EST'=>'Estonia',
            'ETH'=>'Ethiopia',
            'FIN'=>'Finland',
            'FJI'=>'Fiji',
            'FLK'=>'Falkland Islands (Malvinas)',
            'FRA'=>'France',
            'FRO'=>'Faroe Islands',
            'FSM'=>'Micronesia, Federated States of',
            'GAB'=>'Gabon',
            'GBR'=>'United Kingdom',
            'GEO'=>'Georgia',
            'GGY'=>'Guernsey',
            'GHA'=>'Ghana',
            'GIB'=>'Gibraltar',
            'GIN'=>'Guinea',
            'GLP'=>'Guadeloupe',
            'GMB'=>'Gambia',
            'GNB'=>'Guinea-Bissau',
            'GNQ'=>'Equatorial Guinea',
            'GRC'=>'Greece',
            'GRD'=>'Grenada',
            'GRL'=>'Greenland',
            'GTM'=>'Guatemala',
            'GUF'=>'French Guiana',
            'GUM'=>'Guam',
            'GUY'=>'Guyana',
            'HKG'=>'Hong Kong',
            'HMD'=>'Heard Island and McDonald Islands',
            'HND'=>'Honduras',
            'HRV'=>'Croatia',
            'HTI'=>'Haiti',
            'HUN'=>'Hungary',
            'IDN'=>'Indonesia',
            'IMN'=>'Isle of Man',
            'IND'=>'India',
            'IOT'=>'British Indian Ocean Territory',
            'IRL'=>'Ireland',
            'IRN'=>'Iran, Islamic Republic of',
            'IRQ'=>'Iraq',
            'ISL'=>'Iceland',
            'ISR'=>'Israel',
            'ITA'=>'Italy',
            'JAM'=>'Jamaica',
            'JEY'=>'Jersey',
            'JOR'=>'Jordan',
            'JPN'=>'Japan',
            'KAZ'=>'Kazakhstan',
            'KEN'=>'Kenya',
            'KGZ'=>'Kyrgyzstan',
            'KHM'=>'Cambodia',
            'KIR'=>'Kiribati',
            'KNA'=>'Saint Kitts and Nevis',
            'KOR'=>'Korea, Republic of',
            'KWT'=>'Kuwait',
            'LAO'=>'Lao People\'s Democratic Republic',
            'LBN'=>'Lebanon',
            'LBR'=>'Liberia',
            'LBY'=>'Libya',
            'LCA'=>'Saint Lucia',
            'LIE'=>'Liechtenstein',
            'LKA'=>'Sri Lanka',
            'LSO'=>'Lesotho',
            'LTU'=>'Lithuania',
            'LUX'=>'Luxembourg',
            'LVA'=>'Latvia',
            'MAC'=>'Macao',
            'MAF'=>'Saint Martin (French part)',
            'MAR'=>'Morocco',
            'MCO'=>'Monaco',
            'MDA'=>'Moldova, Republic of',
            'MDG'=>'Madagascar',
            'MDV'=>'Maldives',
            'MEX'=>'Mexico',
            'MHL'=>'Marshall Islands',
            'MKD'=>'Macedonia, the former Yugoslav Republic of',
            'MLI'=>'Mali',
            'MLT'=>'Malta',
            'MMR'=>'Myanmar',
            'MNE'=>'Montenegro',
            'MNG'=>'Mongolia',
            'MNP'=>'Northern Mariana Islands',
            'MOZ'=>'Mozambique',
            'MRT'=>'Mauritania',
            'MSR'=>'Montserrat',
            'MTQ'=>'Martinique',
            'MUS'=>'Mauritius',
            'MWI'=>'Malawi',
            'MYS'=>'Malaysia',
            'MYT'=>'Mayotte',
            'NAM'=>'Namibia',
            'NCL'=>'New Caledonia',
            'NER'=>'Niger',
            'NFK'=>'Norfolk Island',
            'NGA'=>'Nigeria',
            'NIC'=>'Nicaragua',
            'NIU'=>'Niue',
            'NLD'=>'Netherlands',
            'NOR'=>'Norway',
            'NPL'=>'Nepal',
            'NRU'=>'Nauru',
            'NZL'=>'New Zealand',
            'OMN'=>'Oman',
            'PAK'=>'Pakistan',
            'PAN'=>'Panama',
            'PCN'=>'Pitcairn',
            'PER'=>'Peru',
            'PHL'=>'Philippines',
            'PLW'=>'Palau',
            'PNG'=>'Papua New Guinea',
            'POL'=>'Poland',
            'PRI'=>'Puerto Rico',
            'PRK'=>'Korea, Democratic People\'s Republic of',
            'PRT'=>'Portugal',
            'PRY'=>'Paraguay',
            'PSE'=>'Palestinian Territory, Occupied',
            'PYF'=>'French Polynesia',
            'QAT'=>'Qatar',
            'REU'=>'Réunion',
            'ROU'=>'Romania',
            'RUS'=>'Russian Federation',
            'RWA'=>'Rwanda',
            'SAU'=>'Saudi Arabia',
            'SDN'=>'Sudan',
            'SEN'=>'Senegal',
            'SGP'=>'Singapore',
            'SGS'=>'South Georgia and the South Sandwich Islands',
            'SHN'=>'Saint Helena, Ascension and Tristan da Cunha',
            'SJM'=>'Svalbard and Jan Mayen',
            'SLB'=>'Solomon Islands',
            'SLE'=>'Sierra Leone',
            'SLV'=>'El Salvador',
            'SMR'=>'San Marino',
            'SOM'=>'Somalia',
            'SPM'=>'Saint Pierre and Miquelon',
            'SRB'=>'Serbia',
            'SSD'=>'South Sudan',
            'STP'=>'Sao Tome and Principe',
            'SUR'=>'Suriname',
            'SVK'=>'Slovakia',
            'SVN'=>'Slovenia',
            'SWE'=>'Sweden',
            'SWZ'=>'Swaziland',
            'SXM'=>'Sint Maarten (Dutch part)',
            'SYC'=>'Seychelles',
            'SYR'=>'Syrian Arab Republic',
            'TCA'=>'Turks and Caicos Islands',
            'TCD'=>'Chad',
            'TGO'=>'Togo',
            'THA'=>'Thailand',
            'TJK'=>'Tajikistan',
            'TKL'=>'Tokelau',
            'TKM'=>'Turkmenistan',
            'TLS'=>'Timor-Leste',
            'TON'=>'Tonga',
            'TTO'=>'Trinidad and Tobago',
            'TUN'=>'Tunisia',
            'TUR'=>'Turkey',
            'TUV'=>'Tuvalu',
            'TWN'=>'Taiwan, Province of China',
            'TZA'=>'Tanzania, United Republic of',
            'UGA'=>'Uganda',
            'UKR'=>'Ukraine',
            'UMI'=>'United States Minor Outlying Islands',
            'URY'=>'Uruguay',
            'USA'=>'United States',
            'UZB'=>'Uzbekistan',
            'VAT'=>'Holy See (Vatican City State)',
            'VCT'=>'Saint Vincent and the Grenadines',
            'VEN'=>'Venezuela, Bolivarian Republic of',
            'VGB'=>'Virgin Islands, British',
            'VIR'=>'Virgin Islands, U.S.',
            'VNM'=>'Viet Nam',
            'VUT'=>'Vanuatu',
            'WLF'=>'Wallis and Futuna',
            'WSM'=>'Samoa',
            'YEM'=>'Yemen',
            'ZAF'=>'South Africa',
            'ZMB'=>'Zambia',
            'ZWE'=>'Zimbabwe'
        ];
    }

    public function stripDiacritics($string)
    {
        $string = str_replace(
            [
                'ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ú', 'ů',
                'ó', 'ť', 'ď', 'ľ', 'ň', 'ŕ', 'â', 'ă', 'ä', 'ĺ', 'ć',
                'ç', 'ę', 'ë', 'î', 'ń', 'ô', 'ő', 'ö', 'ů', 'ű', 'ü',
                'Ě', 'Š', 'Č', 'Ř', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ů',
                'Ó', 'Ť', 'Ď', 'Ľ', 'Ň', 'Ä', 'Ć', 'Ë', 'Ö', 'Ü'
            ],
            [
                'e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'u', 'u',
                'o', 't', 'd', 'l', 'n', 'a', 'a', 'a', 'a', 'a', 'a',
                'c', 'e', 'e', 'i', 'n', 'o', 'o', 'o', 'u', 'u', 'u',
                'E', 'S', 'C', 'R', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U',
                'O', 'T', 'D', 'L', 'N', 'A', 'C', 'E', 'O', 'U'
            ],
            $string
        );

        return $string;
    }
}
