<?php
if (!defined('ABSPATH'))
exit;  // if direct access

function rbfw_currency_object(){
    $currency_list = array(
        "AFA" => array("name" => "Afghan Afghani", "symbol" => "؋"),
        "ALL" => array("name" => "Albanian Lek", "symbol" => "Lek"),
        "DZD" => array("name" => "Algerian Dinar", "symbol" => "دج"),
        "AOA" => array("name" => "Angolan Kwanza", "symbol" => "Kz"),
        "ARS" => array("name" => "Argentine Peso", "symbol" => "$"),
        "AMD" => array("name" => "Armenian Dram", "symbol" => "֏"),
        "AWG" => array("name" => "Aruban Florin", "symbol" => "ƒ"),
        "AUD" => array("name" => "Australian Dollar", "symbol" => "$"),
        "AZN" => array("name" => "Azerbaijani Manat", "symbol" => "m"),
        "BSD" => array("name" => "Bahamian Dollar", "symbol" => "B$"),
        "BHD" => array("name" => "Bahraini Dinar", "symbol" => ".د.ب"),
        "BDT" => array("name" => "Bangladeshi Taka", "symbol" => "৳"),
        "BBD" => array("name" => "Barbadian Dollar", "symbol" => "Bds$"),
        "BYR" => array("name" => "Belarusian Ruble", "symbol" => "Br"),
        "BEF" => array("name" => "Belgian Franc", "symbol" => "fr"),
        "BZD" => array("name" => "Belize Dollar", "symbol" => "$"),
        "BMD" => array("name" => "Bermudan Dollar", "symbol" => "$"),
        "BTN" => array("name" => "Bhutanese Ngultrum", "symbol" => "Nu."),
        "BTC" => array("name" => "Bitcoin", "symbol" => "฿"),
        "BOB" => array("name" => "Bolivian Boliviano", "symbol" => "Bs."),
        "BAM" => array("name" => "Bosnia", "symbol" => "KM"),
        "BWP" => array("name" => "Botswanan Pula", "symbol" => "P"),
        "BRL" => array("name" => "Brazilian Real", "symbol" => "R$"),
        "GBP" => array("name" => "British Pound Sterling", "symbol" => "£"),
        "BND" => array("name" => "Brunei Dollar", "symbol" => "B$"),
        "BGN" => array("name" => "Bulgarian Lev", "symbol" => "Лв."),
        "BIF" => array("name" => "Burundian Franc", "symbol" => "FBu"),
        "KHR" => array("name" => "Cambodian Riel", "symbol" => "KHR"),
        "CAD" => array("name" => "Canadian Dollar", "symbol" => "$"),
        "CVE" => array("name" => "Cape Verdean Escudo", "symbol" => "$"),
        "KYD" => array("name" => "Cayman Islands Dollar", "symbol" => "$"),
        "XOF" => array("name" => "CFA Franc BCEAO", "symbol" => "CFA"),
        "XAF" => array("name" => "CFA Franc BEAC", "symbol" => "FCFA"),
        "XPF" => array("name" => "CFP Franc", "symbol" => "₣"),
        "CLP" => array("name" => "Chilean Peso", "symbol" => "$"),
        "CNY" => array("name" => "Chinese Yuan", "symbol" => "¥"),
        "COP" => array("name" => "Colombian Peso", "symbol" => "$"),
        "KMF" => array("name" => "Comorian Franc", "symbol" => "CF"),
        "CDF" => array("name" => "Congolese Franc", "symbol" => "FC"),
        "CRC" => array("name" => "Costa Rican ColÃ³n", "symbol" => "₡"),
        "HRK" => array("name" => "Croatian Kuna", "symbol" => "kn"),
        "CUC" => array("name" => "Cuban Convertible Peso", "symbol" => "$, CUC"),
        "CZK" => array("name" => "Czech Republic Koruna", "symbol" => "Kč"),
        "DKK" => array("name" => "Danish Krone", "symbol" => "Kr."),
        "DJF" => array("name" => "Djiboutian Franc", "symbol" => "Fdj"),
        "DOP" => array("name" => "Dominican Peso", "symbol" => "$"),
        "XCD" => array("name" => "East Caribbean Dollar", "symbol" => "$"),
        "EGP" => array("name" => "Egyptian Pound", "symbol" => "ج.م"),
        "ERN" => array("name" => "Eritrean Nakfa", "symbol" => "Nfk"),
        "EEK" => array("name" => "Estonian Kroon", "symbol" => "kr"),
        "ETB" => array("name" => "Ethiopian Birr", "symbol" => "Nkf"),
        "EUR" => array("name" => "Euro", "symbol" => "€"),
        "FKP" => array("name" => "Falkland Islands Pound", "symbol" => "£"),
        "FJD" => array("name" => "Fijian Dollar", "symbol" => "FJ$"),
        "GMD" => array("name" => "Gambian Dalasi", "symbol" => "D"),
        "GEL" => array("name" => "Georgian Lari", "symbol" => "ლ"),
        "DEM" => array("name" => "German Mark", "symbol" => "DM"),
        "GHS" => array("name" => "Ghanaian Cedi", "symbol" => "GH₵"),
        "GIP" => array("name" => "Gibraltar Pound", "symbol" => "£"),
        "GRD" => array("name" => "Greek Drachma", "symbol" => "₯, Δρχ, Δρ"),
        "GTQ" => array("name" => "Guatemalan Quetzal", "symbol" => "Q"),
        "GNF" => array("name" => "Guinean Franc", "symbol" => "FG"),
        "GYD" => array("name" => "Guyanaese Dollar", "symbol" => "$"),
        "HTG" => array("name" => "Haitian Gourde", "symbol" => "G"),
        "HNL" => array("name" => "Honduran Lempira", "symbol" => "L"),
        "HKD" => array("name" => "Hong Kong Dollar", "symbol" => "$"),
        "HUF" => array("name" => "Hungarian Forint", "symbol" => "Ft"),
        "ISK" => array("name" => "Icelandic KrÃ³na", "symbol" => "kr"),
        "INR" => array("name" => "Indian Rupee", "symbol" => "₹"),
        "IDR" => array("name" => "Indonesian Rupiah", "symbol" => "Rp"),
        "IRR" => array("name" => "Iranian Rial", "symbol" => "﷼"),
        "IQD" => array("name" => "Iraqi Dinar", "symbol" => "د.ع"),
        "ILS" => array("name" => "Israeli New Sheqel", "symbol" => "₪"),
        "ITL" => array("name" => "Italian Lira", "symbol" => "L,£"),
        "JMD" => array("name" => "Jamaican Dollar", "symbol" => "J$"),
        "JPY" => array("name" => "Japanese Yen", "symbol" => "¥"),
        "JOD" => array("name" => "Jordanian Dinar", "symbol" => "ا.د"),
        "KZT" => array("name" => "Kazakhstani Tenge", "symbol" => "лв"),
        "KES" => array("name" => "Kenyan Shilling", "symbol" => "KSh"),
        "KWD" => array("name" => "Kuwaiti Dinar", "symbol" => "ك.د"),
        "KGS" => array("name" => "Kyrgystani Som", "symbol" => "лв"),
        "LAK" => array("name" => "Laotian Kip", "symbol" => "₭"),
        "LVL" => array("name" => "Latvian Lats", "symbol" => "Ls"),
        "LBP" => array("name" => "Lebanese Pound", "symbol" => "£"),
        "LSL" => array("name" => "Lesotho Loti", "symbol" => "L"),
        "LRD" => array("name" => "Liberian Dollar", "symbol" => "$"),
        "LYD" => array("name" => "Libyan Dinar", "symbol" => "د.ل"),
        "LTL" => array("name" => "Lithuanian Litas", "symbol" => "Lt"),
        "MOP" => array("name" => "Macanese Pataca", "symbol" => "$"),
        "MKD" => array("name" => "Macedonian Denar", "symbol" => "ден"),
        "MGA" => array("name" => "Malagasy Ariary", "symbol" => "Ar"),
        "MWK" => array("name" => "Malawian Kwacha", "symbol" => "MK"),
        "MYR" => array("name" => "Malaysian Ringgit", "symbol" => "RM"),
        "MVR" => array("name" => "Maldivian Rufiyaa", "symbol" => "Rf"),
        "MRO" => array("name" => "Mauritanian Ouguiya", "symbol" => "MRU"),
        "MUR" => array("name" => "Mauritian Rupee", "symbol" => "₨"),
        "MXN" => array("name" => "Mexican Peso", "symbol" => "$"),
        "MDL" => array("name" => "Moldovan Leu", "symbol" => "L"),
        "MNT" => array("name" => "Mongolian Tugrik", "symbol" => "₮"),
        "MAD" => array("name" => "Moroccan Dirham", "symbol" => "MAD"),
        "MZM" => array("name" => "Mozambican Metical", "symbol" => "MT"),
        "MMK" => array("name" => "Myanmar Kyat", "symbol" => "K"),
        "NAD" => array("name" => "Namibian Dollar", "symbol" => "$"),
        "NPR" => array("name" => "Nepalese Rupee", "symbol" => "₨"),
        "ANG" => array("name" => "Netherlands Antillean Guilder", "symbol" => "ƒ"),
        "TWD" => array("name" => "New Taiwan Dollar", "symbol" => "$"),
        "NZD" => array("name" => "New Zealand Dollar", "symbol" => "$"),
        "NIO" => array("name" => "Nicaraguan CÃ³rdoba", "symbol" => "C$"),
        "NGN" => array("name" => "Nigerian Naira", "symbol" => "₦"),
        "KPW" => array("name" => "North Korean Won", "symbol" => "₩"),
        "NOK" => array("name" => "Norwegian Krone", "symbol" => "kr"),
        "OMR" => array("name" => "Omani Rial", "symbol" => ".ع.ر"),
        "PKR" => array("name" => "Pakistani Rupee", "symbol" => "₨"),
        "PAB" => array("name" => "Panamanian Balboa", "symbol" => "B/."),
        "PGK" => array("name" => "Papua New Guinean Kina", "symbol" => "K"),
        "PYG" => array("name" => "Paraguayan Guarani", "symbol" => "₲"),
        "PEN" => array("name" => "Peruvian Nuevo Sol", "symbol" => "S/."),
        "PHP" => array("name" => "Philippine Peso", "symbol" => "₱"),
        "PLN" => array("name" => "Polish Zloty", "symbol" => "zł"),
        "QAR" => array("name" => "Qatari Rial", "symbol" => "ق.ر"),
        "RON" => array("name" => "Romanian Leu", "symbol" => "lei"),
        "RUB" => array("name" => "Russian Ruble", "symbol" => "₽"),
        "RWF" => array("name" => "Rwandan Franc", "symbol" => "FRw"),
        "SVC" => array("name" => "Salvadoran ColÃ³n", "symbol" => "₡"),
        "WST" => array("name" => "Samoan Tala", "symbol" => "SAT"),
        "SAR" => array("name" => "Saudi Riyal", "symbol" => "﷼"),
        "RSD" => array("name" => "Serbian Dinar", "symbol" => "din"),
        "SCR" => array("name" => "Seychellois Rupee", "symbol" => "SRe"),
        "SLL" => array("name" => "Sierra Leonean Leone", "symbol" => "Le"),
        "SGD" => array("name" => "Singapore Dollar", "symbol" => "$"),
        "SKK" => array("name" => "Slovak Koruna", "symbol" => "Sk"),
        "SBD" => array("name" => "Solomon Islands Dollar", "symbol" => "Si$"),
        "SOS" => array("name" => "Somali Shilling", "symbol" => "Sh.so."),
        "ZAR" => array("name" => "South African Rand", "symbol" => "R"),
        "KRW" => array("name" => "South Korean Won", "symbol" => "₩"),
        "XDR" => array("name" => "Special Drawing Rights", "symbol" => "SDR"),
        "LKR" => array("name" => "Sri Lankan Rupee", "symbol" => "Rs"),
        "SHP" => array("name" => "St. Helena Pound", "symbol" => "£"),
        "SDG" => array("name" => "Sudanese Pound", "symbol" => ".س.ج"),
        "SRD" => array("name" => "Surinamese Dollar", "symbol" => "$"),
        "SZL" => array("name" => "Swazi Lilangeni", "symbol" => "E"),
        "SEK" => array("name" => "Swedish Krona", "symbol" => "kr"),
        "CHF" => array("name" => "Swiss Franc", "symbol" => "CHf"),
        "SYP" => array("name" => "Syrian Pound", "symbol" => "LS"),
        "STD" => array("name" => "São Tomé and Príncipe Dobra", "symbol" => "Db"),
        "TJS" => array("name" => "Tajikistani Somoni", "symbol" => "SM"),
        "TZS" => array("name" => "Tanzanian Shilling", "symbol" => "TSh"),
        "THB" => array("name" => "Thai Baht", "symbol" => "฿"),
        "TOP" => array("name" => "Tongan pa'anga", "symbol" => "$"),
        "TTD" => array("name" => "Trinidad & Tobago Dollar", "symbol" => "$"),
        "TND" => array("name" => "Tunisian Dinar", "symbol" => "ت.د"),
        "TRY" => array("name" => "Turkish Lira", "symbol" => "₺"),
        "TMT" => array("name" => "Turkmenistani Manat", "symbol" => "T"),
        "UGX" => array("name" => "Ugandan Shilling", "symbol" => "USh"),
        "UAH" => array("name" => "Ukrainian Hryvnia", "symbol" => "₴"),
        "AED" => array("name" => "United Arab Emirates Dirham", "symbol" => "إ.د"),
        "UYU" => array("name" => "Uruguayan Peso", "symbol" => "$"),
        "USD" => array("name" => "US Dollar", "symbol" => "$"),
        "UZS" => array("name" => "Uzbekistan Som", "symbol" => "лв"),
        "VUV" => array("name" => "Vanuatu Vatu", "symbol" => "VT"),
        "VEF" => array("name" => "Venezuelan BolÃvar", "symbol" => "Bs"),
        "VND" => array("name" => "Vietnamese Dong", "symbol" => "₫"),
        "YER" => array("name" => "Yemeni Rial", "symbol" => "﷼"),
        "ZMK" => array("name" => "Zambian Kwacha", "symbol" => "ZK")
    );

    return $currency_list;
}

function rbfw_mps_currency_list(){

    $currency_obj = rbfw_currency_object();
    $arr = [];
    foreach ($currency_obj as $key => $value) {
        $arr[$key] = $key;
    }
    return $arr;
}

function rbfw_mps_currency_symbol(){
    global $rbfw;
    $currency = $rbfw->get_option_trans('rbfw_mps_currency', 'rbfw_basic_payment_settings','USD');
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
    $currency_obj = rbfw_currency_object();
    $currency_symbol = '';
    foreach ($currency_obj as $key => $value) {
        if($key == $currency){
            $currency_symbol = $value['symbol'];
        }
    }

    if($rbfw_payment_system == 'wps' && class_exists( 'WooCommerce' )){
        return get_woocommerce_currency_symbol();
    }else{
        return $currency_symbol;
    }
}

function rbfw_mps_price($amount=0){
    global $rbfw;
    $currency_symbol = rbfw_mps_currency_symbol();
    $currency_position = $rbfw->get_option_trans('rbfw_mps_currency_position', 'rbfw_basic_payment_settings','left');
    $currency_decimal_number = $rbfw->get_option_trans('rbfw_mps_currency_decimal_number', 'rbfw_basic_payment_settings','2');
    $currency_thousand_seperator = $rbfw->get_option_trans('rbfw_mps_currency_thousand_seperator', 'rbfw_basic_payment_settings',',');
    $currency_decimal_seperator = $rbfw->get_option_trans('rbfw_mps_currency_decimal_seperator', 'rbfw_basic_payment_settings','.');
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
    $number = $amount;
    $amount = preg_replace('/\s+/', '', $amount); // remove white spaces
    $amount = preg_replace('/[^0-9.]+/', '', $amount); // filter number only
    $amount = number_format($amount, $currency_decimal_number, $currency_decimal_seperator, $currency_thousand_seperator);
    $price = '';
    switch ($currency_position) {
        case "left":
            $price = $currency_symbol.$amount;
            break;
        case "right":
            $price = $amount.$currency_symbol;
            break;
        case "left_space":
            $price = $currency_symbol.' '.$amount;
          break;
        case "right_space":
            $price = $amount.' '.$currency_symbol;
        break;          
        default:
            $price = $currency_symbol.$amount;
    }

    if($rbfw_payment_system == 'wps' && class_exists( 'WooCommerce' )){
        return wc_price($number);
    }else{
        return $price;
    }
    
}