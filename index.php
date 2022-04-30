<?php
include 'simple_html_dom.php';
$PAGENUMBER = 4;
createTable();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB123";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}
$websiteUrl1 = "https://www.fish4.co.uk/jobs/$PAGENUMBER";
$html = file_get_html($websiteUrl1);
foreach ($html->find('li.lister__item') as $li)
{
    foreach ($li->find('a') as $a)
    {
        $jobLink= $a->attr['href'];
        $a = trim($jobLink, " ");
        if (!startsWith("$a", "h"))
        {
            $a = trim($jobLink);
            $source_linkForDB = "https://www.fish4.co.uk$a";

            $result = $conn->query("SELECT id FROM site_import WHERE source_link ='$source_linkForDB'");
            if ($result->num_rows == 0)
            {
                $html = file_get_html($source_linkForDB);

                $titleText = $html->find('h1');
                $titleForDb = $titleText[0]->plaintext;
                $info = array();
                foreach ($html->find('img') as $value)
                {
                    if ($tmpTel = strpos($value->attr['src'], "getasset"))
                    {
                        $logo_linkForDB = $value->attr['src'] . "<br>";
                        break;
                    }
                }
                foreach ($html->find('dd') as $element)
                {
                    $info[] = $element;
                }
                //SEARCH IN PAGE AS PLANE TEXT
                $contact_phoneForDB = "";
                $contact_websiteForDB = "";
                $contact_emailForDB = "";
                $contact_facebookForDB = "";
                $contact_twitterForDB = "";
                $contact_telegramForDB = "";
                foreach ($html->find('p') as $textInfo)
                {
                    $tmpTel = strpos($textInfo, "Tel");
                    $tmpWeb = strpos($textInfo, "www.");
                    $tmpEmail = strpos($textInfo, "@");
                    $tmpFacebook = strpos($textInfo, "facebook");
                    $tmpTwiter = strpos($textInfo, "twitter");
                    $tmpTelegram = strpos($textInfo, "telegram");

                    if ($tmpTel != null and $contact_phoneForDB == null)
                    {
                        $space = " ";
                        $afT = between('Tel: ', " ", "$textInfo$space");
                        $contact_phoneForDB = $afT;
                    }
                    if ($tmpWeb != null and $contact_websiteForDB == null)
                    {
                        $space = " ";
                        $afW = between('www', " ", "$textInfo$space");
                        $contact_websiteForDB = "www$afW";
                    }
                    if ($tmpEmail != null and $contact_emailForDB == null)
                    {
                        $space = " ";
                        $afE = between('@', " ", "$space$textInfo$space");
                        $bf = between_last(" ", '@', "$textInfo");
                        $contact_emailForDB = "$bf@$afE";
                    }
                    if ($tmpFacebook != null and $contact_facebookForDB == null)
                    {
                        $space = " ";
                        $afE = between('facebook', " ", "$space$textInfo$space");
                        $bf = between_last(" ", 'facebook', "$textInfo");
                        $contact_facebookForDB = "$bf@$afE";
                    }
                    if ($tmpTwiter != null and $contact_twitterForDB == null)
                    {
                        $space = " ";
                        $afE = between('twitter', " ", "$space$textInfo$space");
                        $bf = between_last(" ", 'twitter', "$textInfo");
                        $contact_twitterForDB = "$bf@$afE";
                    }
                    if ($tmpTelegram != null and $contact_telegramForDB == null)
                    {
                        $space = " ";
                        $afE = between('telegram', " ", "$space$textInfo$space");
                        $bf = between_last(" ", 'telegram', "$textInfo");
                        $contact_telegramForDB = "$bf@$afE";
                    }
                }
                ///END SEARCH IN TEXT
                $company_nameForDB = $info[0]->plaintext;
                $locationForDB = $info[1]->plaintext;
                $salaryForDB = substr($info[2]->plaintext, 0, 100);

                $tmpImport_date = $info[3]->plaintext;
                $tmpImp = new DateTime(trim($tmpImport_date, " "));
                $import_dateForDB = $tmpImp->format('Y.m.d');

                $tmpClosing_date = $info[4]->plaintext;
                $tmpClose = new DateTime(trim($tmpClosing_date, " "));
                $closing_dateForDB = $tmpClose->format('Y.m.d');

                $tmpArr = getSalary($info[2]);
                $salary_minForDB = $tmpArr[0];
                $salary_maxForDB = $tmpArr[1];
                $salary_currencyForDB = $tmpArr[2];
                $job_sourceForDB = '21';
                $descriptionForDB = "";
                $tmpdescriptionForDB = "";
                foreach ($html->find('.job-description') as $element)
                {
                    $tmpdescriptionForDB .= $element->plaintext;
                }
                $tmpDescriptionForDB = substr($tmpdescriptionForDB, 0, 100);
                $descriptionForDB = str_replace("'", "", $tmpDescriptionForDB);;
                $tmpExperienceForDB = $info[5]->plaintext;
                $experienceForDB = substr($tmpExperienceForDB, 0, 200);
                $conditionsForDB = $info[7]->plaintext;
                $company_linkForDB = "";
                $company_descriptionForDB = "";
                foreach ($html->find('dd') as $dd)
                {
                    foreach ($dd->find('a') as $a)
                    {
                        $tmp = strpos($a->attr['href'], "employer/");
                        if ($tmp != null)
                        {
                            $tmp2 = $a->attr['href'];
                            $company_linkForDB = "https://www.fish4.co.uk$tmp2";
                            break;
                        }
                    }
                }
                if ($company_linkForDB != "")
                {
                    $html2 = file_get_html("$company_linkForDB");
                    $tmpcompany_descriptionForDB = "";

                    foreach ($html2->find('.fix-text') as $p)
                    {
                        $tmpcompany_descriptionForDB .= $p->plaintext;
                    }
                    $tmpCompany_descriptionForDB = substr($tmpcompany_descriptionForDB, 0, 100);
                    $company_descriptionForDB = str_replace("'", "", $tmpCompany_descriptionForDB);
                }
                $contact_addresForDB = "";
                addElementsToDB($conn, $titleForDb, $locationForDB, $salaryForDB, $salary_minForDB, $salary_maxForDB, $salary_currencyForDB, $company_nameForDB, $company_linkForDB, $import_dateForDB, $contact_emailForDB, $contact_phoneForDB, $contact_addresForDB, $contact_websiteForDB, $contact_facebookForDB, $contact_twitterForDB, $contact_telegramForDB, $company_descriptionForDB, $logo_linkForDB, $source_linkForDB, $closing_dateForDB, $descriptionForDB, $job_sourceForDB, $conditionsForDB, $experienceForDB);
            }
            else
            {
                echo "already in db!" . "<br>";
            }
        }
    }
}
?>

<?php
function after($thiss, $inthat)
{
    if (!is_bool(strpos($inthat, $thiss)))
    {
        return substr($inthat, strpos($inthat, $thiss) + strlen($thiss));
    }
};
function between($thiss, $that, $inthat)
{
    return before($that, after($thiss, $inthat));
};
function before($thiss, $inthat)
{
    return substr($inthat, 0, strpos($inthat, $thiss));
};
function between_last($thiss, $that, $inthat)
{
    return after_last($thiss, before_last($that, $inthat));
};
function after_last($thiss, $inthat)
{
    if (!is_bool(strrevpos($inthat, $thiss)))
    {
        return substr($inthat, strrevpos($inthat, $thiss) + strlen($thiss));
    }
};
function before_last($thiss, $inthat)
{
    return substr($inthat, 0, strrevpos($inthat, $thiss));
};
function strrevpos($instr, $needle)
{
    $rev_pos = strpos(strrev($instr) , strrev($needle));
    if ($rev_pos === false) return false;
    else
    {
        return strlen($instr) - $rev_pos - strlen($needle);
    }
};
function startsWith($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}
?>
<?php
function getSalary($info)
{
    $finarr[] = array();
    $salaryMin = 0;
    $salaryMax = 0;
    $salary_currency = "";
    $tmpSalary = $result = str_replace('Salary', '', $info->plaintext);
    $floats[0] = null;
    $floats[1] = null;
    if (strpos($tmpSalary, 'GBP') !== false)
    {
        $salary_currency = "GBP";
        $tmpPos = strpos($tmpSalary, "-");
        if ($tmpPos != null)
        {
            $salaryMax = between('- ', ' ', "$tmpSalary");
            $salaryMin = between(' ', ' -', "$tmpSalary");
        }
        else
        {
            $salaryCount = preg_match_all('!\d+(?:\.\d+)?!', $tmpSalary, $matches);
            if (preg_match_all('!\d+(?:\.\d+)?!', $tmpSalary, $matches))
            {
                $floats = $matches[0];
            }
            $tmpsalaryMin = str_replace(',', '', $floats[0]);
            $salaryMin = substr($tmpsalaryMin, 1);
            if ($salaryCount == 2)
            {
                $salaryMax = str_replace(',', '', $floats[1]);
            }
            else
            {
                $salaryMax = $salaryMin;
            }
            $salaryForDB = substr($tmpSalary, 0, 100);
        }
    }
    else
    {
        $salaryCount = preg_match_all('!\d+(?:\,\d+)?!', $tmpSalary, $matches);
        if (preg_match_all('!\d+(?:\,\d+)?!', $tmpSalary, $matches))
        {
            $floats = $matches[0];
        }
        $salaryMin = str_replace(',', '', $floats[0]);
        $salary_currency = "£";
        if ($salaryCount == 2)
        {
            $salaryMax = str_replace(',', '', $floats[1]);
        }
        else
        {
            $salaryMax = $salaryMin;
        }
        $salaryForDB = substr($tmpSalary, 0, 100);
        
    }
    $finarr[0] = $salaryMin;
    $finarr[1] = $salaryMax;
    $finarr[2] = $salary_currency;
    return $finarr;
}
?>
<?php
function addElementsToDB($conn, $titleForDb, $locationForDB, $salaryForDB, $salary_minForDB, $salary_maxForDB, $salary_currencyForDB, $company_nameForDB, $company_linkForDB, $import_dateForDB, $contact_emailForDB, $contact_phoneForDB, $contact_addresForDB, $contact_websiteForDB, $contact_facebookForDB, $contact_twitterForDB, $contact_telegramForDB, $company_descriptionForDB, $logo_linkForDB, $source_linkForDB, $closing_dateForDB, $descriptionForDB, $job_sourceForDB, $conditionsForDB, $experienceForDB)
{

    $title = "$titleForDb";
    $location = "$locationForDB";
    $country = "United Kingdom";
    $salary = "$salaryForDB";
    $salary_min = (int)$salary_minForDB;
    $salary_max = (int)$salary_maxForDB;
    $salary_currency = "$salary_currencyForDB";

    $company_name = "$company_nameForDB";
    $company_link = "$company_linkForDB";
    $import_date = "$import_dateForDB";
    $contact_email = "$contact_emailForDB";
    $contact_phone = "$contact_phoneForDB";
    $contact_address = "$contact_addresForDB";
    $contact_website = "$contact_websiteForDB";
    $contact_facebook = "$contact_facebookForDB";
    $contact_twitter = "$contact_twitterForDB";
    $contact_telegram = "$contact_telegramForDB";
    $company_description = "$company_descriptionForDB";
    $logo_link = "$logo_linkForDB";
    $source_link = "$source_linkForDB";
    $closing_date = "$closing_dateForDB";
    $description = "$descriptionForDB";
    $job_source = "$job_sourceForDB";
    $conditions = "$conditionsForDB";
    $experience = "$experienceForDB";

    $sql = "INSERT INTO site_import(title, 
            location,country,salary,salary_min,salary_max,salary_currency,company_name,company_link,import_date,contact_email,
            contact_phone,contact_address,contact_website,contact_facebook,contact_twitter,contact_telegram,company_description,
            logo_link,source_link,closing_date,description,job_source,conditions,experience) VALUES ('$title', 
            '$location','$country','$salary','$salary_min', '$salary_max','$salary_currency','$company_name','$company_link'," . "'$import_date','$contact_email','$contact_phone','$contact_address','$contact_website','$contact_facebook','$contact_twitter'," . "'$contact_telegram','$company_description','$logo_link','$source_link','$closing_date','$description','$job_source','$conditions','$experience')";
    if (mysqli_query($conn, $sql))
    {
        echo "<h3>data stored in a database successfully." . " Please browse your localhost php my admin" . " to view the updated data</h3>";

    }
    else
    {
        echo "ERROR: Hush! Sorry $sql. " . mysqli_error($conn);

    }

};
?>
 <?php
function createTable()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB123";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    // sql to create table
    $sql = "CREATE TABLE IF NOT EXISTS `site_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `location` varchar(100) NOT NULL DEFAULT '',
  `country` varchar(100) NOT NULL DEFAULT '',
  `salary` varchar(100) NOT NULL DEFAULT '',
  `salary_min` int(11) NOT NULL DEFAULT '0',
  `salary_max` int(11) NOT NULL DEFAULT '0',
  `salary_currency` varchar(10) NOT NULL DEFAULT '',
  `company_name` varchar(100) NOT NULL DEFAULT '',
  `company_link` varchar(200) NOT NULL DEFAULT '', 
  `import_date` datetime DEFAULT NULL,
  `contact_email` varchar(100) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `contact_address` varchar(100) NOT NULL DEFAULT '',
  `contact_website` varchar(100) NOT NULL DEFAULT '',
  `contact_facebook` varchar(100) NOT NULL DEFAULT '',
  `contact_twitter` varchar(100) NOT NULL DEFAULT '',
  `contact_telegram` varchar(100) NOT NULL DEFAULT '',
  `company_description` text,
  `logo_link` varchar(200) NOT NULL DEFAULT '',
  `source_link` varchar(200) NOT NULL DEFAULT '',
  `closing_date` datetime DEFAULT NULL,
  `description` text,
  `job_source` int(11) NOT NULL DEFAULT '17',
  `conditions` varchar(200) NOT NULL DEFAULT '',
  `experience` varchar(200) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

    if ($conn->query($sql) !== true)
    {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}
?>
