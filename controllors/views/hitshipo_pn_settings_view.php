<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_script("jquery");

//$this->init_settings(); 
global $woocommerce, $wp_roles;
$error = $success =  '';
$_carriers = array(
					//"Public carrier name" => "technical name",
					"7" => "DPD",
					"11" => "Posten Varubrev Ekonomi",
					"17" => "MyPack Home", 
					"18" => "Parcel",
					"19" => "MyPack Collect",
					"20" => "Return Pickup",
					"21" => "Företagspaket Ekonomi Förbet",
					"22" => "PostNord Return Pickup",
					"24" => "Return Drop Off",
					"25" => "Postpaket",
					"27" => "Postpaket Kontant",
					"28" => "SverigePaket",
					"30" => "MyPack Home Small (Parcel Letter)",
					"32" => "MyPack Home",
					"34" => "International tracked letter",
					"35" => "Företagspaket 09.00, (Förbet.)",
					"37" => "Tompallsdistribution",
					"38" => "Kartong med porto",
					"42" => "Express Next Day",
					"45" => "Brevpostförskott Inrikes",
					"47" => "EMS International Express",
					"48" => "InNight",
					"49" => "InNight Reverse",
					"51" => "Företagspaket Comeback",
					"52" => "Pallet",
					"53" => "PALL.ETT Special",
					"54" => "PALL.ETT+",
					"58" => "InNight Forwarding",
					"59" => "Retail Delivery",
					"69" => "InNight Systemtransporter",
					"75" => "Posten Varubrev 1:a klass",
					"78" => "Posten Varubrev Klimatek",
					"79" => "Posten Varubrev Ekonomi",
					"80" => "DPD MAX",
					"81" => "Lokal Åkeritjänst - Pall",
					"82" => "Lokal Åkeritjänst - Paket",
					"83" => "Groupage",
					"84" => "Road Freight Europe",
					"85" => "Part- /Full Loads",
					"86" => "Posten Varubrev 1:a klass",
					"87" => "Posten Varubrev Retur",
					"88" => "DPD",
					"91" => "International Parcel",
					"92" => "Import-Ekonomipaket",
					"93" => "eCIP Collect",
					"94" => "eCIP Home",
					"95" => "Postpaket Utrikes",
					"97" => "Parcel Post Collect",
					"98" => "Import-EPG",
					"AF" => "Brevpostförskott",
					"RR" => "Rek",
					"VV" => "Värde",
					"RP" => "Rek",
					"RL" => "RL",
					"RK" => "RK",
					"AJ" => "Skicka Hem",
					"LX" => "Expressbrev",
					"EE" => "EMS",
					"AP" => "Skicka Lätt",
					"ED" => "EMS",
					"EM" => "EMS",
					"VS" => "Värde skjutvapen RPS",
					"LY" => "Spårbart brev",
					"UX" => "Export Letter Sweden",
					"AK" => "Postal Distributed Newspaper",
	);
$print_size = array('standard' => 'STANDARD - 190x105mm', 'small' => 'SMALL - 75x105mm', 'ste' => 'STE - 190x105mm');
$pac_type = array("PC" => "PC - parcel", "PE" => "PE - pallet_eur", "AF" => "AF - pallet_half", "OA" => "OA - pallet_quarter", "OF" => "OF - pallet_special", "CW" => "CW - cage roll", "BX" => "BX - box", "EN" => "EN - envelope");
$tos = array("DDP" => "DDP - Delivery at Place (DAP Cleared)", "DAP" => "DAP - Delivery at Place (DAP)", "EXW" => "EXW - Ex Works", "DAT" => "DAT - Delivery at Terminal");
$tod_cc = array("DDP" => "DDP - Consignor", "EXW" => "EXW - Consignee");
$tod_ccl = array("2000" => "2000 - COMBITERMS", "1990" => "1990 - INCOTERMS");
$paper_size = array("A4" => "A4", "A5" => "A5", "A6" => "A6", "LETTER" => "LETTER", "LABEL" => "LABEL");

$countires =  array(
									'AF' => 'Afghanistan',
									'AL' => 'Albania',
									'DZ' => 'Algeria',
									'AS' => 'American Samoa',
									'AD' => 'Andorra',
									'AO' => 'Angola',
									'AI' => 'Anguilla',
									'AG' => 'Antigua and Barbuda',
									'AR' => 'Argentina',
									'AM' => 'Armenia',
									'AW' => 'Aruba',
									'AU' => 'Australia',
									'AT' => 'Austria',
									'AZ' => 'Azerbaijan',
									'BS' => 'Bahamas',
									'BH' => 'Bahrain',
									'BD' => 'Bangladesh',
									'BB' => 'Barbados',
									'BY' => 'Belarus',
									'BE' => 'Belgium',
									'BZ' => 'Belize',
									'BJ' => 'Benin',
									'BM' => 'Bermuda',
									'BT' => 'Bhutan',
									'BO' => 'Bolivia',
									'BA' => 'Bosnia and Herzegovina',
									'BW' => 'Botswana',
									'BR' => 'Brazil',
									'VG' => 'British Virgin Islands',
									'BN' => 'Brunei',
									'BG' => 'Bulgaria',
									'BF' => 'Burkina Faso',
									'BI' => 'Burundi',
									'KH' => 'Cambodia',
									'CM' => 'Cameroon',
									'CA' => 'Canada',
									'CV' => 'Cape Verde',
									'KY' => 'Cayman Islands',
									'CF' => 'Central African Republic',
									'TD' => 'Chad',
									'CL' => 'Chile',
									'CN' => 'China',
									'CO' => 'Colombia',
									'KM' => 'Comoros',
									'CK' => 'Cook Islands',
									'CR' => 'Costa Rica',
									'HR' => 'Croatia',
									'CU' => 'Cuba',
									'CY' => 'Cyprus',
									'CZ' => 'Czech Republic',
									'DK' => 'Denmark',
									'DJ' => 'Djibouti',
									'DM' => 'Dominica',
									'DO' => 'Dominican Republic',
									'TL' => 'East Timor',
									'EC' => 'Ecuador',
									'EG' => 'Egypt',
									'SV' => 'El Salvador',
									'GQ' => 'Equatorial Guinea',
									'ER' => 'Eritrea',
									'EE' => 'Estonia',
									'ET' => 'Ethiopia',
									'FK' => 'Falkland Islands',
									'FO' => 'Faroe Islands',
									'FJ' => 'Fiji',
									'FI' => 'Finland',
									'FR' => 'France',
									'GF' => 'French Guiana',
									'PF' => 'French Polynesia',
									'GA' => 'Gabon',
									'GM' => 'Gambia',
									'GE' => 'Georgia',
									'DE' => 'Germany',
									'GH' => 'Ghana',
									'GI' => 'Gibraltar',
									'GR' => 'Greece',
									'GL' => 'Greenland',
									'GD' => 'Grenada',
									'GP' => 'Guadeloupe',
									'GU' => 'Guam',
									'GT' => 'Guatemala',
									'GG' => 'Guernsey',
									'GN' => 'Guinea',
									'GW' => 'Guinea-Bissau',
									'GY' => 'Guyana',
									'HT' => 'Haiti',
									'HN' => 'Honduras',
									'HK' => 'Hong Kong',
									'HU' => 'Hungary',
									'IS' => 'Iceland',
									'IN' => 'India',
									'ID' => 'Indonesia',
									'IR' => 'Iran',
									'IQ' => 'Iraq',
									'IE' => 'Ireland',
									'IL' => 'Israel',
									'IT' => 'Italy',
									'CI' => 'Ivory Coast',
									'JM' => 'Jamaica',
									'JP' => 'Japan',
									'JE' => 'Jersey',
									'JO' => 'Jordan',
									'KZ' => 'Kazakhstan',
									'KE' => 'Kenya',
									'KI' => 'Kiribati',
									'KW' => 'Kuwait',
									'KG' => 'Kyrgyzstan',
									'LA' => 'Laos',
									'LV' => 'Latvia',
									'LB' => 'Lebanon',
									'LS' => 'Lesotho',
									'LR' => 'Liberia',
									'LY' => 'Libya',
									'LI' => 'Liechtenstein',
									'LT' => 'Lithuania',
									'LU' => 'Luxembourg',
									'MO' => 'Macao',
									'MK' => 'Macedonia',
									'MG' => 'Madagascar',
									'MW' => 'Malawi',
									'MY' => 'Malaysia',
									'MV' => 'Maldives',
									'ML' => 'Mali',
									'MT' => 'Malta',
									'MH' => 'Marshall Islands',
									'MQ' => 'Martinique',
									'MR' => 'Mauritania',
									'MU' => 'Mauritius',
									'YT' => 'Mayotte',
									'MX' => 'Mexico',
									'FM' => 'Micronesia',
									'MD' => 'Moldova',
									'MC' => 'Monaco',
									'MN' => 'Mongolia',
									'ME' => 'Montenegro',
									'MS' => 'Montserrat',
									'MA' => 'Morocco',
									'MZ' => 'Mozambique',
									'MM' => 'Myanmar',
									'NA' => 'Namibia',
									'NR' => 'Nauru',
									'NP' => 'Nepal',
									'NL' => 'Netherlands',
									'NC' => 'New Caledonia',
									'NZ' => 'New Zealand',
									'NI' => 'Nicaragua',
									'NE' => 'Niger',
									'NG' => 'Nigeria',
									'NU' => 'Niue',
									'KP' => 'North Korea',
									'MP' => 'Northern Mariana Islands',
									'NO' => 'Norway',
									'OM' => 'Oman',
									'PK' => 'Pakistan',
									'PW' => 'Palau',
									'PA' => 'Panama',
									'PG' => 'Papua New Guinea',
									'PY' => 'Paraguay',
									'PE' => 'Peru',
									'PH' => 'Philippines',
									'PL' => 'Poland',
									'PT' => 'Portugal',
									'PR' => 'Puerto Rico',
									'QA' => 'Qatar',
									'CG' => 'Republic of the Congo',
									'RE' => 'Reunion',
									'RO' => 'Romania',
									'RU' => 'Russia',
									'RW' => 'Rwanda',
									'SH' => 'Saint Helena',
									'KN' => 'Saint Kitts and Nevis',
									'LC' => 'Saint Lucia',
									'VC' => 'Saint Vincent and the Grenadines',
									'WS' => 'Samoa',
									'SM' => 'San Marino',
									'ST' => 'Sao Tome and Principe',
									'SA' => 'Saudi Arabia',
									'SN' => 'Senegal',
									'RS' => 'Serbia',
									'SC' => 'Seychelles',
									'SL' => 'Sierra Leone',
									'SG' => 'Singapore',
									'SK' => 'Slovakia',
									'SI' => 'Slovenia',
									'SB' => 'Solomon Islands',
									'SO' => 'Somalia',
									'ZA' => 'South Africa',
									'KR' => 'South Korea',
									'SS' => 'South Sudan',
									'ES' => 'Spain',
									'LK' => 'Sri Lanka',
									'SD' => 'Sudan',
									'SR' => 'Suriname',
									'SZ' => 'Swaziland',
									'SE' => 'Sweden',
									'CH' => 'Switzerland',
									'SY' => 'Syria',
									'TW' => 'Taiwan',
									'TJ' => 'Tajikistan',
									'TZ' => 'Tanzania',
									'TH' => 'Thailand',
									'TG' => 'Togo',
									'TO' => 'Tonga',
									'TT' => 'Trinidad and Tobago',
									'TN' => 'Tunisia',
									'TR' => 'Turkey',
									'TC' => 'Turks and Caicos Islands',
									'TV' => 'Tuvalu',
									'VI' => 'U.S. Virgin Islands',
									'UG' => 'Uganda',
									'UA' => 'Ukraine',
									'AE' => 'United Arab Emirates',
									'GB' => 'United Kingdom',
									'US' => 'United States',
									'UY' => 'Uruguay',
									'UZ' => 'Uzbekistan',
									'VU' => 'Vanuatu',
									'VE' => 'Venezuela',
									'VN' => 'Vietnam',
									'YE' => 'Yemen',
									'ZM' => 'Zambia',
									'ZW' => 'Zimbabwe',
								);
		$value = array();
		$value['AD'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AE'] = array('region' => 'AP', 'currency' =>'AED', 'weight' => 'KG_CM');
		$value['AF'] = array('region' => 'AP', 'currency' =>'AFN', 'weight' => 'KG_CM');
		$value['AG'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AI'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AL'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AM'] = array('region' => 'AP', 'currency' =>'AMD', 'weight' => 'KG_CM');
		$value['AN'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'KG_CM');
		$value['AO'] = array('region' => 'AP', 'currency' =>'AOA', 'weight' => 'KG_CM');
		$value['AR'] = array('region' => 'AM', 'currency' =>'ARS', 'weight' => 'KG_CM');
		$value['AS'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['AT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AU'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['AW'] = array('region' => 'AM', 'currency' =>'AWG', 'weight' => 'LB_IN');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['BA'] = array('region' => 'AP', 'currency' =>'BAM', 'weight' => 'KG_CM');
		$value['BB'] = array('region' => 'AM', 'currency' =>'BBD', 'weight' => 'LB_IN');
		$value['BD'] = array('region' => 'AP', 'currency' =>'BDT', 'weight' => 'KG_CM');
		$value['BE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['BF'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BG'] = array('region' => 'EU', 'currency' =>'BGN', 'weight' => 'KG_CM');
		$value['BH'] = array('region' => 'AP', 'currency' =>'BHD', 'weight' => 'KG_CM');
		$value['BI'] = array('region' => 'AP', 'currency' =>'BIF', 'weight' => 'KG_CM');
		$value['BJ'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BM'] = array('region' => 'AM', 'currency' =>'BMD', 'weight' => 'LB_IN');
		$value['BN'] = array('region' => 'AP', 'currency' =>'BND', 'weight' => 'KG_CM');
		$value['BO'] = array('region' => 'AM', 'currency' =>'BOB', 'weight' => 'KG_CM');
		$value['BR'] = array('region' => 'AM', 'currency' =>'BRL', 'weight' => 'KG_CM');
		$value['BS'] = array('region' => 'AM', 'currency' =>'BSD', 'weight' => 'LB_IN');
		$value['BT'] = array('region' => 'AP', 'currency' =>'BTN', 'weight' => 'KG_CM');
		$value['BW'] = array('region' => 'AP', 'currency' =>'BWP', 'weight' => 'KG_CM');
		$value['BY'] = array('region' => 'AP', 'currency' =>'BYR', 'weight' => 'KG_CM');
		$value['BZ'] = array('region' => 'AM', 'currency' =>'BZD', 'weight' => 'KG_CM');
		$value['CA'] = array('region' => 'AM', 'currency' =>'CAD', 'weight' => 'LB_IN');
		$value['CF'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CG'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CH'] = array('region' => 'EU', 'currency' =>'CHF', 'weight' => 'KG_CM');
		$value['CI'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['CK'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['CL'] = array('region' => 'AM', 'currency' =>'CLP', 'weight' => 'KG_CM');
		$value['CM'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CN'] = array('region' => 'AP', 'currency' =>'CNY', 'weight' => 'KG_CM');
		$value['CO'] = array('region' => 'AM', 'currency' =>'COP', 'weight' => 'KG_CM');
		$value['CR'] = array('region' => 'AM', 'currency' =>'CRC', 'weight' => 'KG_CM');
		$value['CU'] = array('region' => 'AM', 'currency' =>'CUC', 'weight' => 'KG_CM');
		$value['CV'] = array('region' => 'AP', 'currency' =>'CVE', 'weight' => 'KG_CM');
		$value['CY'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['CZ'] = array('region' => 'EU', 'currency' =>'CZK', 'weight' => 'KG_CM');
		$value['DE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['DJ'] = array('region' => 'EU', 'currency' =>'DJF', 'weight' => 'KG_CM');
		$value['DK'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['DM'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['DO'] = array('region' => 'AP', 'currency' =>'DOP', 'weight' => 'LB_IN');
		$value['DZ'] = array('region' => 'AM', 'currency' =>'DZD', 'weight' => 'KG_CM');
		$value['EC'] = array('region' => 'EU', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['EE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['EG'] = array('region' => 'AP', 'currency' =>'EGP', 'weight' => 'KG_CM');
		$value['ER'] = array('region' => 'EU', 'currency' =>'ERN', 'weight' => 'KG_CM');
		$value['ES'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ET'] = array('region' => 'AU', 'currency' =>'ETB', 'weight' => 'KG_CM');
		$value['FI'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['FJ'] = array('region' => 'AP', 'currency' =>'FJD', 'weight' => 'KG_CM');
		$value['FK'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['FM'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['FO'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['FR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GA'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GD'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['GE'] = array('region' => 'AM', 'currency' =>'GEL', 'weight' => 'KG_CM');
		$value['GF'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GG'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GH'] = array('region' => 'AP', 'currency' =>'GHS', 'weight' => 'KG_CM');
		$value['GI'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GL'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['GM'] = array('region' => 'AP', 'currency' =>'GMD', 'weight' => 'KG_CM');
		$value['GN'] = array('region' => 'AP', 'currency' =>'GNF', 'weight' => 'KG_CM');
		$value['GP'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GQ'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GT'] = array('region' => 'AM', 'currency' =>'GTQ', 'weight' => 'KG_CM');
		$value['GU'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['GW'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['GY'] = array('region' => 'AP', 'currency' =>'GYD', 'weight' => 'LB_IN');
		$value['HK'] = array('region' => 'AM', 'currency' =>'HKD', 'weight' => 'KG_CM');
		$value['HN'] = array('region' => 'AM', 'currency' =>'HNL', 'weight' => 'KG_CM');
		$value['HR'] = array('region' => 'AP', 'currency' =>'HRK', 'weight' => 'KG_CM');
		$value['HT'] = array('region' => 'AM', 'currency' =>'HTG', 'weight' => 'LB_IN');
		$value['HU'] = array('region' => 'EU', 'currency' =>'HUF', 'weight' => 'KG_CM');
		$value['IC'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ID'] = array('region' => 'AP', 'currency' =>'IDR', 'weight' => 'KG_CM');
		$value['IE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['IL'] = array('region' => 'AP', 'currency' =>'ILS', 'weight' => 'KG_CM');
		$value['IN'] = array('region' => 'AP', 'currency' =>'INR', 'weight' => 'KG_CM');
		$value['IQ'] = array('region' => 'AP', 'currency' =>'IQD', 'weight' => 'KG_CM');
		$value['IR'] = array('region' => 'AP', 'currency' =>'IRR', 'weight' => 'KG_CM');
		$value['IS'] = array('region' => 'EU', 'currency' =>'ISK', 'weight' => 'KG_CM');
		$value['IT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['JE'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['JM'] = array('region' => 'AM', 'currency' =>'JMD', 'weight' => 'KG_CM');
		$value['JO'] = array('region' => 'AP', 'currency' =>'JOD', 'weight' => 'KG_CM');
		$value['JP'] = array('region' => 'AP', 'currency' =>'JPY', 'weight' => 'KG_CM');
		$value['KE'] = array('region' => 'AP', 'currency' =>'KES', 'weight' => 'KG_CM');
		$value['KG'] = array('region' => 'AP', 'currency' =>'KGS', 'weight' => 'KG_CM');
		$value['KH'] = array('region' => 'AP', 'currency' =>'KHR', 'weight' => 'KG_CM');
		$value['KI'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['KM'] = array('region' => 'AP', 'currency' =>'KMF', 'weight' => 'KG_CM');
		$value['KN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['KP'] = array('region' => 'AP', 'currency' =>'KPW', 'weight' => 'LB_IN');
		$value['KR'] = array('region' => 'AP', 'currency' =>'KRW', 'weight' => 'KG_CM');
		$value['KV'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['KW'] = array('region' => 'AP', 'currency' =>'KWD', 'weight' => 'KG_CM');
		$value['KY'] = array('region' => 'AM', 'currency' =>'KYD', 'weight' => 'KG_CM');
		$value['KZ'] = array('region' => 'AP', 'currency' =>'KZF', 'weight' => 'LB_IN');
		$value['LA'] = array('region' => 'AP', 'currency' =>'LAK', 'weight' => 'KG_CM');
		$value['LB'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['LC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'KG_CM');
		$value['LI'] = array('region' => 'AM', 'currency' =>'CHF', 'weight' => 'LB_IN');
		$value['LK'] = array('region' => 'AP', 'currency' =>'LKR', 'weight' => 'KG_CM');
		$value['LR'] = array('region' => 'AP', 'currency' =>'LRD', 'weight' => 'KG_CM');
		$value['LS'] = array('region' => 'AP', 'currency' =>'LSL', 'weight' => 'KG_CM');
		$value['LT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LU'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LV'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LY'] = array('region' => 'AP', 'currency' =>'LYD', 'weight' => 'KG_CM');
		$value['MA'] = array('region' => 'AP', 'currency' =>'MAD', 'weight' => 'KG_CM');
		$value['MC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MD'] = array('region' => 'AP', 'currency' =>'MDL', 'weight' => 'KG_CM');
		$value['ME'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MG'] = array('region' => 'AP', 'currency' =>'MGA', 'weight' => 'KG_CM');
		$value['MH'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MK'] = array('region' => 'AP', 'currency' =>'MKD', 'weight' => 'KG_CM');
		$value['ML'] = array('region' => 'AP', 'currency' =>'COF', 'weight' => 'KG_CM');
		$value['MM'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['MN'] = array('region' => 'AP', 'currency' =>'MNT', 'weight' => 'KG_CM');
		$value['MO'] = array('region' => 'AP', 'currency' =>'MOP', 'weight' => 'KG_CM');
		$value['MP'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MQ'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MR'] = array('region' => 'AP', 'currency' =>'MRO', 'weight' => 'KG_CM');
		$value['MS'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['MT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MU'] = array('region' => 'AP', 'currency' =>'MUR', 'weight' => 'KG_CM');
		$value['MV'] = array('region' => 'AP', 'currency' =>'MVR', 'weight' => 'KG_CM');
		$value['MW'] = array('region' => 'AP', 'currency' =>'MWK', 'weight' => 'KG_CM');
		$value['MX'] = array('region' => 'AM', 'currency' =>'MXN', 'weight' => 'KG_CM');
		$value['MY'] = array('region' => 'AP', 'currency' =>'MYR', 'weight' => 'KG_CM');
		$value['MZ'] = array('region' => 'AP', 'currency' =>'MZN', 'weight' => 'KG_CM');
		$value['NA'] = array('region' => 'AP', 'currency' =>'NAD', 'weight' => 'KG_CM');
		$value['NC'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['NE'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['NG'] = array('region' => 'AP', 'currency' =>'NGN', 'weight' => 'KG_CM');
		$value['NI'] = array('region' => 'AM', 'currency' =>'NIO', 'weight' => 'KG_CM');
		$value['NL'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['NO'] = array('region' => 'EU', 'currency' =>'NOK', 'weight' => 'KG_CM');
		$value['NP'] = array('region' => 'AP', 'currency' =>'NPR', 'weight' => 'KG_CM');
		$value['NR'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['NU'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['NZ'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['OM'] = array('region' => 'AP', 'currency' =>'OMR', 'weight' => 'KG_CM');
		$value['PA'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PE'] = array('region' => 'AM', 'currency' =>'PEN', 'weight' => 'KG_CM');
		$value['PF'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['PG'] = array('region' => 'AP', 'currency' =>'PGK', 'weight' => 'KG_CM');
		$value['PH'] = array('region' => 'AP', 'currency' =>'PHP', 'weight' => 'KG_CM');
		$value['PK'] = array('region' => 'AP', 'currency' =>'PKR', 'weight' => 'KG_CM');
		$value['PL'] = array('region' => 'EU', 'currency' =>'PLN', 'weight' => 'KG_CM');
		$value['PR'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['PT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['PW'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PY'] = array('region' => 'AM', 'currency' =>'PYG', 'weight' => 'KG_CM');
		$value['QA'] = array('region' => 'AP', 'currency' =>'QAR', 'weight' => 'KG_CM');
		$value['RE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['RO'] = array('region' => 'EU', 'currency' =>'RON', 'weight' => 'KG_CM');
		$value['RS'] = array('region' => 'AP', 'currency' =>'RSD', 'weight' => 'KG_CM');
		$value['RU'] = array('region' => 'AP', 'currency' =>'RUB', 'weight' => 'KG_CM');
		$value['RW'] = array('region' => 'AP', 'currency' =>'RWF', 'weight' => 'KG_CM');
		$value['SA'] = array('region' => 'AP', 'currency' =>'SAR', 'weight' => 'KG_CM');
		$value['SB'] = array('region' => 'AP', 'currency' =>'SBD', 'weight' => 'KG_CM');
		$value['SC'] = array('region' => 'AP', 'currency' =>'SCR', 'weight' => 'KG_CM');
		$value['SD'] = array('region' => 'AP', 'currency' =>'SDG', 'weight' => 'KG_CM');
		$value['SE'] = array('region' => 'EU', 'currency' =>'SEK', 'weight' => 'KG_CM');
		$value['SG'] = array('region' => 'AP', 'currency' =>'SGD', 'weight' => 'KG_CM');
		$value['SH'] = array('region' => 'AP', 'currency' =>'SHP', 'weight' => 'KG_CM');
		$value['SI'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SK'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SL'] = array('region' => 'AP', 'currency' =>'SLL', 'weight' => 'KG_CM');
		$value['SM'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SN'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['SO'] = array('region' => 'AM', 'currency' =>'SOS', 'weight' => 'KG_CM');
		$value['SR'] = array('region' => 'AM', 'currency' =>'SRD', 'weight' => 'KG_CM');
		$value['SS'] = array('region' => 'AP', 'currency' =>'SSP', 'weight' => 'KG_CM');
		$value['ST'] = array('region' => 'AP', 'currency' =>'STD', 'weight' => 'KG_CM');
		$value['SV'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['SY'] = array('region' => 'AP', 'currency' =>'SYP', 'weight' => 'KG_CM');
		$value['SZ'] = array('region' => 'AP', 'currency' =>'SZL', 'weight' => 'KG_CM');
		$value['TC'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['TD'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['TG'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['TH'] = array('region' => 'AP', 'currency' =>'THB', 'weight' => 'KG_CM');
		$value['TJ'] = array('region' => 'AP', 'currency' =>'TJS', 'weight' => 'KG_CM');
		$value['TL'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['TN'] = array('region' => 'AP', 'currency' =>'TND', 'weight' => 'KG_CM');
		$value['TO'] = array('region' => 'AP', 'currency' =>'TOP', 'weight' => 'KG_CM');
		$value['TR'] = array('region' => 'AP', 'currency' =>'TRY', 'weight' => 'KG_CM');
		$value['TT'] = array('region' => 'AM', 'currency' =>'TTD', 'weight' => 'LB_IN');
		$value['TV'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['TW'] = array('region' => 'AP', 'currency' =>'TWD', 'weight' => 'KG_CM');
		$value['TZ'] = array('region' => 'AP', 'currency' =>'TZS', 'weight' => 'KG_CM');
		$value['UA'] = array('region' => 'AP', 'currency' =>'UAH', 'weight' => 'KG_CM');
		$value['UG'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['US'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['UY'] = array('region' => 'AM', 'currency' =>'UYU', 'weight' => 'KG_CM');
		$value['UZ'] = array('region' => 'AP', 'currency' =>'UZS', 'weight' => 'KG_CM');
		$value['VC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['VE'] = array('region' => 'AM', 'currency' =>'VEF', 'weight' => 'KG_CM');
		$value['VG'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VI'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VN'] = array('region' => 'AP', 'currency' =>'VND', 'weight' => 'KG_CM');
		$value['VU'] = array('region' => 'AP', 'currency' =>'VUV', 'weight' => 'KG_CM');
		$value['WS'] = array('region' => 'AP', 'currency' =>'WST', 'weight' => 'KG_CM');
		$value['XB'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XE'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['XM'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['XS'] = array('region' => 'AP', 'currency' =>'SIS', 'weight' => 'KG_CM');
		$value['XY'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['YE'] = array('region' => 'AP', 'currency' =>'YER', 'weight' => 'KG_CM');
		$value['YT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ZA'] = array('region' => 'AP', 'currency' =>'ZAR', 'weight' => 'KG_CM');
		$value['ZM'] = array('region' => 'AP', 'currency' =>'ZMW', 'weight' => 'KG_CM');
		$value['ZW'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
	
	$packing_type = array("per_item" => "Pack Items Induviually", "weight_based" => "Weight Based Packing", "box" => "Box Packing");
	$boxes = include_once('data_helper/default_boxes.php');
	$package_type = array('BOX' => 'PostNord Box','FLY' => 'Flyer','YP' => 'Your Pack');
	$weight_dim_unit = array("KG_CM" => "KG_CM", "G_CM" => "G_CM");
	$part_type = array("160" => "160 - customer number", "167" => "167 - VAT customer number", "156" => "156 - Service point ID", "229" => "229 - Geographic location");
	$issue_c = array("Z11-DK" => "Z11-Denmark", "Z12-SE" => "Z12-Sweden", "Z13-NO" => "Z13-Norway", "Z14-FI" => "Z14-Finland");
	$general_settings = get_option('hitshipo_pn_main_settings');
	$general_settings = empty($general_settings) ? array() : $general_settings;
	
	function hitshipo_sanitize_array($arr_to_san = []){
		$sanitized_data = [];
		if (!empty($arr_to_san) && is_array($arr_to_san)) {
			foreach ($arr_to_san as $key => $value) {
				$sanitized_data[$key] = sanitize_text_field($value);
			}
		}
		return $sanitized_data;
	}

	if(isset($_POST['save']))
	{	
		if(isset($_POST['hitshipo_pn_site_id'])){
			
			$boxes_id = isset($_POST['boxes_id']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_id'])) : array();
			$boxes_name = isset($_POST['boxes_name']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_name'])) : array();
			$boxes_length = isset($_POST['boxes_length']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_length'])) : array();
			$boxes_width = isset($_POST['boxes_width']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_width'])) : array();
			$boxes_height = isset($_POST['boxes_height']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_height'])) : array();
			$boxes_box_weight = isset($_POST['boxes_box_weight']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_box_weight'])) : array();
			$boxes_max_weight = isset($_POST['boxes_max_weight']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_max_weight'])) : array();
			$boxes_enabled = isset($_POST['boxes_enabled']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_enabled'])) : array();
			$boxes_pack_type = isset($_POST['boxes_pack_type']) ? hitshipo_sanitize_array(wp_unslash($_POST['boxes_pack_type'])) : array();

			$all_boxes = array();
			if (!empty($boxes_name)) {
				// if (isset($boxes_name['filter'])) { //Using sanatize_post() it's adding filter type. Have to unset otherwise it will display as box
				// 	unset($boxes_name['filter']);
				// }
				// if (isset($boxes_name['ID'])) {
				// 	unset($boxes_name['ID']);
				// }
				foreach ($boxes_name as $key => $value) {
					if (empty($value)) {
						continue;
					}
					$ind_box_id = $boxes_id[$key];
					$ind_box_name = empty($boxes_name[$key]) ? "New Box" : $boxes_name[$key];
					$ind_box_length = empty($boxes_length[$key]) ? 0 : $boxes_length[$key];
					$ind_boxes_width = empty($boxes_width[$key]) ? 0 : $boxes_width[$key];
					$ind_boxes_height = empty($boxes_height[$key]) ? 0 : $boxes_height[$key];
					$ind_boxes_box_weight = empty($boxes_box_weight[$key]) ? 0 : $boxes_box_weight[$key];
					$ind_boxes_max_weight = empty($boxes_max_weight[$key]) ? 0 : $boxes_max_weight[$key];
					$ind_box_enabled = isset($boxes_enabled[$key]) ? true : false;

					$all_boxes[$key] = array(
						'id' => $ind_box_id,
						'name' => $ind_box_name,
						'length' => $ind_box_length,
						'width' => $ind_boxes_width,
						'height' => $ind_boxes_height,
						'box_weight' => $ind_boxes_box_weight,
						'max_weight' => $ind_boxes_max_weight,
						'enabled' => $ind_box_enabled,
						'pack_type' => $boxes_pack_type[$key]
					);
				}
			}

			// echo '<pre>';print_r($all_boxes); die();

			$general_settings['hitshipo_pn_site_id'] = sanitize_text_field(isset($_POST['hitshipo_pn_site_id']) ? $_POST['hitshipo_pn_site_id'] : '');
			$general_settings['hitshipo_pn_site_pwd'] = sanitize_text_field(isset($_POST['hitshipo_pn_site_pwd']) ? $_POST['hitshipo_pn_site_pwd'] : '');
			$general_settings['hitshipo_pn_api_key'] = sanitize_text_field(isset($_POST['hitshipo_pn_api_key']) ? $_POST['hitshipo_pn_api_key'] : '');
			$general_settings['hitshipo_pn_test'] = sanitize_text_field(isset($_POST['hitshipo_pn_test']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_rates'] = sanitize_text_field(isset($_POST['hitshipo_pn_rates']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_etd_date'] = sanitize_text_field(isset($_POST['hitshipo_pn_etd_date']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_shipper_name'] = sanitize_text_field(isset($_POST['hitshipo_pn_shipper_name']) ? $_POST['hitshipo_pn_shipper_name'] : '');
			$general_settings['hitshipo_pn_company'] = sanitize_text_field(isset($_POST['hitshipo_pn_company']) ? $_POST['hitshipo_pn_company'] : '');
			$general_settings['hitshipo_pn_mob_num'] = sanitize_text_field(isset($_POST['hitshipo_pn_mob_num']) ? $_POST['hitshipo_pn_mob_num'] : '');
			$general_settings['hitshipo_pn_email'] = sanitize_text_field(isset($_POST['hitshipo_pn_email']) ? $_POST['hitshipo_pn_email'] : '');
			$general_settings['hitshipo_pn_address1'] = sanitize_text_field(isset($_POST['hitshipo_pn_address1']) ? $_POST['hitshipo_pn_address1'] : '');
			$general_settings['hitshipo_pn_address2'] = sanitize_text_field(isset($_POST['hitshipo_pn_address2']) ? $_POST['hitshipo_pn_address2'] : '');
			$general_settings['hitshipo_pn_city'] = sanitize_text_field(isset($_POST['hitshipo_pn_city']) ? $_POST['hitshipo_pn_city'] : '');
			$general_settings['hitshipo_pn_state'] = sanitize_text_field(isset($_POST['hitshipo_pn_state']) ? $_POST['hitshipo_pn_state'] : '');
			$general_settings['hitshipo_pn_zip'] = sanitize_text_field(isset($_POST['hitshipo_pn_zip']) ? $_POST['hitshipo_pn_zip'] : '');
			$general_settings['hitshipo_pn_country'] = sanitize_text_field(isset($_POST['hitshipo_pn_country']) ? $_POST['hitshipo_pn_country'] : '');
			$general_settings['hitshipo_pn_gstin'] = sanitize_text_field(isset($_POST['hitshipo_pn_gstin']) ? $_POST['hitshipo_pn_gstin'] : '');
			$general_settings['hitshipo_pn_carrier'] = isset($_POST['hitshipo_pn_carrier']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_carrier'])) : array();
			$general_settings['hitshipo_pn_carrier_name'] = isset($_POST['hitshipo_pn_carrier_name']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_carrier_name'])) : array();
			$general_settings['hitshipo_pn_carrier_adj'] = isset($_POST['hitshipo_pn_carrier_adj']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_carrier_adj'])) : array();
			$general_settings['hitshipo_pn_carrier_adj_percentage'] = isset($_POST['hitshipo_pn_carrier_adj_percentage']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_carrier_adj_percentage'])) : array();
			$general_settings['hitshipo_pn_account_rates'] = sanitize_text_field(isset($_POST['hitshipo_pn_account_rates']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_excul_tax'] = sanitize_text_field(isset($_POST['hitshipo_pn_excul_tax']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_developer_rate'] = sanitize_text_field(isset($_POST['hitshipo_pn_developer_rate']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_insure'] = sanitize_text_field(isset($_POST['hitshipo_pn_insure']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_exclude_countries'] = isset($_POST['hitshipo_pn_exclude_countries']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_exclude_countries'])) : array();
			
			$general_settings['hitshipo_pn_translation'] = sanitize_text_field(isset($_POST['hitshipo_pn_translation']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_translation_key'] = sanitize_text_field(isset($_POST['hitshipo_pn_translation_key']) ? $_POST['hitshipo_pn_translation_key'] : '');


			$general_settings['hitshipo_pn_uostatus'] = sanitize_text_field(isset($_POST['hitshipo_pn_uostatus']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_trk_status_cus'] = sanitize_text_field(isset($_POST['hitshipo_pn_trk_status_cus']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_email_alert'] = sanitize_text_field(isset($_POST['hitshipo_pn_email_alert']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_sms_alert'] = sanitize_text_field(isset($_POST['hitshipo_pn_sms_alert']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_cod'] = sanitize_text_field(isset($_POST['hitshipo_pn_cod']) ? 'yes' :'no');
			$general_settings['hitshipo_pn_label_automation'] = sanitize_text_field(isset($_POST['hitshipo_pn_label_automation']) ? 'yes' :'no');

			$general_settings['hitshipo_pn_packing_type'] = sanitize_text_field(isset($_POST['hitshipo_pn_packing_type']) ? $_POST['hitshipo_pn_packing_type'] : 'per_item');
			$general_settings['hitshipo_pn_max_weight'] = sanitize_text_field(isset($_POST['hitshipo_pn_max_weight']) ? $_POST['hitshipo_pn_max_weight'] : '100');
			
			$general_settings['hitshipo_pn_label_email'] = sanitize_text_field(isset($_POST['hitshipo_pn_label_email']) ? $_POST['hitshipo_pn_label_email'] : '');
			$general_settings['hitshipo_pn_ship_content'] = sanitize_text_field(isset($_POST['hitshipo_pn_ship_content']) ? $_POST['hitshipo_pn_ship_content'] : 'No shipment content');
			$general_settings['hitshipo_pn_eori'] = sanitize_text_field(isset($_POST['hitshipo_pn_eori']) ? $_POST['hitshipo_pn_eori'] : '');
			$general_settings['hitshipo_pn_hsn'] = sanitize_text_field(isset($_POST['hitshipo_pn_hsn']) ? $_POST['hitshipo_pn_hsn'] : '');
			$general_settings['hitshipo_pn_print_size'] = sanitize_text_field(isset($_POST['hitshipo_pn_print_size']) ? $_POST['hitshipo_pn_print_size'] : '6X4_PDF');
			$general_settings['hitshipo_pn_weight_unit'] = sanitize_text_field(isset($_POST['hitshipo_pn_weight_unit']) ? $_POST['hitshipo_pn_weight_unit'] : 'KG_CM');
			$general_settings['hitshipo_pn_part_type'] = sanitize_text_field(isset($_POST['hitshipo_pn_part_type']) ? $_POST['hitshipo_pn_part_type'] : '');
			$general_settings['hitshipo_pn_issue_c'] = sanitize_text_field(isset($_POST['hitshipo_pn_issue_c']) ? $_POST['hitshipo_pn_issue_c'] : '');
			$general_settings['hitshipo_pn_pac_type'] = sanitize_text_field(isset($_POST['hitshipo_pn_pac_type']) ? $_POST['hitshipo_pn_pac_type'] : '');
			$general_settings['hitshipo_pn_tos'] = sanitize_text_field(isset($_POST['hitshipo_pn_tos']) ? $_POST['hitshipo_pn_tos'] : '');
			$general_settings['hitshipo_pn_tod_cc'] = sanitize_text_field(isset($_POST['hitshipo_pn_tod_cc']) ? $_POST['hitshipo_pn_tod_cc'] : '');
			$general_settings['hitshipo_pn_tod_ccl'] = sanitize_text_field(isset($_POST['hitshipo_pn_tod_ccl']) ? $_POST['hitshipo_pn_tod_ccl'] : '');
			$general_settings['hitshipo_pn_paper_size'] = sanitize_text_field(isset($_POST['hitshipo_pn_paper_size']) ? $_POST['hitshipo_pn_paper_size'] : '');
			$general_settings['hitshipo_pn_pickup_open_date'] = sanitize_text_field(isset($_POST['hitshipo_pn_pickup_open_date']) ? $_POST['hitshipo_pn_pickup_open_date'] : '');
			$general_settings['hitshipo_pn_pickup_close_date'] = sanitize_text_field(isset($_POST['hitshipo_pn_pickup_close_date']) ? $_POST['hitshipo_pn_pickup_close_date'] : '');
			$general_settings['hitshipo_pn_pickup_open_time'] = sanitize_text_field(isset($_POST['hitshipo_pn_pickup_open_time']) ? $_POST['hitshipo_pn_pickup_open_time'] : '');
			$general_settings['hitshipo_pn_pickup_close_time'] = sanitize_text_field(isset($_POST['hitshipo_pn_pickup_close_time']) ? $_POST['hitshipo_pn_pickup_close_time'] : '');
			$general_settings['hitshipo_pn_con_rate'] = sanitize_text_field(isset($_POST['hitshipo_pn_con_rate']) ? $_POST['hitshipo_pn_con_rate'] : '');
			$general_settings['hitshipo_pn_auto_con_rate'] = sanitize_text_field(isset($_POST['hitshipo_pn_auto_con_rate']) ? 'yes' : 'no');

			$general_settings['hitshipo_pn_pickup_automation'] = sanitize_text_field(isset($_POST['hitshipo_pn_pickup_automation']) ? 'yes' :'no');

			// Multi Vendor Settings

			$general_settings['hitshipo_pn_v_enable'] = sanitize_text_field(isset($_POST['hitshipo_pn_v_enable']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_v_rates'] = sanitize_text_field(isset($_POST['hitshipo_pn_v_rates']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_v_labels'] = sanitize_text_field(isset($_POST['hitshipo_pn_v_labels']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_v_roles'] = isset($_POST['hitshipo_pn_v_roles']) ? hitshipo_sanitize_array(wp_unslash($_POST['hitshipo_pn_v_roles'])) : array();
			$general_settings['hitshipo_pn_v_email'] = sanitize_text_field(isset($_POST['hitshipo_pn_v_email']) ? 'yes' : 'no');
			
			$general_settings['hitshipo_pn_track_audit'] = sanitize_text_field(isset($_POST['hitshipo_pn_track_audit']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_daily_report'] = sanitize_text_field(isset($_POST['hitshipo_pn_daily_report']) ? 'yes' : 'no');
			$general_settings['hitshipo_pn_monthly_report'] = sanitize_text_field(isset($_POST['hitshipo_pn_monthly_report']) ? 'yes' : 'no');

			$general_settings['hitshipo_pn_shipo_signup'] = sanitize_text_field(isset($_POST['hitshipo_pn_shipo_signup']) ? $_POST['hitshipo_pn_shipo_signup'] : '');
// echo '<pre>';print_r($general_settings);die();
			// boxes
			$general_settings['hitshipo_pn_boxes'] = !empty($all_boxes) ? $all_boxes : array();
			update_option('hitshipo_pn_main_settings', $general_settings);
			$success = 'Settings Saved Successfully.';
		}

		if ((!isset($general_settings['hitshipo_pn_integration_key']) || empty($general_settings['hitshipo_pn_integration_key'])) && isset($_POST['shipo_link_type']) && $_POST['shipo_link_type'] == "WITH") {
			$general_settings['hitshipo_pn_integration_key'] = sanitize_text_field(isset($_POST['hitshipo_pn_integration_key']) ? $_POST['hitshipo_pn_integration_key'] : '');
			update_option('hitshipo_pn_main_settings', $general_settings);
			update_option('hitshipo_pn_working_status', 'start_working');
			$success = 'Site Linked Successfully.<br><br> It\'s great to have you here.';
		}

		if(!isset($general_settings['hitshipo_pn_integration_key']) || empty($general_settings['hitshipo_pn_integration_key'])){
			$random_nonce = wp_generate_password(16, false);
			set_transient( 'hitshipo_pn_nonce_temp', $random_nonce, HOUR_IN_SECONDS );
			
			$link_hitshipo_request = json_encode(array('site_url' => site_url(),
				'site_name' => get_bloginfo('name'),
				'email_address' => $general_settings['hitshipo_pn_shipo_signup'],
				'password' => (isset($_POST['hitshipo_pn_shipo_signup_pass']) && !empty($_POST['hitshipo_pn_shipo_signup_pass'])) ? base64_encode($_POST['hitshipo_pn_shipo_signup_pass']) : "",
				'nonce' => $random_nonce,
				'audit' => $general_settings['hitshipo_pn_track_audit'],
				'd_report' => $general_settings['hitshipo_pn_daily_report'],
				'm_report' => $general_settings['hitshipo_pn_monthly_report'],
				'pulgin' => 'Postnord',
				'platfrom' => 'woocommerce',
			));
			
			$link_site_url = "https://app.myshipi.com/api/link-site.php";
			//$link_site_url = "http://localhost/hitshipo-v2/api/link-site.php";
			$link_site_response = wp_remote_post( $link_site_url , array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
					'body'        => $link_hitshipo_request,
					'sslverify'   => FALSE
					)
				);
				
				$link_site_response = ( is_array($link_site_response) && isset($link_site_response['body'])) ? json_decode($link_site_response['body'], true) : array();
				if($link_site_response){
					if($link_site_response['status'] != 'error'){
						$general_settings['hitshipo_pn_integration_key'] = sanitize_text_field($link_site_response['integration_key']);
						update_option('hitshipo_pn_main_settings', $general_settings);
						update_option('hitshipo_pn_working_status', 'start_working');
						$success = 'Site Linked Successfully.<br><br> It\'s great to have you here. ' . (isset($link_site_response['trail']) ? 'Your 60days Trail period is started. To know about this more, please check your inbox.' : '' ) . '<br><br><button class="button" type="submit">Back to Settings</button>';
					}else{
						$error = '<p style="color:red;">'. $link_site_response['message'] .'</p>';
						$success = '';
					}
				}else{
					$error = '<p style="color:red;">Failed to connect with Shipi</p>';
					$success = '';
				}
		
		}
		
	}
		$initial_setup = empty($general_settings) ? true : false;
		$countries_obj   = new WC_Countries();
		$default_country = $countries_obj->get_base_country();
		$general_settings['hitshipo_pn_currency'] = isset($value[(isset($general_settings['hitshipo_pn_country']) ? $general_settings['hitshipo_pn_country'] : '')]) ? $value[$general_settings['hitshipo_pn_country']]['currency'] : (isset($value[$default_country]) ? $value[$default_country]['currency'] : "");
		$general_settings['hitshipo_pn_woo_currency'] = get_option('woocommerce_currency');

?>
<style>
.notice{display:none;}
#multistepsform {
  width: 80%;
  margin: 50px auto;
  text-align: center;
  position: relative;
}
#multistepsform fieldset {
  background: white;
  text-align:left;
  border: 0 none;
  border-radius: 5px;
  <?php if (!$initial_setup) { ?>
  box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
  <?php } ?>
  padding: 20px 30px;
  box-sizing: border-box;
  position: relative;
}
<?php if (!$initial_setup) { ?>
#multistepsform fieldset:not(:first-of-type) {
  display: none;
}
<?php } ?>
#multistepsform input[type=text], #multistepsform input[type=password], #multistepsform input[type=number], #multistepsform input[type=email], 
#multistepsform textarea {
  padding: 5px;
  width: 95%;
}
#multistepsform input:focus,
#multistepsform textarea:focus {
  border-color: #679b9b;
  outline: none;
  color: #637373;
}
#multistepsform .action-button {
  width: 100px;
  background: #00a0d6;
  font-weight: bold;
  color: #fff;
  transition: 150ms;
  border: 0 none;
  float:right;
  border-radius: 1px;
  cursor: pointer;
  padding: 10px 5px;
  margin: 10px 5px;
}
#multistepsform .action-button:hover,
#multistepsform .action-button:focus {
  box-shadow: 0 0 0 2px #f08a5d, 0 0 0 3px #ff976;
  color: #fff;
}
#multistepsform .fs-title {
  font-size: 15px;
  text-transform: uppercase;
  color: #2c3e50;
  margin-bottom: 10px;
}
#multistepsform .fs-subtitle {
  font-weight: normal;
  font-size: 13px;
  color: #666;
  margin-bottom: 20px;
}
#multistepsform #progressbar {
  margin-bottom: 30px;
  overflow: hidden;
  counter-reset: step;
}
#multistepsform #progressbar li {
  list-style-type: none;
  color: #7e8e93;
  text-transform: uppercase;
  font-size: 9px;
  width: 16.5%;
  float: left;
  position: relative;
}
#multistepsform #progressbar li:before {
  content: counter(step);
  counter-increment: step;
  width: 20px;
  line-height: 20px;
  display: block;
  font-size: 10px;
  color: #fff;
  background: #7e8e93;
  border-radius: 3px;
  margin: 0 auto 5px auto;
}
#multistepsform #progressbar li:after {
  content: "";
  width: 100%;
  height: 2px;
  background: #7e8e93;
  position: absolute;
  left: -50%;
  top: 9px;
  z-index: -1;
}
#multistepsform #progressbar li:first-child:after {
  content: none;
}
#multistepsform #progressbar li.active {
  color: #00a0d6;
}
#multistepsform #progressbar li.active:before, #multistepsform #progressbar li.active:after {
  background: #00a0d6;
  color: white;
}
.insetbox{
	/*box-shadow: inset 2px 2px 15px 10px #f4f4f4;*/
	padding: 10px;
	<?php if (!$initial_setup) { ?>
	height: 300px;
	overflow: scroll;
	<?php } ?>
}
		</style>
<div style="text-align:center;margin-top:20px;"><img src="<?php echo plugin_dir_url(__FILE__); ?>pn.png" style="width:150px;"></div>

<?php if($success != ''){
	echo '<form id="multistepsform" method="post"><fieldset>
    <center><h2 class="fs-title" style="line-height:27px;">'. $success .'</h2>
	</center></form>';
}else{
	?>
<!-- multistep form -->
<form id="multistepsform" method="post">
  <?php if (!$initial_setup) { ?>
  <!-- progressbar -->
  <ul id="progressbar">
    <li class="active">Integration</li>
    <li>Setup</li>
    <li>Packing</li>
    <li>Rates</li>
    <li>Shipping Label</li>
    <li>Shipi</li>
  </ul>
  <?php } ?>
  <?php if($error == ''){

  ?>
  <!-- fieldsets -->
 <fieldset>
    <center><h2 class="fs-title">PostNord Account Information</h2>
	</center>
	<table style="width:100%">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<div class="insetbox">
	<center>
	<table style="padding-left:10px;padding-right:10px;">
	<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_test" <?php echo (isset($general_settings['hitshipo_pn_test']) && $general_settings['hitshipo_pn_test'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Enable Test Mode</small></span></td>
	<!-- <td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_rates" <?php echo (isset($general_settings['hitshipo_pn_rates']) && $general_settings['hitshipo_pn_rates'] == 'yes') || ($initial_setup) ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Enable Live Shipping Rates.</small></span></td> -->
	<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_label_automation" <?php echo (isset($general_settings['hitshipo_pn_label_automation']) && $general_settings['hitshipo_pn_label_automation'] == 'yes') || ($initial_setup) ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Create Label automatically</small></span></td>
	<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_insure" <?php echo (isset($general_settings['hitshipo_pn_insure']) && $general_settings['hitshipo_pn_insure'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Enable Insurance</small></span></td>
	
	</table>
	</center>
	<table style="width:100%;">
	<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('PostNord API Application ID','hitshipo_pn') ?>
				<input type="text" class="input-text regular-input" name="hitshipo_pn_site_id" id="hitshipo_pn_site_id" value="<?php echo (isset($general_settings['hitshipo_pn_site_id'])) ? $general_settings['hitshipo_pn_site_id'] : ''; ?>">
				<br><small style="color:gray"><?php _e('The ID is assigned by PostNord for the client.','hitshipo_pn') ?></small>
			</td>
			<td style="padding:10px;">
			<?php _e('PostNord Party ID','hitshipo_pn') ?><font style="color:red;">*</font>
			<input type="text" name="hitshipo_pn_site_pwd" id="hitshipo_pn_site_pwd" value="<?php echo (isset($general_settings['hitshipo_pn_site_pwd'])) ? $general_settings['hitshipo_pn_site_pwd'] : ''; ?>">
			<br><small style="color:gray"><?php _e('The information ID associated with the party.','hitshipo_pn') ?></small>	
			</td>
		</tr>
		<tr>
			<td style="padding:10px;">
			<?php _e('PostNord Party ID Type','hitshipo_pn') ?><br>
				<select name="hitshipo_pn_part_type" class="wc-enhanced-select" style="width:95%;padding:5px;">
					<?php foreach($part_type as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_part_type']) && ($general_settings['hitshipo_pn_part_type'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
			<td style="padding:10px;">
				<?php _e('Issuer Code and Country','hitshipo_pn') ?><br>
				<select name="hitshipo_pn_issue_c" class="wc-enhanced-select" style="width:95%;padding:5px;">
					<?php foreach($issue_c as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_issue_c']) && ($general_settings['hitshipo_pn_issue_c'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="padding:10px;">
				<?php _e('PostNord API Key','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_api_key" id="hitshipo_pn_api_key" value="<?php echo (isset($general_settings['hitshipo_pn_api_key'])) ? $general_settings['hitshipo_pn_api_key'] : ''; ?>">
				<br><small style="color:gray"><?php _e('Get the API key from PostNord team.','hitshipo_pn') ?></small>
			</td>
			<td style="padding:10px;vertical-align: top;">
				<?php _e('PostNord Weight and Dimension Unit','hitshipo_pn') ?><br>
				<select name="hitshipo_pn_weight_unit" class="wc-enhanced-select" style="width:95%;padding:5px;">
					<?php foreach($weight_dim_unit as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_weight_unit']) && ($general_settings['hitshipo_pn_weight_unit'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<?php if ($general_settings['hitshipo_pn_woo_currency'] != $general_settings['hitshipo_pn_currency'] ){
			?>
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
				<tr><td colspan="2" style="text-align:center;"><small><?php _e(' Your Website Currency is ','hitshipo_pn') ?> <b><?php echo $general_settings['hitshipo_pn_woo_currency'];?></b> and your PostNord currency is <b><?php echo (isset($general_settings['hitshipo_pn_currency'])) ? $general_settings['hitshipo_pn_currency'] : '(Choose country)'; ?></b>. <?php echo ($general_settings['hitshipo_pn_woo_currency'] != $general_settings['hitshipo_pn_currency'] ) ? 'So you have to consider the converstion rate.' : '' ?></small>
					</td>
				</tr>
				<tr><td colspan="2" style="text-align:center;">
				<input type="checkbox" id="auto_con" name="hitshipo_pn_auto_con_rate" <?php echo (isset($general_settings['hitshipo_pn_auto_con_rate']) && $general_settings['hitshipo_pn_auto_con_rate'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><?php _e('Auto Currency Conversion ','hitshipo_pn') ?>
					
				</td>
				</tr>
				<tr>
					<td style="padding:10px;text-align:center;" colspan="2" class="con_rate" >
						<?php _e('Exchange Rate','hitshipo_pn') ?><font style="color:red;">*</font> <?php echo "( ".$general_settings['hitshipo_pn_woo_currency']."->".$general_settings['hitshipo_pn_currency']." )"; ?>
						<br><input type="text" style="width:240px;" name="hitshipo_pn_con_rate" value="<?php echo (isset($general_settings['hitshipo_pn_con_rate'])) ? $general_settings['hitshipo_pn_con_rate'] : ''; ?>">
						<br><small style="color:gray;"><?php _e('Enter conversion rate.','hitshipo_pn') ?></small>
					</td>
				</tr>
				<!-- <tr><td colspan="2" style="padding:10px;"><hr></td></tr> -->
			<?php
		}
		?>
	</table>
	</div>
	<table style="width:100%">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<?php if(isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] !=''){
		echo '<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />';
	}

	?>
	<?php if (!$initial_setup) { ?>
    <input type="button" name="next" class="next action-button" value="Next" />
    <?php } ?>
  </fieldset>

  <fieldset>
  	<center><h2 class="fs-title">Shipping Address Information</h2></center>
	<table style="width:100%">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<div class="insetbox">
	<table style="width:100%;">
		<!-- <tr><td colspan="2" style="padding:10px;"><hr></td></tr> -->
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('Shipper Name','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_shipper_name" id="hitshipo_pn_shipper_name" value="<?php echo (isset($general_settings['hitshipo_pn_shipper_name'])) ? $general_settings['hitshipo_pn_shipper_name'] : ''; ?>">
			</td>
			<td style="padding:10px;">
			<?php _e('Company Name','hitshipo_pn') ?><font style="color:red;">*</font>
			<input type="text" name="hitshipo_pn_company" id="hitshipo_pn_company" value="<?php echo (isset($general_settings['hitshipo_pn_company'])) ? $general_settings['hitshipo_pn_company'] : ''; ?>">
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('Shipper Mobile / Contact Number','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_mob_num" id="hitshipo_pn_mob_num" value="<?php echo (isset($general_settings['hitshipo_pn_mob_num'])) ? $general_settings['hitshipo_pn_mob_num'] : ''; ?>">
			</td>
			<td style="padding:10px;">
			<?php _e('Email Address of the Shipper','hitshipo_pn') ?><font style="color:red;">*</font>
			<input type="text" name="hitshipo_pn_email" id="hitshipo_pn_email" value="<?php echo (isset($general_settings['hitshipo_pn_email'])) ? $general_settings['hitshipo_pn_email'] : ''; ?>">
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('Address Line 1','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_address1" id="hitshipo_pn_address1" value="<?php echo (isset($general_settings['hitshipo_pn_address1'])) ? $general_settings['hitshipo_pn_address1'] : ''; ?>">
			</td>
			<td style="padding:10px;">
			<?php _e('Address Line 2','hitshipo_pn') ?>
			<input type="text" name="hitshipo_pn_address2" value="<?php echo (isset($general_settings['hitshipo_pn_address2'])) ? $general_settings['hitshipo_pn_address2'] : ''; ?>">
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('City of the Shipper from address','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_city" id="hitshipo_pn_city" value="<?php echo (isset($general_settings['hitshipo_pn_city'])) ? $general_settings['hitshipo_pn_city'] : ''; ?>">
			</td>
			<td style="padding:10px;">
			<?php _e('State (Two letter ISO code accepted.)','hitshipo_pn') ?><font style="color:red;">*</font>
			<input type="text" name="hitshipo_pn_state"id="hitshipo_pn_state" value="<?php echo (isset($general_settings['hitshipo_pn_state'])) ? $general_settings['hitshipo_pn_state'] : ''; ?>">
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('Postal/Zip Code','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_zip" id="hitshipo_pn_zip" value="<?php echo (isset($general_settings['hitshipo_pn_zip'])) ? $general_settings['hitshipo_pn_zip'] : ''; ?>">
			</td>
			<td style="padding:10px;">
			<?php _e('Country of the Shipper from Address','hitshipo_pn') ?><font style="color:red;">*</font>
			<select name="hitshipo_pn_country" class="wc-enhanced-select" style="width:95%;padding:5px;">
					<?php foreach($countires as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_country']) && ($general_settings['hitshipo_pn_country'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('VAT No','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_gstin" value="<?php echo (isset($general_settings['hitshipo_pn_gstin'])) ? $general_settings['hitshipo_pn_gstin'] : ''; ?>">
			</td>
			
		</tr>
		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
	</table>
	<center><h2 class="fs-title">Are you gonna use Multi Vendor?</h2><br>
	<table style="padding-left:10px;padding-right:10px;">
		<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_v_enable" <?php echo (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Use Multi-Vendor.</small></span></td>
		<!-- <td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_v_rates" <?php echo (isset($general_settings['hitshipo_pn_v_rates']) && $general_settings['hitshipo_pn_v_rates'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Get rates from vendor address.</small></span></td> -->
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_v_labels" <?php echo (isset($general_settings['hitshipo_pn_v_labels']) && $general_settings['hitshipo_pn_v_labels'] == 'yes') || ($initial_setup) ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Create Label from vendor address.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_v_email" <?php echo (isset($general_settings['hitshipo_pn_v_email']) && $general_settings['hitshipo_pn_v_email'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Email the shipping labels to vendors.</small></span></td>
	</table>
	</center>
	<table style="width:100%">
						
						
						<tr>
							<td style=" width: 50%;padding:10px;text-align:center;">
								<?php _e('Vendor role','hitshipo_pn') ?></h4><br>
								<select name="hitshipo_pn_v_roles[]" style="padding:5px;width:240px;">

									<?php foreach (get_editable_roles() as $role_name => $role_info){
										if(isset($general_settings['hitshipo_pn_v_roles']) && in_array($role_name, $general_settings['hitshipo_pn_v_roles'])){
											echo "<option value=".$role_name." selected='true'>".$role_info['name']."</option>";
										}else{
											echo "<option value=".$role_name.">".$role_info['name']."</option>";	
										}
										
									}
								?>

								</select><br>
								<small style="color:gray;"> To this role users edit page, you can find the new<br>fields to enter the ship from address.</small>
								
							</td>
						</tr>
						<!-- <tr><td style="padding:10px;"><hr></td></tr> -->
					</table>
	</div>
	<table style="width:100%">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<?php if(isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] !=''){
		echo '<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />';
	}

	?>
	<?php if (!$initial_setup) { ?>
		<input type="button" name="next" class="next action-button" value="Next" />
		<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php } ?>
  </fieldset>

<fieldset <?php echo ($initial_setup) ? 'style="display:none"' : ''?>>
	<center><h2 class="fs-title">Choose Packing ALGORITHM</h2></center>
	<table style="width:100%">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<div class="insetbox">
	<table style="width:100%">
						<tr>
							<td style=" width: 50%;padding:10px;">
								<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Integration key Created from HIT Shipo','hitshipo_pn') ?>"></span>	<?php _e('Select Package Type','hitshipo_pn') ?><font style="color:red;">*</font></h4>
							</td>
							<td style="padding:10px;">
								<select name="hitshipo_pn_packing_type" style="padding:5px; width:95%;" id = "hitshipo_pn_packing_type" class="wc-enhanced-select" style="width:153px;" onchange="changepacktype(this)">
									<?php foreach($packing_type as $key => $value)
									{
										if(isset($general_settings['hitshipo_pn_packing_type']) && ($general_settings['hitshipo_pn_packing_type'] == $key))
										{
											echo "<option value=".$key." selected='true'>".$value."</option>";
										}
										else
										{
											echo "<option value=".$key.">".$value."</option>";
										}
									} ?>
								</select>
							</td>
						</tr>
						<tr style=" display:none;" id="weight_based">
							<td style=" width: 50%;padding:10px;">
								<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('To email address, the shipping label, Commercial invoice will sent.') ?>"></span>	<?php _e('What is the Maximum weight to one package?','hitshipo_pn') ?><font style="color:red;">*</font></h4>
							</td>
							<td style="padding:10px;">
								<input type="number" name="hitshipo_pn_max_weight" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_max_weight'])) ? $general_settings['hitshipo_pn_max_weight'] : ''; ?>">
							</td>
						</tr>
					</table>
					<div id="box_pack" style="width: 100%;">
					<h4 style="font-size: 16px;">Box packing configuration</h4><p>( Saved boxes are used when package type is "BOX". )</p>
					<table id="box_pack_t">
						<tr>
							<th style="padding:3px;"></th>
							<th style="padding:3px;"><?php _e('Name','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Length','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Width','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Height','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Box Weight','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Max Weight','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Enabled','hitshipo_pn') ?><font style="color:red;">*</font></th>
							<th style="padding:3px;"><?php _e('Package Type','hitshipo_pn') ?><font style="color:red;">*</font></th>
						</tr>
						<tbody id="box_pack_tbody">
							<?php

							$boxes = ( isset($general_settings['hitshipo_pn_boxes']) ) ? $general_settings['hitshipo_pn_boxes'] : $boxes;
								if (!empty($boxes)) {//echo '<pre>';print_r($general_settings['hitshipo_pn_boxes']);die();
									foreach ($boxes as $key => $box) {
										echo '<tr>
												<td class="check-column" style="padding:3px;"><input type="checkbox" /></td>
												<input type="hidden" size="1" name="boxes_id['.$key.']" value="'.$box["id"].'"/>
												<td style="padding:3px;"><input type="text" size="25" name="boxes_name['.$key.']" value="'.$box["name"].'" /></td>
												<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_length['.$key.']" value="'.$box["length"].'" /></td>
												<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_width['.$key.']" value="'.$box["width"].'" /></td>
												<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_height['.$key.']" value="'.$box["height"].'" /></td>
												<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_box_weight['.$key.']" value="'.$box["box_weight"].'" /></td>
												<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_max_weight['.$key.']" value="'.$box["max_weight"].'" /></td>';
												if ($box['enabled'] == true) {
													echo '<td style="padding:3px;"><center><input type="checkbox" name="boxes_enabled['.$key.']" checked/></center></td>';
												}else {
													echo '<td style="padding:3px;"><center><input type="checkbox" name="boxes_enabled['.$key.']" /></center></td>';
												}
												
										echo '<td style="padding:3px;"><select name="boxes_pack_type['.$key.']">';
											foreach ($package_type as $k => $v) {
												$selected = ($k==$box['pack_type']) ? "selected='true'" : '';
												echo '<option value="'.$k.'" ' .$selected. '>'.$v.'</option>';
											}
										echo '</select></td>
											</tr>';
									}
								}
							?>
							<tfoot>
							<tr>
								<th colspan="6">
									<a href="#" class="button button-secondary" id="add_box"><?php _e('Add Box','hitshipo_pn') ?></a>
									<a href="#" class="button button-secondary" id="remove_box"><?php _e('Remove selected box(es)','hitshipo_pn') ?></a>
								</th>
							</tr>
						</tfoot>
						</tbody>
					</table>
				</div>
		</div>
		<table style="width:100%">
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
	<?php if(isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] !=''){
		echo '<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />';
	}

	?>
	<?php if (!$initial_setup) { ?>
	<input type="button" name="next" class="next action-button" value="Next" />
	<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php } ?>
</fieldset>

  <fieldset>
  <center><h2 class="fs-title">Rates</h2>
  	<!-- <table style="padding-left:10px;padding-right:10px;">
		<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_etd_date" <?php echo (isset($general_settings['hitshipo_pn_etd_date']) && $general_settings['hitshipo_pn_etd_date'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Show delivery date.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_account_rates" <?php echo (isset($general_settings['hitshipo_pn_account_rates']) && $general_settings['hitshipo_pn_account_rates'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Fetch PostNord account rates.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_excul_tax" <?php echo (isset($general_settings['hitshipo_pn_excul_tax']) && $general_settings['hitshipo_pn_excul_tax'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Exclude tax.</small></span></td>
	</table> -->
  </center>
  <table style="width:100%;">
  	<tr><td style="padding:10px;"><hr></td></tr>
  </table>
  <div class="insetbox">
  	<center style="color: red;">Note: Currently Rating is not available. Selected services are only avilable to create label manually.</center>
  	<!-- <table style="width:100%">
					
  		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
				<input type="checkbox" name="hitshipo_pn_translation" id="hitshipo_pn_translation" <?php echo (isset($general_settings['hitshipo_pn_translation']) && $general_settings['hitshipo_pn_translation'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" > <?php _e('Address translation any language to english.','hitshipo_pn') ?><br>
					<small style="color:gray">Use this if you have your own language to checkout.</small>
				</td>
				<td style=" width: 50%;padding:10px;" >
					<div id="translation_key">
					<?php _e('Google\'s Cloud API Key','hitshipo_pn') ?><br>
					<input type="text" name="hitshipo_pn_translation_key" value="<?php echo (isset($general_settings['hitshipo_pn_translation_key'])) ? $general_settings['hitshipo_pn_translation_key'] : ''; ?>">
					</div>
				</td>
			</tr>
			
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr><td colspan="2" style="padding:10px;"><center><h2 class="fs-title">Do you wants to exclude countries?</h2></center></td></tr>
				
			<tr>
				<td colspan="2" style="text-align:center;padding:10px;">
					<?php _e('Exclude Countries','hitshipo_pn') ?><br>
					<select name="hitshipo_pn_exclude_countries[]" multiple="true" class="wc-enhanced-select" style="padding:5px;width:600px;">

					<?php
					$general_settings['hitshipo_pn_exclude_countries'] = empty($general_settings['hitshipo_pn_exclude_countries'])? array() : $general_settings['hitshipo_pn_exclude_countries'];
					foreach ($countires as $key => $county){
						if(in_array($key,$general_settings['hitshipo_pn_exclude_countries'])){
							echo "<option value=".$key." selected='true'>".$county."</option>";
						}else{
							echo "<option value=".$key.">".$county."</option>";	
						}
						
					}
					?>

					</select>
				</td>
				<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
				
			</tr>
			
		</table> -->
				<center><h2 class="fs-title">Shipping Services & Price adjustment</h2></center>
				<table style="width:100%;">
				
					<tr>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Carries','hitshipo_pn') ?></h3>
						</td>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Alternate Name for Carrier','hitshipo_pn') ?></h3>
						</td>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Price adjustment','hitshipo_pn') ?></h3>
						</td>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Price adjustment (%)','hitshipo_pn') ?></h3>
						</td>
					</tr>
							<?php foreach($_carriers as $key => $value)
							{
								if($value == 'GLOBALMAIL BUSINESS'){
									echo ' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><div style="padding:10px;border:1px solid gray;"><b><u>INTERNATIONAL SERVICES</u><br>
									This all are the services provided by PostNord to ship domestic.<br>
									<span style="background:#fdcd02;color:#d30b2a;">Some of the carrier names are repeated </span>. Please don\'t get confuse on that. <span style="background:#fdcd02;color:#d30b2a;">Select both</span>.This our <span style="background:#fdcd02;color:#d30b2a;">plugin handle this based on ship to country</span>.
								</b></div></td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>';
								}else if($value == "DOMESTIC EXPRESS"){
									echo ' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><div style="padding:10px;border:1px solid gray;"><b><u>DOMESTIC SERVICES</u><br>
										This all are the services provided by PostNord to ship international.<br>
										<span style="background:#fdcd02;color:#d30b2a;">Some of the carrier names are repeated </span>. Please don\'t get confuse on that. <span style="background:#fdcd02;color:#d30b2a;">Select both</span>.This our <span style="background:#fdcd02;color:#d30b2a;">plugin handle this based on ship to country</span>.
									</b></div>
									</td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>';
								}else if ($value == 'JUMBO BOX'){
									echo ' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><b><u>OTHER SPACIAL SERVICES</u><br>
										
									</b>
									</td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>';
								}
								$ser_to_enable = ["7", "18", "88", "97"];
								echo '	<tr>
										<td>
										<input type="checkbox" value="yes" name="hitshipo_pn_carrier['.$key.']" '. ((isset($general_settings['hitshipo_pn_carrier'][$key]) && $general_settings['hitshipo_pn_carrier'][$key] == 'yes') || ($initial_setup && in_array($key, $ser_to_enable)) ? 'checked="true"' : '') .' > <small>'.__($value,"hitshipo_pn").' - [ '.$key.' ]</small>
										</td>
										<td>
											<input type="text" name="hitshipo_pn_carrier_name['.$key.']" value="'.((isset($general_settings['hitshipo_pn_carrier_name'][$key])) ? __($general_settings['hitshipo_pn_carrier_name'][$key],"hitshipo_pn") : '').'">
										</td>
										<td>
											<input type="text" name="hitshipo_pn_carrier_adj['.$key.']" value="'.((isset($general_settings['hitshipo_pn_carrier_adj'][$key])) ? $general_settings['hitshipo_pn_carrier_adj'][$key] : '').'">
										</td>
										<td>
											<input type="text" name="hitshipo_pn_carrier_adj_percentage['.$key.']" value="'.((isset($general_settings['hitshipo_pn_carrier_adj_percentage'][$key])) ? $general_settings['hitshipo_pn_carrier_adj_percentage'][$key] : '').'">
										</td>
										</tr>';
							} ?>
				
				</table>
			</div>
			<table style="width:100%">
				<tr><td style="padding:10px;"><hr></td></tr>
			</table>
			<?php if(isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] !=''){
				echo '<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />';
			}
			?>
			<?php if (!$initial_setup) { ?>
			<input type="button" name="next" class="next action-button" value="Next" />
  			<input type="button" name="previous" class="previous action-button" value="Previous" />
			<?php } ?>
	
 </fieldset> 

 <fieldset>
 <center><h2 class="fs-title">Configure Shipping Label</h2></center>
	<table style="width:100%;">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<div class="insetbox">
 <center>
  	<table style="padding-left:10px;padding-right:10px;">
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_cod" <?php echo (isset($general_settings['hitshipo_pn_cod']) && $general_settings['hitshipo_pn_cod'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Cash on Delivery.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_email_alert" <?php echo (isset($general_settings['hitshipo_pn_email_alert']) && $general_settings['hitshipo_pn_email_alert'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> PostNord Email Notification.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_sms_alert" <?php echo (isset($general_settings['hitshipo_pn_sms_alert']) && $general_settings['hitshipo_pn_sms_alert'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> PostNord SMS Notification.</small></span></td>
	</table>
</center>
  <table style="width:100%">
  	<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		
	  <tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('Shipment Content','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_ship_content" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_ship_content'])) ? $general_settings['hitshipo_pn_ship_content'] : ''; ?>">
			</td>
			<td style="padding:10px;">
				<?php _e('Shipping Label Size (PDF)','hitshipo_pn') ?><font style="color:red;">*</font>
				<select name="hitshipo_pn_print_size" style="width:95%;padding:5px;">
					<?php foreach($print_size as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_print_size']) && ($general_settings['hitshipo_pn_print_size'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style=" width: 50%;padding:10px;">
				<?php _e('EORI or Personal Id Number','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_eori" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_eori'])) ? $general_settings['hitshipo_pn_eori'] : ''; ?>">
			</td>
			<td style=" width: 50%;padding:10px;">
				<?php _e('HS Tariff Number','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_hsn" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_hsn'])) ? $general_settings['hitshipo_pn_hsn'] : ''; ?>">
			</td>
		</tr>
		<tr <?php echo ($initial_setup) ? 'style="display:none"' : ''?>>
			<td style="padding:10px;width: 50%;">
				<?php _e('Package Type','hitshipo_pn') ?><font style="color:red;">*</font><br>
				<select name="hitshipo_pn_pac_type" style="width:95%;padding:5px;">
					<?php foreach($pac_type as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_pac_type']) && ($general_settings['hitshipo_pn_pac_type'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
			<td style="padding:10px;">
				<?php _e('Terms Of Sale','hitshipo_pn') ?><font style="color:red;">*</font><br>
				<select name="hitshipo_pn_tos" style="width:95%;padding:5px;">
					<?php foreach($tos as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_tos']) && ($general_settings['hitshipo_pn_tos'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr <?php echo ($initial_setup) ? 'style="display:none"' : ''?>>
			<td style="padding:10px;width: 50%;">
				<?php _e('TOD Condition Code','hitshipo_pn') ?><font style="color:red;">*</font>
				<select name="hitshipo_pn_tod_cc" style="width:95%;padding:5px;">
					<?php foreach($tod_cc as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_tod_cc']) && ($general_settings['hitshipo_pn_tod_cc'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
			<td style="padding:10px;">
				<?php _e('TOD Condition Code List','hitshipo_pn') ?><font style="color:red;">*</font>
				<select name="hitshipo_pn_tod_ccl" style="width:95%;padding:5px;">
					<?php foreach($tod_ccl as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_tod_ccl']) && ($general_settings['hitshipo_pn_tod_ccl'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="padding:10px;width: 50%;">
				<?php _e('Label Paper Size','hitshipo_pn') ?><font style="color:red;">*</font><br>
				<select name="hitshipo_pn_paper_size" style="width:95%;padding:5px;">
					<?php foreach($paper_size as $key => $value)
					{
						if(isset($general_settings['hitshipo_pn_paper_size']) && ($general_settings['hitshipo_pn_paper_size'] == $key))
						{
							echo "<option value=".$key." selected='true'>".$value."</option>";
						}
						else
						{
							echo "<option value=".$key.">".$value."</option>";
						}
					} ?>
				</select>
			</td>
			<td  style="padding:10px;">
				<?php _e('Email address to sent Shipping label','hitshipo_pn') ?><font style="color:red;">*</font>
				<input type="text" name="hitshipo_pn_label_email" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_label_email'])) ? $general_settings['hitshipo_pn_label_email'] : ''; ?>"><br>
				<small style="color:gray;"> While Shipi created the shipping label, It will sent the label, invoice to the given email. <br> If you don't need this thenleave it empty.</small>
			</td>
		</tr>
		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
		<!-- <center><h2 class="fs-title">Shippment Tracking</h2><br/>
  	<table style="padding-left:10px;padding-right:10px;">
		<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_uostatus" <?php echo (isset($general_settings['hitshipo_pn_uostatus']) && $general_settings['hitshipo_pn_uostatus'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Update the order status by tracking.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_trk_status_cus" <?php echo (isset($general_settings['hitshipo_pn_trk_status_cus']) && $general_settings['hitshipo_pn_trk_status_cus'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Enable tracking in user my account section.</small></span></td>
		</table>
		</center>
		<table style="width:100%">
		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table> -->
		<center <?php echo ($initial_setup) ? 'style="display:none"' : ''?>><h2 class="fs-title">Pickup</h2><br/>
  	<table style="padding-left:10px;padding-right:10px;">
		<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_pickup_automation" <?php echo (isset($general_settings['hitshipo_pn_pickup_automation']) && $general_settings['hitshipo_pn_pickup_automation'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Automatically assign pickup.</small></span></td>
		</table>
		</center><br>
		<table style="width:100%; <?php echo ($initial_setup) ? 'display:none;' : ''?>">
			<tr>
				<td style="padding:10px;">
					<?php _e('Earliest Pickup Date from label creation date?','hitshipo_pn') ?><font style="color:red;">*</font>
					<select name="hitshipo_pn_pickup_open_date" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php
						for ($i = 1; $i <= 10; $i++) {
							if(isset($general_settings['hitshipo_pn_pickup_open_date']) && $general_settings['hitshipo_pn_pickup_open_date'] == $i)
							{
								echo "<option value=".$i." selected='true'>".$i."</option>";
							}
							else
							{
								echo "<option value=".$i.">".$i."</option>";
							}
						}
						?>
					</select>
				</td>
				<td style="padding:10px;">
					<?php _e('Latest Pickup Date from label creation date?','hitshipo_pn') ?><font style="color:red;">*</font>
					<select name="hitshipo_pn_pickup_close_date" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php
						for ($i = 1; $i <= 10; $i++) {
							if(isset($general_settings['hitshipo_pn_pickup_close_date']) && $general_settings['hitshipo_pn_pickup_close_date'] == $i)
							{
								echo "<option value=".$i." selected='true'>".$i."</option>";
							}
							else
							{
								echo "<option value=".$i.">".$i."</option>";
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Earliest Pickup time','hitshipo_pn') ?><font style="color:red;">*</font>
					<select name="hitshipo_pn_pickup_open_time" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php
						for ($i = 1; $i <= 24; $i++) {
							$t = ($i < 10) ? "T0".$i.":00:00+00:00" : "T".$i.":00:00+00:00";
							if(isset($general_settings['hitshipo_pn_pickup_open_time']) && $general_settings['hitshipo_pn_pickup_open_time'] == $t)
							{
								echo "<option value=".$t." selected='true'>".$t."</option>";
							}
							else
							{
								echo "<option value=".$t.">".$t."</option>";
							}
						}
						?>
					</select>
				</td>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Latest Pickup time','hitshipo_pn') ?><font style="color:red;">*</font>
					<select name="hitshipo_pn_pickup_close_time" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php
						for ($i = 1; $i <= 24; $i++) {
							$t = ($i < 10) ? "T0".$i.":00:00+00:00" : "T".$i.":00:00+00:00";
							if(isset($general_settings['hitshipo_pn_pickup_close_time']) && $general_settings['hitshipo_pn_pickup_close_time'] == $t)
							{
								echo "<option value=".$t." selected='true'>".$t."</option>";
							}
							else
							{
								echo "<option value=".$t.">".$t."</option>";
							}
						}
						?>
					</select>
				</td>
			</tr>
		</table>

		</div>
		<table style="width:100%;">
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
		
		<?php if(isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] !=''){
			echo '<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />';
		}

		?>
		<?php if (!$initial_setup) { ?>
		<input type="button" name="next" class="next action-button" value="Next" />
 		<input type="button" name="previous" class="previous action-button" value="Previous" />
		<?php } ?>
 </fieldset>
 <?php
  }
  ?>
  <fieldset>
    <center><h2 class="fs-title">LINK Shipi</h2></center>
    <table style="width:100%;">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<div class="insetbox">
		<center>
			<img src="<?php echo plugin_dir_url(__FILE__); ?>pn.png">
			<h3 class="fs-subtitle">Shipi is performs all the operations in its own server. So it won't affect your page speed or server usage.</h3>
			<?php 
				if(!isset($general_settings['hitshipo_pn_integration_key']) || empty($general_settings['hitshipo_pn_integration_key'])){
			?>
			<input type="radio" name="shipo_link_type" id="WITHOUT" value="WITHOUT" checked>I don't have Shipi account  &nbsp; &nbsp; &nbsp;
		<input type="radio" name="shipo_link_type" id="WITH" value="WITH">I have Shipi integration key
<br><hr>
		<table class="with_shipo_acc" style="width:100%;text-align:center;display: none;">
		<tr>
			<td style="width: 50%;padding:10px;">
				<?php _e('Enter Intergation Key', 'hitshipo_pn') ?><font style="color:red;">*</font><br>
				
				<input type="text" style="width:330px;" class="intergration" id="shipo_intergration"  name="hitshipo_pn_integration_key" value="">
			</td>
		</tr>
	</table>
			<table class="without_shipo_acc" style="padding-left:10px;padding-right:10px;">
				<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_track_audit" <?php echo (isset($general_settings['hitshipo_pn_track_audit']) && $general_settings['hitshipo_pn_track_audit'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Track shipments everyday & Update the order status with Audit shipments.</small></span></td>
				<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_daily_report" <?php echo (isset($general_settings['hitshipo_pn_daily_report']) && $general_settings['hitshipo_pn_daily_report'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Daily Report.</small></span></td>
				<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshipo_pn_monthly_report" <?php echo (isset($general_settings['hitshipo_pn_monthly_report']) && $general_settings['hitshipo_pn_monthly_report'] == 'yes') ? 'checked="true"' : ''; ?> value="yes" ><small style="color:gray"> Monthly Report.</small></span></td>
			</table>
		</center>
	    <table class="without_shipo_acc" style="width:100%;text-align:center;">
			<tr><td style="padding:10px;"></td></tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Email address to signup / check the registered email.','hitshipo_pn') ?><font style="color:red;">*</font><br>
					<input type="email" id="shipo_mail" style="width:330px;" placeholder="Enter email address" name="hitshipo_pn_shipo_signup" placeholder="" value="<?php echo (isset($general_settings['hitshipo_pn_shipo_signup'])) ? $general_settings['hitshipo_pn_shipo_signup'] : ''; ?>">
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Enter Password.','hitshipo_pn') ?><font style="color:red;">*</font><br>
					<input type="password" id="shipo_pass" style="width:330px;" placeholder="Enter password" name="hitshipo_pn_shipo_signup_pass" placeholder="" value="">
				</td>
			</tr>
		</table>
	</div>

	<?php }else{
		?>
		<p style="font-size:14px;line-height:24px;">
			Site Linked Successfully. <br><br>
		It's great to have you here. Your account has been linked successfully with Shipi. <br><br>
Make your customers happier by reacting faster and handling their service requests in a timely manner, meaning higher store reviews and more revenue.</p>
		<?php
		echo '</center></div>';
	}
	?>
	<?php echo '<center>' . $error . '</center>'; ?>
	<table style="width: 100%;">
		<tr><td style="padding:10px;"><hr></td></tr>
	</table>
	<?php if(!isset($general_settings['hitshipo_pn_integration_key']) || empty($general_settings['hitshipo_pn_integration_key']) ){
	?>
		<input type="submit" name="save" class="action-button save_change" style="width:auto;" value="SAVE & START" />
	<?php	} else {	?>
		<input type="submit" name="save" class="action-button save_change" style="width:auto;" value="Save Changes" />
	<?php	} if (!$initial_setup) { if (empty($error)) { ?>
		<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php }else { ?>
		<button type="button" style="padding:11px;" name="previous" class="previous action-button"  onclick="location.reload();">Previous</button>
	<?php }} ?>
    
  </fieldset>
</form>
<center><a href="https://app.myshipi.com/support" target="_blank" style="width:auto;margin-right :20px;" class="button button-primary">Trouble in configuration? / not working? Email us.</a>
<a href="https://meetings.hubspot.com/hitshipo" target="_blank" style="width:auto;" class="button button-primary">Looking for demo ? Book your slot with our expert</a></center>
<?php } ?>
		<script>
			var current_fs, next_fs, previous_fs;
var left, opacity, scale;
var animating;
jQuery(".next").click(function () {
  if (animating) return false;
  animating = true;

  current_fs = jQuery(this).parent();
  next_fs = jQuery(this).parent().next();
  jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");
  next_fs.show();
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; 
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        scale = 1 - (1 - now) * 0.2;
        left = now * 50 + "%";
        opacity = 1 - now;
        current_fs.css({
          transform: "scale(" + scale + ")"});
        next_fs.css({ left: left, opacity: opacity });
      },
      duration: 0,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //easing: "easeInOutBack"
    }
  );
});

jQuery(".previous").click(function () {
  if (animating) return false;
  animating = true;

  current_fs = jQuery(this).parent();
  previous_fs = jQuery(this).parent().prev();
  jQuery("#progressbar li")
    .eq(jQuery("fieldset").index(current_fs))
    .removeClass("active");

  previous_fs.show();
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        scale = 0.8 + (1 - now) * 0.2;
        left = (1 - now) * 50 + "%";
        opacity = 1 - now;
        current_fs.css({ left: left });
        previous_fs.css({
          transform: "scale(" + scale + ")",
          opacity: opacity
        });
      },
      duration: 0,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //easing: "easeInOutBack"
    }
  );
});

jQuery(".submit").click(function () {
  return false;
});

jQuery(document).ready(function(){
	var pn_curr = '<?php echo $general_settings['hitshipo_pn_currency']; ?>';
	var woo_curr = '<?php echo $general_settings['hitshipo_pn_woo_currency']; ?>';
	// console.log(pn_curr);
	// console.log(woo_curr);

	if (pn_curr != null && pn_curr == woo_curr) {
		jQuery('.con_rate').each(function(){
		jQuery('.con_rate').hide();
	    });
	}else{
		if(jQuery("#auto_con").prop('checked') == true){
			jQuery('.con_rate').hide();
		}else{
			jQuery('.con_rate').each(function(){
			jQuery('.con_rate').show();
		    });
		}
	}

	jQuery('#add_box').click( function() {
		var pack_type_options = '<option value="BOX">PostNord Box</option><option value="FLY">Flyer</option><option value="YP" selected="selected" >Your Pack</option>';
		var tbody = jQuery('#box_pack_t').find('#box_pack_tbody');
		var size = tbody.find('tr').size();
		var code = '<tr class="new">\
			<td  style="padding:3px;" class="check-column"><input type="checkbox" /></td>\
			<input type="hidden" size="1" name="boxes_id[' + size + ']" value="box_id_' + size + '"/>\
			<td style="padding:3px;"><input type="text" size="25" name="boxes_name[' + size + ']" /></td>\
			<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_length[' + size + ']" /></td>\
			<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_width[' + size + ']" /></td>\
			<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_height[' + size + ']" /></td>\
			<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_box_weight[' + size + ']" /></td>\
			<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_max_weight[' + size + ']" /></td>\
			<td style="padding:3px;"><center><input type="checkbox" name="boxes_enabled[' + size + ']" /></center></td>\
			<td style="padding:3px;"><select name="boxes_pack_type[' + size + ']" >' + pack_type_options + '</select></td>\
	        </tr>';
		tbody.append( code );
		return false;
	});

	jQuery('#remove_box').click(function() {
		var tbody = jQuery('#box_pack_t').find('#box_pack_tbody');console.log(tbody);
		tbody.find('.check-column input:checked').each(function() {
			jQuery(this).closest('tr').remove().find('input').val('');
		});
		return false;
	});

	var translation = "<?php echo ( isset($general_settings['hitshipo_pn_translation']) && !empty($general_settings['hitshipo_pn_translation']) ) ? $general_settings['hitshipo_pn_translation'] : ''; ?>";
	if (translation != null && translation == "yes") {
		jQuery('#translation_key').show();
	}else{
		jQuery('#translation_key').hide();
	}

	jQuery('#hitshipo_pn_translation').click(function() {
		if (jQuery(this).is(":checked")) {
			jQuery('#translation_key').show();
		}else{
			jQuery('#translation_key').hide();
		}
	});
	jQuery('.save_change').click(function() {
		var shipo_mail = jQuery('#shipo_mail').val();
		var shipo_intergration = jQuery('#shipo_intergration').val();
			var link_type = jQuery("input[name='shipo_link_type']:checked").val();
			if (link_type === 'WITHOUT') {
				if(shipo_mail == ''){
						alert('Enter Shipi Email');
						return false;
					}
			} else {
				if(shipo_intergration == ''){
						alert('Enter Shipi intergtraion Key');
						return false;
					}
			}
			
	});

});
function changepacktype(selectbox){
	var box = document.getElementById("box_pack");
	var weight = document.getElementById("weight_based");
	var box_type = selectbox.value;
	if(box_type == "weight_based"){			
		weight.style.display = "table-row";
	}else{
		weight.style.display = "none";
	}
	if (box_type == "box") {
	    box.style.display = "block";
	  } else {
	    box.style.display = "none";
	  }
		// alert(box_type);
}
	var box_type = jQuery('#hitshipo_pn_packing_type').val();	
	// var box = document.getElementById("box_pack");
	// var weight = document.getElementById("weight_based");
	if (box_type != "box") {
		// box.style.display = "none";
		jQuery('#box_pack').hide();
	}
	if (box_type != "weight_based") {
		// weight.style.display = "none";
		jQuery('#weight_based').hide();
	}else{
		// weight.style.display = "table-row";
		jQuery('#weight_based').show();
	}

	jQuery("#auto_con").change(function() {
	    if(this.checked) {
	        jQuery('.con_rate').hide();
	    }else{
	    	jQuery('.con_rate').show();
	    }
	});
	jQuery(document).ready(function() {
			jQuery("input[name='shipo_link_type']").change(function() {
			if (jQuery(this).val() == "WITHOUT") {
				jQuery(".without_shipo_acc").show();
				jQuery(".with_shipo_acc").hide();
			} else if (jQuery(this).val() == "WITH") {
				jQuery(".without_shipo_acc").hide();
				jQuery(".with_shipo_acc").show();
			}
		});
	});

</script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/671925bb4304e3196ad6b676/1iat3mpss';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->