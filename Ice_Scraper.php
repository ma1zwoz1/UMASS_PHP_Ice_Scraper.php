<?php
//Week 9 INFO 3050.060 PHP Assignment
//Zachary Wozich
//The program is a semi complex web scraper type of PHP program and is a duplicate of the Perl/Python web scraper.
//It downloads all text based weather
//alerts for the east coast and only gives the "hazardous weather outlook" for
//the weather station in Gray Maine since I live in Southern Maine and I only
//care about winter weather alerts.
//This program then prints the output to a text file.
//The program is called by calling Ice_Scraper.php
//////////////REQUIRE simple_html_dom.php Script!////////////////////////////////////////////
require('simple_html_dom.php');
//////////////REQUIRE CURL Shell for PHP///////////////////////////////////////////////////////////////////
if (!function_exists('curl_init')){
        die('cURL is not installed. Install and try again.');
    }
//////////////////////////////Print to Screen File Download Info and Loc/////////////////////
echo 'Print Output to File>>>','Ice_Scraper_Output.txt',"<br>";
echo  "To Current Working Directory>>>",getcwd() . "<br>";
echo "<br>";
/////////////////////////////////Set Directory Info Variables for Prepare for Printing to File
$workdir = getcwd();
$file = 'Ice_Scraper_Output.txt';
$total_path = $workdir = $file;
/////////////////////////////////////////////////Visit URL Function to See if Website is Up////
function Visit($url){
       //////The website I am scraping has to see you emulate a Webrowser or will refuse connection
       $agent= 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';$ch=curl_init();
       curl_setopt ($ch, CURLOPT_URL,$url );
       curl_setopt($ch, CURLOPT_USERAGENT, $agent);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt ($ch,CURLOPT_VERBOSE,false);
       curl_setopt($ch, CURLOPT_TIMEOUT, 5);
       curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch,CURLOPT_SSLVERSION,1);////Important to set to one or page will not load
       curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
       $page=curl_exec($ch);
       //echo curl_error($ch);
       $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       curl_close($ch);
       if($httpcode>=200 && $httpcode<300) return true;
       else return false;
       
       echo $page;
       echo $httpcode;
}
////////////////////////////////////Check Domain Available Function////////////////////////////////
if (isDomainAvailible('https://www.weather.gov/wwamap/wwatxtget.php?cwa=gyx&wwa=all'))
       {
               echo "Weather.gov Domain Up and Running!" , "<br>";
       }
       else
       {
               echo "Check URL." , "<br>";
       }
       //returns true, if domain is availible, false if not
       function isDomainAvailible($domain)
       {
               //check, if a valid url is provided
               if(!filter_var($domain, FILTER_VALIDATE_URL))
               {
                       return false;
               }

               //initialize curl
               $curlInit = curl_init($domain);
               curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
               curl_setopt($curlInit,CURLOPT_HEADER,true);
               curl_setopt($curlInit,CURLOPT_NOBODY,true);
               curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

               //get answer
               $response = curl_exec($curlInit);

               curl_close($curlInit);

               if ($response) return true;

               return false;
       }
///////////////////////////////////Print Results of Visit Webpage Function//////////////// 
if (Visit("https://www.weather.gov/wwamap/wwatxtget.php?cwa=gyx&wwa=all"))
       echo "Website https://www.weather.gov/wwamap/wwatxtget.php?cwa=gyx&wwa=all", "<br>", "<br>","OK, ready for scraping", "<br>";
else
       echo "Website https://www.weather.gov/wwamap/wwatxtget.php?cwa=gyx&wwa=all", "<br>","<br>","Site Down", "<br>";
///////////////////////////////////////////Down Load Web Page Function////////////////////
function curl_download($Url){
  
    if (!function_exists('curl_init')){
        die('cURL is not installed. Install and try again.');
    }
    //////The website I am scraping has to see you emulate a Webrowser or will refuse connection
    $agent= 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_SSLVERSION,1);////Important to set to one or page will not load
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_REFERER, $Url);
    $output = curl_exec($ch);
    curl_close($ch);
  
    return $output; 
}
/////////////////////////////////////Scrape Between Function/////////////////////////////
function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }
////////////////////////////Start Scraping and Subsetting/////////////////////////////////
//Download Webpage using Curl Function
$html= curl_download('https://www.weather.gov/wwamap/wwatxtget.php?cwa=gyx&wwa=all');
//Strip Web Site Title using Scrape Between Function
$scraped_data_title = scrape_between($html, "<title>", "</title>");
//Create Blank DOM Tree
$html_base = new simple_html_dom();
// Load HTML from a string in DOM
$html_base->load($html);
//////////////////////////Run Logic and Print to file and screen//////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
if(strpos($html_base, 'National Weather Service Gray ME') == true) { 
    foreach($html_base->find('h3[plaintext*="Hazardous Weather Outlook"]')  as $h3) { //Subset Scraped String for just Hazardous Events
       echo $h3,"<br>";
            $all_data_h3 = $h3;
               file_put_contents($total_path,$all_data_h3,FILE_APPEND);
                   foreach($html_base->find('pre[plaintext*="Hazardous Weather Outlook National Weather Service Gray"]') as $pre) { //Subset Scraped String for just Gray
                      echo $scraped_data_title,"<br>";
                         echo $pre,"<br>";
                            $all_data_pre = $pre;
                              file_put_contents($total_path,$all_data_pre,FILE_APPEND);
   
                   }   
    }

 }else if ((strpos($html_base, 'National Weather Service Gray ME') == false) && (strpos($html_base,'Hazardous Weather Outlook') == true)) {
    echo "<br>","No Hazardous Events in Gray Maine At this Hour But There Are Others:", "<br>";
        $text = 'No Hazardous Events in Gray Maine At this Hour But There Are Others:';
           file_put_contents($total_path,$text,FILE_APPEND);
             foreach($html_base->find('h3[plaintext*="Hazardous Weather Outlook"]')  as $h3) { //Subset Scraped String for just Hazardous Events
                echo $h3,"<br>";
                    $all_data_h3 = $h3;
                        file_put_contents($total_path,$all_data_h3,FILE_APPEND);
                            foreach($html_base->find('pre') as $pre) {
                                echo $scraped_data_title,"<br>";
                                    echo $pre,"<br>";
                                        $all_data_pre = $pre;
                                            file_put_contents($total_path,$all_data_pre,FILE_APPEND);
   
                            }   
            }

  }else if (strpos($html_base,'Hazardous Weather Outlook') == false) {
    echo "<br>","No Hazardous Events At This Hour", "<br>";
        $text = 'No Hazardous Events At This Hour';
           file_put_contents($total_path,$text,FILE_APPEND);
             foreach($html_base->find('h3') as $h3) {
                echo $h3,"<br>";
                    $all_data_h3 = $h3;
                        file_put_contents($total_path,$all_data_h3,FILE_APPEND);
                            foreach($html_base->find('pre') as $pre) {
                                echo $scraped_data_title,"<br>";
                                    echo $pre,"<br>";
                                        $all_data_pre = $pre;
                                            file_put_contents($total_path,$all_data_pre,FILE_APPEND);
   
                            }   
            }
  }
/////////////////////////////////////ENG LOGIC////////////////////////////////////////////////
$html_base->clear(); 
unset($html_base);
////EOS////////////
?>
