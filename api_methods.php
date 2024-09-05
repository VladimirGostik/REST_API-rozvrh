<?php
require_once('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function loginToAIS($username, $password) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/system/login.pl");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'lang=sk&login_hidden=1&auth_2fa_type=no&credential_0=' . $username . '&credential_1=' . $password);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt'); // Use cookies from the file
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function getDataFromPage($pracovisko) {
    loginToAIS("xgostik", "eBa.2.ciz.fik");

    $ch = curl_init();
    $url = "https://is.stuba.sk/pracoviste/prehled_temat.pl?lang=sk;pracoviste=" . $pracovisko;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt'); // Use cookies from the file
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


function parseHtmlContent($html, $typ_prace,$pracovisko) {
    $data = array();
    $dom = new DOMDocument();
    
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $rows = $dom->getElementsByTagName('tr');
    
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        if ($cells->length >= 2) {
            $secondCellValue = $cells->item(1)->nodeValue;
              if (in_array($secondCellValue, ["DP", "DizP", "BP"])) {
                $lastCellValue = $cells->item($cells->length - 2)->nodeValue;
                
                $parts = explode("/", $lastCellValue);
                $beforeSlash = trim($parts[0]);
                $afterSlash = trim($parts[1]);
                
                if (in_array($secondCellValue, $typ_prace)) {
                    if ($afterSlash === "--" || (int)$beforeSlash < (int)$afterSlash) {

                      $link = $cells->item((int)count($cells)-3)->getElementsByTagName('a')->item(0)->getAttribute('href');
                        $id = extractId($link);
                        $abstract = getAbstractFromLink($id,$pracovisko);

                        $rowData = array(
                            $cells->item(2)->nodeValue, // "Analýza pohybu zmenšeného modelu vozidla"
                            $cells->item(3)->nodeValue, // "Šarkán, L."
                            $cells->item(4)->nodeValue, // "Ústav automobilovej mechatroniky (FEI)"
                            $cells->item(5)->nodeValue, // "B-AM"
                            $cells->item(6)->nodeValue, // "B-AM-AUME"
                            $abstract
                        );
                        $data[] = $rowData;
                    }
                }
            }
        }
    }

    return $data;
}

function extractId($url) {
    $urlParts = parse_url($url);
      if (isset($urlParts['query'])) {

      $queryString = $urlParts['query'];
      parse_str($queryString, $queryParams);
  
      if (isset($queryParams['detail'])) {
        $id = $queryParams['detail'];
        return $id;
      }
    }
      return null;
  }
  

  function getAbstractFromLink($id, $pracovisko) {
    $username_ais = "xgostik";
    $password_ais = "eBa.2.ciz.fik";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/system/login.pl");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'lang=sk&login_hidden=1&auth_2fa_type=no&credential_0=' . $username_ais . '&credential_1=' . $password_ais);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $loginResponse = curl_exec($ch);

    if (curl_errno($ch)) {
        return "Login Error: " . curl_error($ch);
    }


    curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/auth/pracoviste/prehled_temat.pl");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'detail=' . $id . '&pracovisko=' . $pracovisko . '&lang=sk');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return "Fetch Error: " . curl_error($ch);
    }

    $targetedAbstract = "";
    $dom = new DOMDocument();
    $dom->loadHTML($response);
    $abstractElement = $dom->getElementsByTagName('td'); 
    
    foreach ($abstractElement as $element) {
        if ($element->getAttribute('class') === 'odsazena' && $element->getAttribute('width') === '720' && $element->getAttribute('align') === 'justify') {
            $targetedAbstract = $element->textContent;
            break; 
        }
    }
    curl_close($ch);

    return $targetedAbstract;
}


function getThemesData($pracovisko, $typ_prace) {
    $valid_typ_prace = ["DP", "DizP", "BP"];
    if (!in_array($typ_prace, $valid_typ_prace)) {
        echo json_encode(['error' => "Invalid type of work. Valid values are 'DP', 'DizP', and 'BP'"], 400);
    }
    
    $html_content = getDataFromPage($pracovisko);
    $themes_data = parseHtmlContent($html_content, [$typ_prace],$pracovisko);
    return $themes_data;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
      if (isset($_GET['id_pracoviska'])) {
        $id_pracoviska = $_GET['id_pracoviska'];
          if (isset($_GET['typ_prace'])) {
          $typ_prace = $_GET['typ_prace'];
          $data = getThemesData($id_pracoviska, $typ_prace);
          echo json_encode($data);
          http_response_code(200);

        } else {
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Missing "typ_prace" parameter']);
          http_response_code(400);

        }
      } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Missing "id_pracoviska" parameter'], 400);
        http_response_code(400);
      }
      break;
  
      case 'POST':
          header('Content-Type: application/json');
          echo json_encode(['success' => true]);
        break;
  
    case 'DELETE':
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
      break;
  
    case 'PUT':
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
      break;
  
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid HTTP method'], 405);
      break;
  }
  
?>
