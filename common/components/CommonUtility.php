<?php

namespace common\components;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use yii\base\Component;
use mdm\admin\models\UserDetails;
use yii;
use mdm\admin\components\Helper;
use app\components\SyslogTopology;

//require_once(\Yii::$app->basePath.'/vendor/phpmailer/PHPMailerAutoload.php');

require_once(\Yii::$app->basePath.'/../vendor/phpmailer/PHPMailerAutoload.php');

//$images_path = realpath(Yii::app()->basePath . '/../images');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CommonUtility extends Component {

    /**
     * 
     * @param type $header
     * @param type $data
     * @param type $fileName
     * Use for Download data in CSV Format
     * Developer: Mahesh Solanki
     */
    public static function downloadDataInCSV($header = array(), $data = array(), $fileName = 'datafile') {
        ob_get_clean();
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        $file = fopen('php://output', 'w');
        fputcsv($file, $header);
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        $file = fopen('php://output', 'w');
        exit();
    }

    /**
     * Generate and download excel
     * 
     * @param type $header
     * @param type $arraydata
     * @param string $options
     */
    public static function downloadExcel($header, $arraydata, $options = []) {
        $type = 'Excel5';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        if (!empty($options['creator'])) {
            $objPHPExcel->getProperties()->setCreator($options['creator']);
        }
        if (!empty($options['type'])) {
            $type = $options['type'];
        }
        if (!empty($options['title'])) {
            $objPHPExcel->getActiveSheet()->setTitle($options['title']);
        }
        if (!empty($header)) {
            $cell_name = 'A';
            foreach ($header as $headerName) {
                $prev_cell_name = $cell_name;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name . '1', $headerName);
                $cell_name++;
            }
            if (!empty($options['col_bg_color'])) {
                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $prev_cell_name . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($options['col_bg_color']);
            }
            if (!empty($options['col_font_bold'])) {
                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $prev_cell_name . '1')->getFont()->setBold(true);
            }
        }
        $rowNo = 1;
        if (!empty($arraydata)) {
            foreach ($arraydata as $data) {
                $cell_name = 'A';
                $rowNo++;
                foreach ($data as $key => $value) {
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name . $rowNo, $value);
                    $cell_name++;
                }
            }
        }
        if (empty($options['filename'])) {
            $fileName = 'File_' . date("Y-m-d") . '.xls';
        } else {
            $fileName = $options['filename'];
        }
        ob_get_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $type);
        $objWriter->save('php://output');
        exit(0);
    }

    /**
     * 
     * @param type $dst
     * @param type $mode
     * @param type $recursive
     * Use for Create Directory if its not Exist
     * Developer: Mahesh Solanki
     */
    public static function createDirectory($dst, $mode = null, $recursive = false) {
        if ($mode === null)
            $mode = 0777;
        $prevDir = dirname($dst);
        if ($recursive && !is_dir($dst) && !is_dir($prevDir))
            self::createDirectory(dirname($dst), $mode, true);
        $res = mkdir($dst, $mode);
        @chmod($dst, $mode);
        return $res;
    }

    private function preparePingCommand($target) {
        $commnd = '';
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            $commnd = "ping -c 2 -w 2 " . $target;
        }
        if (filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $commnd = "ping6 -c 2 -w 2 " . $target;
        }
        return $commnd;
    }

    public function ping($target) {
        $commnd = $this->preparePingCommand($target);
        if (!empty($commnd)) {
            $cmd_result = shell_exec($commnd);
        } else {
            return 'Invalid target IP';
        }
        $result = explode(",", $cmd_result);
        if (!empty($result[1])) {
            //if (!preg_match("/\s*(.*)\s+received/i", $result[1], $match) && $match[1] == '0') {
            if (preg_match("/0 received/i", $result[1])) {
                return 'Device is not rechable.';
            } else {
                return 'online';
            }
        } else {
            return 'Error Occured.';
        }
    }

    public function isRechable($target) {
        $return = FALSE;
        $status = $this->ping($target);
        if ('online' === $status) {
            $return = TRUE;
        }
        return $return;
    }

    /**
     * Convert HTML to Text
     * 
     * @param string $html
     * @param string $encoding
     * @return string
     */
    public static function convertHtmlToText($html, $encoding = 'UTF-8') {
        $html = str_replace("&nbsp;", "[[SPACE]]", $html);
        $textContent = strip_tags($html);
        $textContent = array_map('trim', explode("\n", $textContent));
        $textContent = str_replace("[[SPACE]]", chr(32), $textContent);
        $textContent = implode("\n", $textContent);
        $textContent = html_entity_decode($textContent, ENT_QUOTES, $encoding);
        return $textContent;
    }

    /**
     * Extract hostname from FQDN
     * 
     * @param string $hostname
     * @return string
     */
    public static function extractHostname($hostname) {
        $hostParts = explode(".", $hostname);
        if (isset($hostParts[0])) {
            $hostname = $hostParts[0];
        }
        return $hostname;
    }

    public function getCreaterDropdown($tabelName = '') {
        $output = [];
        if ($tabelName == 'tbl_integration_master') {
            $output = UserDetails::find()->select("CONCAT(`first_name` ,space(1), `last_name`) AS first_name, `user_id`")->join("INNER JOIN", "{$tabelName} as e ON (`user_id` = e.integrated_by)")->groupBy(['integrated_by'])->all();
            if (!empty($output)) {
                return \yii\helpers\ArrayHelper::map($output, 'user_id', 'first_name');
            }
            return \yii\helpers\ArrayHelper::map($output, null, null);
        }
        $output = UserDetails::find()->select("CONCAT(`first_name` ,space(1), `last_name`) AS first_name, `user_id`")->join("INNER JOIN", "{$tabelName} as e ON (`user_id` = e.created_by)")->groupBy(['created_by'])->all();
        if (!empty($output)) {
            return \yii\helpers\ArrayHelper::map($output, 'user_id', 'first_name');
        }
        return \yii\helpers\ArrayHelper::map($output, null, null);
    }

    public static function getCreatedByName($model, $attribute = 'created_by', $user_id = NULL) {
        if ((property_exists($model, $attribute)) || ($user_id != "" && $user_id != NULL)) {
            if ($user_id != "" && $user_id != NULL) {
                $outout = UserDetails::find()->where(['user_id' => $user_id])->one();
            } else {
                $outout = UserDetails::find()->where(['user_id' => $model->{$attribute}])->one();
            }
            if (!empty($outout)) {
                return $outout->first_name . " " . $outout->last_name;
            }
            return NULL;
        }
        return null;
    }

    public function getCountryDropdown() {
        $countryArray = [
            ['name' => 'Afghanistan', 'value' => 'Afghanistan'],
            ['name' => 'Aland Islands', 'value' => 'Aland Islands'],
            ['name' => 'Albania', 'value' => 'Albania'],
            ['name' => 'Algeria', 'value' => 'Algeria'],
            ['name' => 'American Samoa', 'value' => 'American Samoa'],
            ['name' => 'Andorra', 'value' => 'Andorra'],
            ['name' => 'Angola', 'value' => 'Angola'],
            ['name' => 'Anguilla', 'value' => 'Anguilla'],
            ['name' => 'Antarctica', 'value' => 'Antarctica'],
            ['name' => 'Antigua & Barbuda', 'value' => 'Antigua & Barbuda'],
            ['name' => 'Argentina', 'value' => 'Argentina'],
            ['name' => 'Armenia', 'value' => 'Armenia'],
            ['name' => 'Aruba', 'value' => 'Aruba'],
            ['name' => 'Ascension Island', 'value' => 'Ascension Island'],
            ['name' => 'Australia', 'value' => 'Australia'],
            ['name' => 'Austria', 'value' => 'Austria'],
            ['name' => 'Azerbaijan', 'value' => 'Azerbaijan'],
            ['name' => 'Bahamas', 'value' => 'Bahamas'],
            ['name' => 'Bahrain', 'value' => 'Bahrain'],
            ['name' => 'Bangladesh', 'value' => 'Bangladesh'],
            ['name' => 'Barbados', 'value' => 'Barbados'],
            ['name' => 'Belarus', 'value' => 'Belarus'],
            ['name' => 'Belgium', 'value' => 'Belgium'],
            ['name' => 'Belize', 'value' => 'Belize'],
            ['name' => 'Benin', 'value' => 'Benin'],
            ['name' => 'Bermuda', 'value' => 'Bermuda'],
            ['name' => 'Bhutan', 'value' => 'Bhutan'],
            ['name' => 'Bolivia', 'value' => 'Bolivia'],
            ['name' => 'Bosnia & Herzegovina', 'value' => 'Bosnia & Herzegovina'],
            ['name' => 'Botswana', 'value' => 'Botswana'],
            ['name' => 'Bouvet Island', 'value' => 'Bouvet Island'],
            ['name' => 'Brazil', 'value' => 'Brazil'],
            ['name' => 'British Indian Ocean Territory', 'value' => 'British Indian Ocean Territory'],
            ['name' => 'British Virgin Islands', 'value' => 'British Virgin Islands'],
            ['name' => 'Brunei', 'value' => 'Brunei'],
            ['name' => 'Bulgaria', 'value' => 'Bulgaria'],
            ['name' => 'Burkina Faso', 'value' => 'Burkina Faso'],
            ['name' => 'Burundi', 'value' => 'Burundi'],
            ['name' => 'Cambodia', 'value' => 'Cambodia'],
            ['name' => 'Cameroon', 'value' => 'Cameroon'],
            ['name' => 'Canada', 'value' => 'Canada'],
            ['name' => 'Canary Islands', 'value' => 'Canary Islands'],
            ['name' => 'Cape Verde', 'value' => 'Cape Verde'],
            ['name' => 'Caribbean Netherlands', 'value' => 'Caribbean Netherlands'],
            ['name' => 'Cayman Islands', 'value' => 'Cayman Islands'],
            ['name' => 'Central African Republic', 'value' => 'Central African Republic'],
            ['name' => 'Ceuta & Melilla', 'value' => 'Ceuta & Melilla'],
            ['name' => 'Chad', 'value' => 'Chad'],
            ['name' => 'Chile', 'value' => 'Chile'],
            ['name' => 'China', 'value' => 'China'],
            ['name' => 'Christmas Island', 'value' => 'Christmas Island'],
            ['name' => 'Clipperton Island', 'value' => 'Clipperton Island'],
            ['name' => 'Cocos Islands', 'value' => 'Cocos Islands'],
            ['name' => 'Colombia', 'value' => 'Colombia'],
            ['name' => 'Comoros', 'value' => 'Comoros'],
            ['name' => 'Congo (DRC)', 'value' => 'Congo (DRC)'],
            ['name' => 'Congo (Republic)', 'value' => 'Congo (Republic)'],
            ['name' => 'Cook Islands', 'value' => 'Cook Islands'],
            ['name' => 'Costa Rica', 'value' => 'Costa Rica'],
            ['name' => 'Cote d’Ivoire', 'value' => 'Cote d’Ivoire'],
            ['name' => 'Croatia', 'value' => 'Croatia'],
            ['name' => 'Cuba', 'value' => 'Cuba'],
            ['name' => 'Curacao', 'value' => 'Curacao'],
            ['name' => 'Cyprus', 'value' => 'Cyprus'],
            ['name' => 'Czech Republic', 'value' => 'Czech Republic'],
            ['name' => 'Denmark', 'value' => 'Denmark'],
            ['name' => 'Diego Garcia', 'value' => 'Diego Garcia'],
            ['name' => 'Djibouti', 'value' => 'Djibouti'],
            ['name' => 'Dominica', 'value' => 'Dominica'],
            ['name' => 'Dominican Republic', 'value' => 'Dominican Republic'],
            ['name' => 'Ecuador', 'value' => 'Ecuador'],
            ['name' => 'Egypt', 'value' => 'Egypt'],
            ['name' => 'El Salvador', 'value' => 'El Salvador'],
            ['name' => 'Equatorial Guinea', 'value' => 'Equatorial Guinea'],
            ['name' => 'Eritrea', 'value' => 'Eritrea'],
            ['name' => 'Estonia', 'value' => 'Estonia'],
            ['name' => 'Ethiopia', 'value' => 'Ethiopia'],
            ['name' => 'Falkland Islands', 'value' => 'Falkland Islands'],
            ['name' => 'Faroe Islands', 'value' => 'Faroe Islands'],
            ['name' => 'Fiji', 'value' => 'Fiji'],
            ['name' => 'Finland', 'value' => 'Finland'],
            ['name' => 'France', 'value' => 'France'],
            ['name' => 'French Guiana', 'value' => 'French Guiana'],
            ['name' => 'French Polynesia', 'value' => 'French Polynesia'],
            ['name' => 'French Southern Territories', 'value' => 'French Southern Territories'],
            ['name' => 'Gabon', 'value' => 'Gabon'],
            ['name' => 'Gambia', 'value' => 'Gambia'],
            ['name' => 'Georgia', 'value' => 'Georgia'],
            ['name' => 'Germany', 'value' => 'Germany'],
            ['name' => 'Ghana', 'value' => 'Ghana'],
            ['name' => 'Gibraltar', 'value' => 'Gibraltar'],
            ['name' => 'Greece', 'value' => 'Greece'],
            ['name' => 'Greenland', 'value' => 'Greenland'],
            ['name' => 'Grenada', 'value' => 'Grenada'],
            ['name' => 'Guadeloupe', 'value' => 'Guadeloupe'],
            ['name' => 'Guam', 'value' => 'Guam'],
            ['name' => 'Guatemala', 'value' => 'Guatemala'],
            ['name' => 'Guernsey', 'value' => 'Guernsey'],
            ['name' => 'Guinea', 'value' => 'Guinea'],
            ['name' => 'Guinea-Bissau', 'value' => 'Guinea-Bissau'],
            ['name' => 'Guyana', 'value' => 'Guyana'],
            ['name' => 'Haiti', 'value' => 'Haiti'],
            ['name' => 'Heard & McDonald Islands', 'value' => 'Heard & McDonald Islands'],
            ['name' => 'Honduras', 'value' => 'Honduras'],
            ['name' => 'Hong Kong', 'value' => 'Hong Kong'],
            ['name' => 'Hungary', 'value' => 'Hungary'],
            ['name' => 'Iceland', 'value' => 'Iceland'],
            ['name' => 'India', 'value' => 'India'],
            ['name' => 'Indonesia', 'value' => 'Indonesia'],
            ['name' => 'Iran', 'value' => 'Iran'],
            ['name' => 'Iraq', 'value' => 'Iraq'],
            ['name' => 'Ireland', 'value' => 'Ireland'],
            ['name' => 'Isle of Man', 'value' => 'Isle of Man'],
            ['name' => 'Israel', 'value' => 'Israel'],
            ['name' => 'Italy', 'value' => 'Italy'],
            ['name' => 'Jamaica', 'value' => 'Jamaica'],
            ['name' => 'Japan', 'value' => 'Japan'],
            ['name' => 'Jersey', 'value' => 'Jersey'],
            ['name' => 'Jordan', 'value' => 'Jordan'],
            ['name' => 'Kazakhstan', 'value' => 'Kazakhstan'],
            ['name' => 'Kenya', 'value' => 'Kenya'],
            ['name' => 'Kiribati', 'value' => 'Kiribati'],
            ['name' => 'Kosovo', 'value' => 'Kosovo'],
            ['name' => 'Kuwait', 'value' => 'Kuwait'],
            ['name' => 'Kyrgyzstan', 'value' => 'Kyrgyzstan'],
            ['name' => 'Laos', 'value' => 'Laos'],
            ['name' => 'Latvia', 'value' => 'Latvia'],
            ['name' => 'Lebanon', 'value' => 'Lebanon'],
            ['name' => 'Lesotho', 'value' => 'Lesotho'],
            ['name' => 'Liberia', 'value' => 'Liberia'],
            ['name' => 'Libya', 'value' => 'Libya'],
            ['name' => 'Liechtenstein', 'value' => 'Liechtenstein'],
            ['name' => 'Lithuania', 'value' => 'Lithuania'],
            ['name' => 'Luxembourg', 'value' => 'Luxembourg'],
            ['name' => 'Macau', 'value' => 'Macau'],
            ['name' => 'Macedonia', 'value' => 'Macedonia'],
            ['name' => 'Madagascar', 'value' => 'Madagascar'],
            ['name' => 'Malawi', 'value' => 'Malawi'],
            ['name' => 'Malaysia', 'value' => 'Malaysia'],
            ['name' => 'Maldives', 'value' => 'Maldives'],
            ['name' => 'Mali', 'value' => 'Mali'],
            ['name' => 'Malta', 'value' => 'Malta'],
            ['name' => 'Marshall Islands', 'value' => 'Marshall Islands'],
            ['name' => 'Martinique', 'value' => 'Martinique'],
            ['name' => 'Mauritania', 'value' => 'Mauritania'],
            ['name' => 'Mauritius', 'value' => 'Mauritius'],
            ['name' => 'Mayotte', 'value' => 'Mayotte'],
            ['name' => 'Mexico', 'value' => 'Mexico'],
            ['name' => 'Micronesia', 'value' => 'Micronesia'],
            ['name' => 'Moldova', 'value' => 'Moldova'],
            ['name' => 'Monaco', 'value' => 'Monaco'],
            ['name' => 'Mongolia', 'value' => 'Mongolia'],
            ['name' => 'Montenegro', 'value' => 'Montenegro'],
            ['name' => 'Montserrat', 'value' => 'Montserrat'],
            ['name' => 'Morocco', 'value' => 'Morocco'],
            ['name' => 'Mozambique', 'value' => 'Mozambique'],
            ['name' => 'Myanmar', 'value' => 'Myanmar'],
            ['name' => 'Namibia', 'value' => 'Namibia'],
            ['name' => 'Nauru', 'value' => 'Nauru'],
            ['name' => 'Nepal', 'value' => 'Nepal'],
            ['name' => 'Netherlands', 'value' => 'Netherlands'],
            ['name' => 'New Caledonia', 'value' => 'New Caledonia'],
            ['name' => 'New Zealand', 'value' => 'New Zealand'],
            ['name' => 'Nicaragua', 'value' => 'Nicaragua'],
            ['name' => 'Niger', 'value' => 'Niger'],
            ['name' => 'Nigeria', 'value' => 'Nigeria'],
            ['name' => 'Niue', 'value' => 'Niue'],
            ['name' => 'Norfolk Island', 'value' => 'Norfolk Island'],
            ['name' => 'Northern Mariana Islands', 'value' => 'Northern Mariana Islands'],
            ['name' => 'North Korea', 'value' => 'North Korea'],
            ['name' => 'Norway', 'value' => 'Norway'],
            ['name' => 'Oman', 'value' => 'Oman'],
            ['name' => 'Pakistan', 'value' => 'Pakistan'],
            ['name' => 'Palau', 'value' => 'Palau'],
            ['name' => 'Palestine', 'value' => 'Palestine'],
            ['name' => 'Panama', 'value' => 'Panama'],
            ['name' => 'Papua New Guinea', 'value' => 'Papua New Guinea'],
            ['name' => 'Paraguay', 'value' => 'Paraguay'],
            ['name' => 'Peru', 'value' => 'Peru'],
            ['name' => 'Philippines', 'value' => 'Philippines'],
            ['name' => 'Pitcairn Islands', 'value' => 'Pitcairn Islands'],
            ['name' => 'Poland', 'value' => 'Poland'],
            ['name' => 'Portugal', 'value' => 'Portugal'],
            ['name' => 'Puerto Rico', 'value' => 'Puerto Rico'],
            ['name' => 'Qatar', 'value' => 'Qatar'],
            ['name' => 'Reunion', 'value' => 'Reunion'],
            ['name' => 'Romania', 'value' => 'Romania'],
            ['name' => 'Russia', 'value' => 'Russia'],
            ['name' => 'Rwanda', 'value' => 'Rwanda'],
            ['name' => 'Samoa', 'value' => 'Samoa'],
            ['name' => 'San Marino', 'value' => 'San Marino'],
            ['name' => 'Sao Tome & Principe', 'value' => 'Sao Tome & Principe'],
            ['name' => 'Saudi Arabia', 'value' => 'Saudi Arabia'],
            ['name' => 'Senegal', 'value' => 'Senegal'],
            ['name' => 'Serbia', 'value' => 'Serbia'],
            ['name' => 'Seychelles', 'value' => 'Seychelles'],
            ['name' => 'Sierra Leone', 'value' => 'Sierra Leone'],
            ['name' => 'Singapore', 'value' => 'Singapore'],
            ['name' => 'Sint Maarten', 'value' => 'Sint Maarten'],
            ['name' => 'Slovakia (Slovensko)', 'value' => 'Slovakia (Slovensko)'],
            ['name' => 'Slovenia (Slovenija)', 'value' => 'Slovenia (Slovenija)'],
            ['name' => 'Solomon Islands', 'value' => 'Solomon Islands'],
            ['name' => 'Somalia', 'value' => 'Somalia'],
            ['name' => 'South Africa', 'value' => 'South Africa'],
            ['name' => 'South Georgia & South Sandwich Islands', 'value' => 'South Georgia & South Sandwich Islands'],
            ['name' => 'South Korea', 'value' => 'South Korea'],
            ['name' => 'South Sudan', 'value' => 'South Sudan'],
            ['name' => 'Spain', 'value' => 'Spain'],
            ['name' => 'Sri Lanka', 'value' => 'Sri Lanka'],
            ['name' => 'St. Barthelemy', 'value' => 'St. Barthelemy'],
            ['name' => 'St. Helena', 'value' => 'St. Helena'],
            ['name' => 'St. Kitts & Nevis', 'value' => 'St. Kitts & Nevis'],
            ['name' => 'St. Lucia', 'value' => 'St. Lucia'],
            ['name' => 'St. Martin', 'value' => 'St. Martin'],
            ['name' => 'St. Pierre & Miquelon', 'value' => 'St. Pierre & Miquelon'],
            ['name' => 'St. Vincent & Grenadines', 'value' => 'St. Vincent & Grenadines'],
            ['name' => 'Sudan', 'value' => 'Sudan'],
            ['name' => 'Suriname', 'value' => 'Suriname'],
            ['name' => 'Svalbard & Jan Mayen', 'value' => 'Svalbard & Jan Mayen'],
            ['name' => 'Swaziland', 'value' => 'Swaziland'],
            ['name' => 'Sweden', 'value' => 'Sweden'],
            ['name' => 'Switzerland', 'value' => 'Switzerland'],
            ['name' => 'Syria', 'value' => 'Syria'],
            ['name' => 'Taiwan', 'value' => 'Taiwan'],
            ['name' => 'Tajikistan', 'value' => 'Tajikistan'],
            ['name' => 'Tanzania', 'value' => 'Tanzania'],
            ['name' => 'Thailand', 'value' => 'Thailand'],
            ['name' => 'Timor-Leste', 'value' => 'Timor-Leste'],
            ['name' => 'Togo', 'value' => 'Togo'],
            ['name' => 'Tokelau', 'value' => 'Tokelau'],
            ['name' => 'Tonga', 'value' => 'Tonga'],
            ['name' => 'Trinidad & Tobago', 'value' => 'Trinidad & Tobago'],
            ['name' => 'Tristan da Cunha', 'value' => 'Tristan da Cunha'],
            ['name' => 'Tunisia', 'value' => 'Tunisia'],
            ['name' => 'Turkey', 'value' => 'Turkey'],
            ['name' => 'Turkmenistan', 'value' => 'Turkmenistan'],
            ['name' => 'Turks & Caicos Islands', 'value' => 'Turks & Caicos Islands'],
            ['name' => 'Tuvalu', 'value' => 'Tuvalu'],
            ['name' => 'U.S. Outlying Islands', 'value' => 'U.S. Outlying Islands'],
            ['name' => 'U.S. Virgin Islands', 'value' => 'U.S. Virgin Islands'],
            ['name' => 'Uganda', 'value' => 'Uganda'],
            ['name' => 'Ukraine', 'value' => 'Ukraine'],
            ['name' => 'United Arab Emirates', 'value' => 'United Arab Emirates'],
            ['name' => 'United Kingdom', 'value' => 'United Kingdom'],
            ['name' => 'United States', 'value' => 'United States'],
            ['name' => 'Uruguay', 'value' => 'Uruguay'],
            ['name' => 'Uzbekistan', 'value' => 'Uzbekistan'],
            ['name' => 'Vanuatu', 'value' => 'Vanuatu'],
            ['name' => 'Vatican City', 'value' => 'Vatican City'],
            ['name' => 'Venezuela', 'value' => 'Venezuela'],
            ['name' => 'Vietnam', 'value' => 'Vietnam'],
            ['name' => 'Wallis & Futuna', 'value' => 'Wallis & Futuna'],
            ['name' => 'Western Sahara', 'value' => 'Western Sahara'],
            ['name' => 'Yemen', 'value' => 'Yemen'],
            ['name' => 'Zambia', 'value' => 'Zambia'],
            ['name' => 'Zimbabwe', 'value' => 'Zimbabwe'],
        ];
        return \yii\helpers\ArrayHelper::map($countryArray, 'name', 'value');
    }

    // Top menu and left menu hide
    public static function validateUserRoutes($route = NULL) {
        if (!empty($route) && Helper::checkRoute($route)) {
            return TRUE;
        }
        return FALSE;
    }

    public static function validateUserRole($route = NULL) {
        if (!empty($route) && Helper::checkRoute($route)) {
            return TRUE;
        }
        return FALSE;
    }

    public function getDsnAttribute($name, $dsn) {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    public static function siteSelectionLogic($hostname) {
        ini_set('max_execution_time', 0);
        ini_set('xdebug.max_nesting_level', 500);
        $model = new SyslogTopology();
        echo "<pre>";
        $model->searchHostname = $hostname;
        $getData = $model->topology();
        print_r($model);
        $nodeData = $model->nodes;
        $linksData = $model->links;
        $isPar = 0;
        foreach ($nodeData as $key => $value) {
            if (self::getDeviceType($value['hostname']) == 'PAR') {
                $isPar++;
            }
        }

        $nextSearchSideOne = false;
        $nextSearchSideTwo = false;
        $nextSearchDownSide = false;
        $masterData = array();

        if ($isPar == 2) {
            $isCompleteRing = true;
        } else {
            $isCompleteRing = false;
        }

        $masterData['selectedPrimaryHost'] = $model->searchHostname;
        $masterData['isCompleteRing'] = $isCompleteRing;
        $masterData['foundParSideOne'] = false;
        $masterData['foundParSideTwo'] = false;
        $masterData['sideOne'] = array();
        $masterData['sideTwo'] = array();
        $masterData['downSide'] = array();
        $masterData['effectedDevice'][] = $model->searchHostname;
        //OR-07515
        //=$tempData = self::getDownTree($linksData);
        foreach ($linksData as $key => $value) {
            if ($value['linkColor'] == 'grey') {
                if (self::getDeviceType($value['target']) == 'ESR' && $value['source'] == $model->searchHostname) {
                    $masterData['downSide'][] = $value['target'];
                    $nextSearchDownSide = $value['target'];
                    continue;
                }
                if ($nextSearchDownSide && $nextSearchDownSide == $value['source']) {
                    $masterData['downSide'][] = $value['target'];
                    $nextSearchDownSide = $value['target'];
                }
                continue;
            }

            if (!$nextSearchSideOne && $value['source'] == $model->searchHostname) {
                if (self::getDeviceType($value['target']) == "ESR") {
                    $masterData['sideOne'][] = $value['target'];
                    $nextSearchSideOne = $value['target'];
                } else {
                    if (self::getDeviceType($value['target']) == "PAR") {
                        if (!$masterData['foundParSideOne']) {
                            $masterData['foundParSideOne'] = true;
                            $masterData['sideOneParHost'] = $value['target'];
                        } else {
                            $masterData['foundParSideTwo'] = true;
                            $masterData['sideTwoParHost'] = $value['target'];
                        }
                    }
                }
                continue;
            }



            if ($nextSearchSideOne && $value['source'] == $model->searchHostname) {

                if (self::getDeviceType($value['target']) == "ESR") {
                    $masterData['sideTwo'][] = $value['target'];
                    $nextSearchSideTwo = $value['target'];
                } else {

                    if (self::getDeviceType($value['target']) == "PAR") {
                        $masterData['foundParSideTwo'] = true;
                        $masterData['sideTwoParHost'] = $value['target'];
                    }
                }
                continue;
            }

            if ($nextSearchSideOne && $value['source'] == $nextSearchSideOne) {
                if (self::getDeviceType($value['target']) == "ESR") {
                    $masterData['sideOne'][] = $value['target'];
                    $nextSearchSideOne = $value['target'];
                } else {
                    if (self::getDeviceType($value['target']) == "PAR") {
                        if (!$masterData['foundParSideOne']) {
                            $masterData['foundParSideOne'] = true;
                            $masterData['sideOneParHost'] = $value['target'];
                        } else {
                            $masterData['foundParSideTwo'] = true;
                            $masterData['sideTwoParHost'] = $value['target'];
                        }
                    }
                }
                continue;
            }

            if ($nextSearchSideTwo && $value['source'] == $nextSearchSideTwo) {

                if (self::getDeviceType($value['target']) == "ESR") {
                    $masterData['sideTwo'][] = $value['target'];
                    $nextSearchSideTwo = $value['target'];
                } else {
                    if (self::getDeviceType($value['target']) == "PAR") {
                        $masterData['foundParSideTwo'] = true;
                        $masterData['sideTwoParHost'] = $value['target'];
                    }
                }
                continue;
            }
        }

        if (!$masterData['foundParSideOne'] && isset($masterData['sideOne'])) {
            $masterData['effectedDevice'] = array_merge($masterData['effectedDevice'], $masterData['sideOne']);
        }

        if (!$masterData['foundParSideTwo'] && isset($masterData['sideTwo'])) {
            $masterData['effectedDevice'] = array_merge($masterData['effectedDevice'], $masterData['sideTwo']);
        }

        if (isset($masterData['downSide'])) {
            $masterData['effectedDevice'] = array_merge($masterData['effectedDevice'], $masterData['downSide']);
        }

        return $masterData;
    }

    public static function getDownTree($linksData, $sourceHost) {
        $downsideData = array();
        $nextDownSideSource = false;
        $tempData = array_column($linksData, "source");

        print_r($tempData);
        exit();
        foreach ($linksData as $key => $value) {
            if ($value['source'] == $sourceHost) {
                $downsideData[] = $value['target'];
                $nextDownSideSource = $value['target'];
            }

            if ($nextDownSideSource && $nextDownSideSource == $value['source']) {
                
            }
        }
    }

    public static function getDeviceType($hostname) {
        return trim(substr($hostname, 8, 3));
    }

    public static function getDeviceTypeName($device_type) {

        switch ($device_type) {
            case 'PAR':
                $device_name = "AG1";
                break;
            case 'ESR':
                $device_name = "CSS";
                break;
            default:
                $device_name = $device_type;
                break;
        }
        return trim($device_name);
    }

    public function getCssYmlTemplateStructure($fileHeader, $commandHeader, $commandArr, $dir) {
        $commands = NULL;
        $commandBody = NULL;
        $ansibleHeader = "---" . PHP_EOL . "- name: {$fileHeader}" . PHP_EOL . "  hosts: devices" . PHP_EOL . "  gather_facts: no" . PHP_EOL . "  connection: local" . PHP_EOL . PHP_EOL . "  tasks: " . PHP_EOL . "    - file: " . PHP_EOL . "       path:\"" . $dir . "\"" . PHP_EOL . "       state: directory" . PHP_EOL . "       mode: 0777" . PHP_EOL . PHP_EOL;
        $ymlContent = $ansibleHeader . "    - name: {$commandHeader}" . PHP_EOL . "      eos_command:" . PHP_EOL . "        commands:" . PHP_EOL;
        foreach ($commandArr as $key => $arr) {
            $commands .= "          - " . $arr['command'] . PHP_EOL;
            $commandBody .= "    - name: Save the output to a file " . $arr['name'] . PHP_EOL . "      copy:" . PHP_EOL . "        content: \"{{ cmd.stdout[" . $key . "] }}\"" . PHP_EOL . "        dest: \"" . $dir . "/" . $arr['name'] . "\"" . PHP_EOL . "      ignore_errors: yes" . PHP_EOL . PHP_EOL;
        }
        $commandsFooter = "        provider: \"{{ cli }}\"" . PHP_EOL . "      register: cmd" . PHP_EOL . "      ignore_errors: yes" . PHP_EOL . PHP_EOL;
        $ymlContent = $ymlContent . $commands . $commandsFooter . $commandBody . "...";

        return $ymlContent;
    }

    public static function writeLog($filePath, $logs) {
        if (is_array($logs) || is_object($logs)) {
            $logs = json_encode($logs, JSON_PRETTY_PRINT);
        }
        $logs = PHP_EOL . date("Y-m-d H:i:s") . " :- " . $logs . PHP_EOL;

        if (file_put_contents($filePath, $logs, FILE_APPEND)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getDeviceTypeByCode($code) {
        $type = trim($code);
        $device_type = Yii::$app->getModule('iosUpgrade')->params['all_device_type'];
        if (isset($device_type[$type]) && $device_type[$type] != '')
            return $device_type[$type];
    }

    public static function batchInsert($table, $column, $data) {
        if (!empty($data)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $res = Yii::$app->db->createCommand()->batchInsert($table, $column, $data)->execute();
                $transaction->commit();
                Yii::$app->session->setFlash("success", "Data Added successfully.");
            } catch (\Exception $ex) {
                $transaction->rollBack();
                Yii::$app->session->setFlash("error", $ex->getMessage());
            }
        } else {
            Yii::$app->session->setFlash("error", "Enter the data");
        }
    }

    public static function reOpenDbConnection($dbName = 'db') {
        try {
            \Yii::$app->$dbName->createCommand("DO 1")->execute();
        } catch (\yii\db\Exception $e) {
            \Yii::$app->$dbName->close();
            \Yii::$app->$dbName->open();
        }
    }

    public static function getLoopback999($ipv4 = '') {
        $loopback999_ip6 = "";
        $ipv4 = trim($ipv4);
        $mgmtBlock = FALSE;
        if (!empty($ipv4)) {
            //tbl_all_devices
            $loopback999Row = Yii::app()->db->createCommand("SELECT DISTINCT(Loopback999_ipv6) Loopback999_ipv6 FROM tbl_all_devices WHERE Loopback999_ipv6 <> '' AND Loopback0 = '{$ipv4}'   LIMIT 1")->queryRow();
            if (is_array($loopback999Row) && !empty(array_filter($loopback999Row))) {
                $loopback999_ip6 = $loopback999Row['Loopback999_ipv6'];
            }
            //ndd_output_master
            if (trim($loopback999_ip6) == '') {
                $loopback999Row = Yii::app()->db->createCommand("SELECT DISTINCT(SUBSTRING_INDEX(t1.loopback999_ipv6,'/',1)) Loopback999_ipv6 FROM ndd_output_master t1 INNER JOIN ndd_request_master t2 ON t1.enode_b_sapid = t2.sapid AND t1.request_id = t2.request_id WHERE t2.is_disabled = 0 AND  loopback0_ipv4 = '{$ipv4}' LIMIT 1")->queryRow();
                if (is_array($loopback999Row) && !empty(array_filter($loopback999Row))) {
                    $loopback999_ip6 = $loopback999Row['Loopback999_ipv6'];
                }
            }
            //ndd_core_ip_master
            if (trim($loopback999_ip6) == '') {
                $loopback999Row = Yii::app()->db->createCommand("SELECT DISTINCT(loopback0_ipv6) Loopback999_ipv6 FROM ndd_core_ip_master WHERE loopback0_ipv6 <> '' AND loopback0_ipv4 = '{$ipv4}'  LIMIT 1")->queryRow();
                if (is_array($loopback999Row) && !empty(array_filter($loopback999Row))) {
                    $loopback999_ip6 = $loopback999Row['Loopback999_ipv6'];
                }
            }
        }
        if (!empty($loopback999_ip6) && !$mgmtBlock) {
            $loopbackTmp = str_replace(".", ":", $ipv4);
            if (preg_match('/' . $loopbackTmp . '/', $loopback999_ip6)) {
                return $loopback999_ip6;
            } else {
                return '';
            }
        } else {
            return $loopback999_ip6;
        }
    }

    public static function sendmailWithAttachment($to, $to_name, $from, $from_name, $subject, $message, $attachments = null, $cc = '', $cc_name = '', $replyto = '', $bcc = '', $bcc_name = '') {

        //print $cc;
        //print "test..";
        try {
            $mail = new \PHPMailer;
            $mail->IsSMTP();
            $mail->Host = '10.137.32.112'; //'mail.rjilauto.com';
            $mail->Port = 25;
            $mail->SMTPAuth = true;
//            $mail->SMTPSecure = 'ssl';
            $mail->Username = 'ndd-css';
            $mail->Password = 'cisco123';
            $mail->Timeout = 3600;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            if (!empty($replyto)) {
                $mail->AddReplyTo($replyto, 'User');
                $autoReply = 0;
            } else {
                $autoReply = 1;
            }
            $mail->SetFrom($from, $from_name, $autoReply);
            $mail->Subject = $subject;
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            $mail->MsgHTML($message);

            if (is_array($to)) {
                foreach ($to as $toData) {
                    $mail->AddAddress($toData['email'], $toData['name']);
                }
            } else {
                $mail->AddAddress($to, $to_name);
            }

            if (is_array($cc)) {
                foreach ($cc as $ccData) {
                    $mail->AddCC($ccData['email'], $ccData['name']);
                }
            } else {
                $mail->AddCC($cc, $cc_name);
            }


            if (is_array($bcc)) {
                foreach ($bcc as $bccData) {
                    $mail->AddBCC($bccData['email'], $bccData['name']);
                }
            } else {
                $mail->AddBCC($bcc, $bcc_name);
            }

            if (!empty($attachments)) {
                foreach ($attachments as $att_name => $file_path) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $att_name = (is_integer($att_name) || trim($att_name) === '') ? basename($file_path) : $att_name;
                    $mail->addAttachment($file_path, $att_name, 'base64', finfo_file($finfo, $file_path));
                }
            }
            $mail->SMTPDebug = 2;
            return $mail->Send();
        } catch (phpmailerException $e) {
            Yii::log($e->errorMessage()); //Pretty error messages from PHPMailer
        }
        return false;
    }

    public static function checkUserDeviceSshCredentials($userID = NULL) {
        if (empty($userID)) {
            $userID = \Yii::$app->user->id;
        }
        $output = \app\models\DeviceCredentials::findOne(["created_by" => $userID, "protocol" => "ssh"]);
        if (!empty($output)) {
            return TRUE;
        }
        return FALSE;
    }

}
