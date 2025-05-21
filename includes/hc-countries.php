<?php
function hc_msg91_iso_to_flag( $iso ) {
	$iso  = strtoupper( $iso );
	$flag = '';
	for ( $i = 0; $i < strlen( $iso ); $i++ ) {
		$flag .= mb_convert_encoding( '&#' . ( 127397 + ord( $iso[ $i ] ) ) . ';', 'UTF-8', 'HTML-ENTITIES' );
	}
	return $flag;
}
function hc_msg91_get_countries_with_iso() {
	return array(
		array(
			'name' => 'Afghanistan',
			'code' => '+93',
			'iso'  => 'AF',
		),
		array(
			'name' => 'Albania',
			'code' => '+355',
			'iso'  => 'AL',
		),
		array(
			'name' => 'Algeria',
			'code' => '+213',
			'iso'  => 'DZ',
		),
		array(
			'name' => 'Andorra',
			'code' => '+376',
			'iso'  => 'AD',
		),
		array(
			'name' => 'Angola',
			'code' => '+244',
			'iso'  => 'AO',
		),
		array(
			'name' => 'Antigua and Barbuda',
			'code' => '+1-268',
			'iso'  => 'AG',
		),
		array(
			'name' => 'Argentina',
			'code' => '+54',
			'iso'  => 'AR',
		),
		array(
			'name' => 'Armenia',
			'code' => '+374',
			'iso'  => 'AM',
		),
		array(
			'name' => 'Australia',
			'code' => '+61',
			'iso'  => 'AU',
		),
		array(
			'name' => 'Austria',
			'code' => '+43',
			'iso'  => 'AT',
		),
		array(
			'name' => 'Azerbaijan',
			'code' => '+994',
			'iso'  => 'AZ',
		),
		array(
			'name' => 'Bahamas',
			'code' => '+1-242',
			'iso'  => 'BS',
		),
		array(
			'name' => 'Bahrain',
			'code' => '+973',
			'iso'  => 'BH',
		),
		array(
			'name' => 'Bangladesh',
			'code' => '+880',
			'iso'  => 'BD',
		),
		array(
			'name' => 'Barbados',
			'code' => '+1-246',
			'iso'  => 'BB',
		),
		array(
			'name' => 'Belarus',
			'code' => '+375',
			'iso'  => 'BY',
		),
		array(
			'name' => 'Belgium',
			'code' => '+32',
			'iso'  => 'BE',
		),
		array(
			'name' => 'Belize',
			'code' => '+501',
			'iso'  => 'BZ',
		),
		array(
			'name' => 'Benin',
			'code' => '+229',
			'iso'  => 'BJ',
		),
		array(
			'name' => 'Bhutan',
			'code' => '+975',
			'iso'  => 'BT',
		),
		array(
			'name' => 'Bolivia',
			'code' => '+591',
			'iso'  => 'BO',
		),
		array(
			'name' => 'Bosnia and Herzegovina',
			'code' => '+387',
			'iso'  => 'BA',
		),
		array(
			'name' => 'Botswana',
			'code' => '+267',
			'iso'  => 'BW',
		),
		array(
			'name' => 'Brazil',
			'code' => '+55',
			'iso'  => 'BR',
		),
		array(
			'name' => 'Brunei Darussalam',
			'code' => '+673',
			'iso'  => 'BN',
		),
		array(
			'name' => 'Bulgaria',
			'code' => '+359',
			'iso'  => 'BG',
		),
		array(
			'name' => 'Burkina Faso',
			'code' => '+226',
			'iso'  => 'BF',
		),
		array(
			'name' => 'Burundi',
			'code' => '+257',
			'iso'  => 'BI',
		),
		array(
			'name' => 'Cabo Verde',
			'code' => '+238',
			'iso'  => 'CV',
		),
		array(
			'name' => 'Cambodia',
			'code' => '+855',
			'iso'  => 'KH',
		),
		array(
			'name' => 'Cameroon',
			'code' => '+237',
			'iso'  => 'CM',
		),
		array(
			'name' => 'Canada',
			'code' => '+1',
			'iso'  => 'CA',
		),
		array(
			'name' => 'Central African Republic',
			'code' => '+236',
			'iso'  => 'CF',
		),
		array(
			'name' => 'Chad',
			'code' => '+235',
			'iso'  => 'TD',
		),
		array(
			'name' => 'Chile',
			'code' => '+56',
			'iso'  => 'CL',
		),
		array(
			'name' => 'China',
			'code' => '+86',
			'iso'  => 'CN',
		),
		array(
			'name' => 'Colombia',
			'code' => '+57',
			'iso'  => 'CO',
		),
		array(
			'name' => 'Comoros',
			'code' => '+269',
			'iso'  => 'KM',
		),
		array(
			'name' => 'Congo (Congo-Brazzaville)',
			'code' => '+242',
			'iso'  => 'CG',
		),
		array(
			'name' => 'Congo (Democratic Republic)',
			'code' => '+243',
			'iso'  => 'CD',
		),
		array(
			'name' => 'Costa Rica',
			'code' => '+506',
			'iso'  => 'CR',
		),
		array(
			'name' => 'Croatia',
			'code' => '+385',
			'iso'  => 'HR',
		),
		array(
			'name' => 'Cuba',
			'code' => '+53',
			'iso'  => 'CU',
		),
		array(
			'name' => 'Cyprus',
			'code' => '+357',
			'iso'  => 'CY',
		),
		array(
			'name' => 'Czech Republic',
			'code' => '+420',
			'iso'  => 'CZ',
		),
		array(
			'name' => 'Denmark',
			'code' => '+45',
			'iso'  => 'DK',
		),
		array(
			'name' => 'Djibouti',
			'code' => '+253',
			'iso'  => 'DJ',
		),
		array(
			'name' => 'Dominica',
			'code' => '+1-767',
			'iso'  => 'DM',
		),
		array(
			'name' => 'Dominican Republic',
			'code' => '+1-809',
			'iso'  => 'DO',
		),
		array(
			'name' => 'Ecuador',
			'code' => '+593',
			'iso'  => 'EC',
		),
		array(
			'name' => 'Egypt',
			'code' => '+20',
			'iso'  => 'EG',
		),
		array(
			'name' => 'El Salvador',
			'code' => '+503',
			'iso'  => 'SV',
		),
		array(
			'name' => 'Equatorial Guinea',
			'code' => '+240',
			'iso'  => 'GQ',
		),
		array(
			'name' => 'Eritrea',
			'code' => '+291',
			'iso'  => 'ER',
		),
		array(
			'name' => 'Estonia',
			'code' => '+372',
			'iso'  => 'EE',
		),
		array(
			'name' => 'Eswatini',
			'code' => '+268',
			'iso'  => 'SZ',
		),
		array(
			'name' => 'Ethiopia',
			'code' => '+251',
			'iso'  => 'ET',
		),
		array(
			'name' => 'Fiji',
			'code' => '+679',
			'iso'  => 'FJ',
		),
		array(
			'name' => 'Finland',
			'code' => '+358',
			'iso'  => 'FI',
		),
		array(
			'name' => 'France',
			'code' => '+33',
			'iso'  => 'FR',
		),
		array(
			'name' => 'Gabon',
			'code' => '+241',
			'iso'  => 'GA',
		),
		array(
			'name' => 'Gambia',
			'code' => '+220',
			'iso'  => 'GM',
		),
		array(
			'name' => 'Georgia',
			'code' => '+995',
			'iso'  => 'GE',
		),
		array(
			'name' => 'Germany',
			'code' => '+49',
			'iso'  => 'DE',
		),
		array(
			'name' => 'Ghana',
			'code' => '+233',
			'iso'  => 'GH',
		),
		array(
			'name' => 'Greece',
			'code' => '+30',
			'iso'  => 'GR',
		),
		array(
			'name' => 'Grenada',
			'code' => '+1-473',
			'iso'  => 'GD',
		),
		array(
			'name' => 'Guatemala',
			'code' => '+502',
			'iso'  => 'GT',
		),
		array(
			'name' => 'Guinea',
			'code' => '+224',
			'iso'  => 'GN',
		),
		array(
			'name' => 'Guinea-Bissau',
			'code' => '+245',
			'iso'  => 'GW',
		),
		array(
			'name' => 'Guyana',
			'code' => '+592',
			'iso'  => 'GY',
		),
		array(
			'name' => 'Haiti',
			'code' => '+509',
			'iso'  => 'HT',
		),
		array(
			'name' => 'Honduras',
			'code' => '+504',
			'iso'  => 'HN',
		),
		array(
			'name' => 'Hungary',
			'code' => '+36',
			'iso'  => 'HU',
		),
		array(
			'name' => 'Iceland',
			'code' => '+354',
			'iso'  => 'IS',
		),
		array(
			'name' => 'India',
			'code' => '+91',
			'iso'  => 'IN',
		),
		array(
			'name' => 'Indonesia',
			'code' => '+62',
			'iso'  => 'ID',
		),
		array(
			'name' => 'Iran',
			'code' => '+98',
			'iso'  => 'IR',
		),
		array(
			'name' => 'Iraq',
			'code' => '+964',
			'iso'  => 'IQ',
		),
		array(
			'name' => 'Ireland',
			'code' => '+353',
			'iso'  => 'IE',
		),
		array(
			'name' => 'Israel',
			'code' => '+972',
			'iso'  => 'IL',
		),
		array(
			'name' => 'Italy',
			'code' => '+39',
			'iso'  => 'IT',
		),
		array(
			'name' => 'Jamaica',
			'code' => '+1-876',
			'iso'  => 'JM',
		),
		array(
			'name' => 'Japan',
			'code' => '+81',
			'iso'  => 'JP',
		),
		array(
			'name' => 'Jordan',
			'code' => '+962',
			'iso'  => 'JO',
		),
		array(
			'name' => 'Kazakhstan',
			'code' => '+7',
			'iso'  => 'KZ',
		),
		array(
			'name' => 'Kenya',
			'code' => '+254',
			'iso'  => 'KE',
		),
		array(
			'name' => 'Kiribati',
			'code' => '+686',
			'iso'  => 'KI',
		),
		array(
			'name' => 'Korea, North',
			'code' => '+850',
			'iso'  => 'KP',
		),
		array(
			'name' => 'Korea, South',
			'code' => '+82',
			'iso'  => 'KR',
		),
		array(
			'name' => 'Kuwait',
			'code' => '+965',
			'iso'  => 'KW',
		),
		array(
			'name' => 'Kyrgyzstan',
			'code' => '+996',
			'iso'  => 'KG',
		),
		array(
			'name' => 'Laos',
			'code' => '+856',
			'iso'  => 'LA',
		),
		array(
			'name' => 'Latvia',
			'code' => '+371',
			'iso'  => 'LV',
		),
		array(
			'name' => 'Lebanon',
			'code' => '+961',
			'iso'  => 'LB',
		),
		array(
			'name' => 'Lesotho',
			'code' => '+266',
			'iso'  => 'LS',
		),
		array(
			'name' => 'Liberia',
			'code' => '+231',
			'iso'  => 'LR',
		),
		array(
			'name' => 'Libya',
			'code' => '+218',
			'iso'  => 'LY',
		),
		array(
			'name' => 'Liechtenstein',
			'code' => '+423',
			'iso'  => 'LI',
		),
		array(
			'name' => 'Lithuania',
			'code' => '+370',
			'iso'  => 'LT',
		),
		array(
			'name' => 'Luxembourg',
			'code' => '+352',
			'iso'  => 'LU',
		),
		array(
			'name' => 'Madagascar',
			'code' => '+261',
			'iso'  => 'MG',
		),
		array(
			'name' => 'Malawi',
			'code' => '+265',
			'iso'  => 'MW',
		),
		array(
			'name' => 'Malaysia',
			'code' => '+60',
			'iso'  => 'MY',
		),
		array(
			'name' => 'Maldives',
			'code' => '+960',
			'iso'  => 'MV',
		),
		array(
			'name' => 'Mali',
			'code' => '+223',
			'iso'  => 'ML',
		),
		array(
			'name' => 'Malta',
			'code' => '+356',
			'iso'  => 'MT',
		),
		array(
			'name' => 'Marshall Islands',
			'code' => '+692',
			'iso'  => 'MH',
		),
		array(
			'name' => 'Mauritania',
			'code' => '+222',
			'iso'  => 'MR',
		),
		array(
			'name' => 'Mauritius',
			'code' => '+230',
			'iso'  => 'MU',
		),
		array(
			'name' => 'Mexico',
			'code' => '+52',
			'iso'  => 'MX',
		),
		array(
			'name' => 'Micronesia',
			'code' => '+691',
			'iso'  => 'FM',
		),
		array(
			'name' => 'Moldova',
			'code' => '+373',
			'iso'  => 'MD',
		),
		array(
			'name' => 'Monaco',
			'code' => '+377',
			'iso'  => 'MC',
		),
		array(
			'name' => 'Mongolia',
			'code' => '+976',
			'iso'  => 'MN',
		),
		array(
			'name' => 'Montenegro',
			'code' => '+382',
			'iso'  => 'ME',
		),
		array(
			'name' => 'Morocco',
			'code' => '+212',
			'iso'  => 'MA',
		),
		array(
			'name' => 'Mozambique',
			'code' => '+258',
			'iso'  => 'MZ',
		),
		array(
			'name' => 'Myanmar',
			'code' => '+95',
			'iso'  => 'MM',
		),
		array(
			'name' => 'Namibia',
			'code' => '+264',
			'iso'  => 'NA',
		),
		array(
			'name' => 'Nauru',
			'code' => '+674',
			'iso'  => 'NR',
		),
		array(
			'name' => 'Nepal',
			'code' => '+977',
			'iso'  => 'NP',
		),
		array(
			'name' => 'Netherlands',
			'code' => '+31',
			'iso'  => 'NL',
		),
		array(
			'name' => 'New Zealand',
			'code' => '+64',
			'iso'  => 'NZ',
		),
		array(
			'name' => 'Nicaragua',
			'code' => '+505',
			'iso'  => 'NI',
		),
		array(
			'name' => 'Niger',
			'code' => '+227',
			'iso'  => 'NE',
		),
		array(
			'name' => 'Nigeria',
			'code' => '+234',
			'iso'  => 'NG',
		),
		array(
			'name' => 'North Macedonia',
			'code' => '+389',
			'iso'  => 'MK',
		),
		array(
			'name' => 'Norway',
			'code' => '+47',
			'iso'  => 'NO',
		),
		array(
			'name' => 'Oman',
			'code' => '+968',
			'iso'  => 'OM',
		),
		array(
			'name' => 'Pakistan',
			'code' => '+92',
			'iso'  => 'PK',
		),
		array(
			'name' => 'Palau',
			'code' => '+680',
			'iso'  => 'PW',
		),
		array(
			'name' => 'Panama',
			'code' => '+507',
			'iso'  => 'PA',
		),
		array(
			'name' => 'Papua New Guinea',
			'code' => '+675',
			'iso'  => 'PG',
		),
		array(
			'name' => 'Paraguay',
			'code' => '+595',
			'iso'  => 'PY',
		),
		array(
			'name' => 'Peru',
			'code' => '+51',
			'iso'  => 'PE',
		),
		array(
			'name' => 'Philippines',
			'code' => '+63',
			'iso'  => 'PH',
		),
		array(
			'name' => 'Poland',
			'code' => '+48',
			'iso'  => 'PL',
		),
		array(
			'name' => 'Portugal',
			'code' => '+351',
			'iso'  => 'PT',
		),
		array(
			'name' => 'Qatar',
			'code' => '+974',
			'iso'  => 'QA',
		),
		array(
			'name' => 'Romania',
			'code' => '+40',
			'iso'  => 'RO',
		),
		array(
			'name' => 'Russia',
			'code' => '+7',
			'iso'  => 'RU',
		),
		array(
			'name' => 'Rwanda',
			'code' => '+250',
			'iso'  => 'RW',
		),
		array(
			'name' => 'Saint Kitts and Nevis',
			'code' => '+1-869',
			'iso'  => 'KN',
		),
		array(
			'name' => 'Saint Lucia',
			'code' => '+1-758',
			'iso'  => 'LC',
		),
		array(
			'name' => 'Saint Vincent and the Grenadines',
			'code' => '+1-784',
			'iso'  => 'VC',
		),
		array(
			'name' => 'Samoa',
			'code' => '+685',
			'iso'  => 'WS',
		),
		array(
			'name' => 'San Marino',
			'code' => '+378',
			'iso'  => 'SM',
		),
		array(
			'name' => 'Sao Tome and Principe',
			'code' => '+239',
			'iso'  => 'ST',
		),
		array(
			'name' => 'Saudi Arabia',
			'code' => '+966',
			'iso'  => 'SA',
		),
		array(
			'name' => 'Senegal',
			'code' => '+221',
			'iso'  => 'SN',
		),
		array(
			'name' => 'Serbia',
			'code' => '+381',
			'iso'  => 'RS',
		),
		array(
			'name' => 'Seychelles',
			'code' => '+248',
			'iso'  => 'SC',
		),
		array(
			'name' => 'Sierra Leone',
			'code' => '+232',
			'iso'  => 'SL',
		),
		array(
			'name' => 'Singapore',
			'code' => '+65',
			'iso'  => 'SG',
		),
		array(
			'name' => 'Slovakia',
			'code' => '+421',
			'iso'  => 'SK',
		),
		array(
			'name' => 'Slovenia',
			'code' => '+386',
			'iso'  => 'SI',
		),
		array(
			'name' => 'Solomon Islands',
			'code' => '+677',
			'iso'  => 'SB',
		),
		array(
			'name' => 'Somalia',
			'code' => '+252',
			'iso'  => 'SO',
		),
		array(
			'name' => 'South Africa',
			'code' => '+27',
			'iso'  => 'ZA',
		),
		array(
			'name' => 'South Sudan',
			'code' => '+211',
			'iso'  => 'SS',
		),
		array(
			'name' => 'Spain',
			'code' => '+34',
			'iso'  => 'ES',
		),
		array(
			'name' => 'Sri Lanka',
			'code' => '+94',
			'iso'  => 'LK',
		),
		array(
			'name' => 'Sudan',
			'code' => '+249',
			'iso'  => 'SD',
		),
		array(
			'name' => 'Suriname',
			'code' => '+597',
			'iso'  => 'SR',
		),
		array(
			'name' => 'Sweden',
			'code' => '+46',
			'iso'  => 'SE',
		),
		array(
			'name' => 'Switzerland',
			'code' => '+41',
			'iso'  => 'CH',
		),
		array(
			'name' => 'Syria',
			'code' => '+963',
			'iso'  => 'SY',
		),
		array(
			'name' => 'Taiwan',
			'code' => '+886',
			'iso'  => 'TW',
		),
		array(
			'name' => 'Tajikistan',
			'code' => '+992',
			'iso'  => 'TJ',
		),
		array(
			'name' => 'Tanzania',
			'code' => '+255',
			'iso'  => 'TZ',
		),
		array(
			'name' => 'Thailand',
			'code' => '+66',
			'iso'  => 'TH',
		),
		array(
			'name' => 'Timor-Leste',
			'code' => '+670',
			'iso'  => 'TL',
		),
		array(
			'name' => 'Togo',
			'code' => '+228',
			'iso'  => 'TG',
		),
		array(
			'name' => 'Tonga',
			'code' => '+676',
			'iso'  => 'TO',
		),
		array(
			'name' => 'Trinidad and Tobago',
			'code' => '+1-868',
			'iso'  => 'TT',
		),
		array(
			'name' => 'Tunisia',
			'code' => '+216',
			'iso'  => 'TN',
		),
		array(
			'name' => 'Turkey',
			'code' => '+90',
			'iso'  => 'TR',
		),
		array(
			'name' => 'Turkmenistan',
			'code' => '+993',
			'iso'  => 'TM',
		),
		array(
			'name' => 'Tuvalu',
			'code' => '+688',
			'iso'  => 'TV',
		),
		array(
			'name' => 'Uganda',
			'code' => '+256',
			'iso'  => 'UG',
		),
		array(
			'name' => 'Ukraine',
			'code' => '+380',
			'iso'  => 'UA',
		),
		array(
			'name' => 'United Arab Emirates',
			'code' => '+971',
			'iso'  => 'AE',
		),
		array(
			'name' => 'United Kingdom',
			'code' => '+44',
			'iso'  => 'GB',
		),
		array(
			'name' => 'United States',
			'code' => '+1',
			'iso'  => 'US',
		),
		array(
			'name' => 'Uruguay',
			'code' => '+598',
			'iso'  => 'UY',
		),
		array(
			'name' => 'Uzbekistan',
			'code' => '+998',
			'iso'  => 'UZ',
		),
		array(
			'name' => 'Vanuatu',
			'code' => '+678',
			'iso'  => 'VU',
		),
		array(
			'name' => 'Vatican City',
			'code' => '+379',
			'iso'  => 'VA',
		),
		array(
			'name' => 'Venezuela',
			'code' => '+58',
			'iso'  => 'VE',
		),
		array(
			'name' => 'Vietnam',
			'code' => '+84',
			'iso'  => 'VN',
		),
		array(
			'name' => 'Yemen',
			'code' => '+967',
			'iso'  => 'YE',
		),
		array(
			'name' => 'Zambia',
			'code' => '+260',
			'iso'  => 'ZM',
		),
		array(
			'name' => 'Zimbabwe',
			'code' => '+263',
			'iso'  => 'ZW',
		),
	);
}
