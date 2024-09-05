<?php
require_once('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
  $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo $e->getMessage();
}

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
    // Login to AIS
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

    // Create a new DOMDocument
    $dom = new DOMDocument();
    
    // Set parameters for HTML manipulation
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    // Get all table rows
    $rows = $dom->getElementsByTagName('tr');
    
    // Iterate over each table row
    foreach ($rows as $row) {
        // Get all cells in the row
        $cells = $row->getElementsByTagName('td');
        
        // Check if the row contains at least two cells
        if ($cells->length >= 2) {
            // Get the value of the second cell
            $secondCellValue = $cells->item(1)->nodeValue;
            
            // Check if the second cell value matches "DP", "DizP", or "BP"
            if (in_array($secondCellValue, ["DP", "DizP", "BP"])) {
                // Get the value of the last cell
                $lastCellValue = $cells->item($cells->length - 2)->nodeValue;
                
                // Split the value based on the "/"
                $parts = explode("/", $lastCellValue);
                
                // Check if the second part contains "--"
                $beforeSlash = trim($parts[0]);
                $afterSlash = trim($parts[1]);
                
                if (in_array($secondCellValue, $typ_prace)) {
                    if ($afterSlash === "--" || (int)$beforeSlash < (int)$afterSlash) {
                        // Add only specific values to $rowData
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
                        
                        // Add the row data to the result array
                        $data[] = $rowData;
                    }
                }
            }
        }
    }

    return $data;
}

function extractId($url) {
    // Parse the URL using parse_url()
    $urlParts = parse_url($url);
  
    // Check if the 'query' component exists
    if (isset($urlParts['query'])) {
      // Extract the query string
      $queryString = $urlParts['query'];
  
      // Parse the query string into key-value pairs using parse_str()
      parse_str($queryString, $queryParams);
  
      // Check if the 'detail' key exists in the query parameters
      if (isset($queryParams['detail'])) {
        // Extract the ID from the 'detail' value
        $id = $queryParams['detail'];
  
        // Return the extracted ID
        return $id;
      }
    }
  
    // Return null if the ID is not found
    return null;
  }
  

  function getAbstractFromLink($id, $pracovisko) {
    // Replace with your actual AIS credentials
    $username_ais = "xgostik";
    $password_ais = "eBa.2.ciz.fik";

    // Initialize cURL
    $ch = curl_init();

    // Login to AIS
    curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/system/login.pl");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'lang=sk&login_hidden=1&auth_2fa_type=no&credential_0=' . $username_ais . '&credential_1=' . $password_ais);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt'); // Store cookies in a file
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $loginResponse = curl_exec($ch);

    // Check for cURL errors after login
    if (curl_errno($ch)) {
        return "Login Error: " . curl_error($ch);
    }

    // Check for successful login (replace with your logic)

    // Redirect to the target page after login
    curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/auth/pracoviste/prehled_temat.pl");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'detail=' . $id . '&pracovisko=' . $pracovisko . '&lang=sk');

    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    // Check for cURL errors after fetching the linked page
    if (curl_errno($ch)) {
        return "Fetch Error: " . curl_error($ch);
    }

    // Process the response to extract the targeted abstract
    $targetedAbstract = "";
    $dom = new DOMDocument();
    $dom->loadHTML($response);
    //var_dump($response);
    // Find the element containing the targeted text (adjust based on actual HTML structure)
    $abstractElement = $dom->getElementsByTagName('td'); // Assuming the text is within a 'td' element
    // Iterate through the 'td' elements and check for a match
    foreach ($abstractElement as $element) {
        //var_dump($element->textContent);
        if ($element->getAttribute('class') === 'odsazena' && $element->getAttribute('width') === '720' && $element->getAttribute('align') === 'justify') {
            $targetedAbstract = $element->textContent;
            break; // Stop searching after finding the match
        }
    }


    // Close cURL handle
    curl_close($ch);

    // Return the extracted targeted abstract or an empty string if not found
    return $targetedAbstract;
}


function getThemesData($pracovisko, $typ_prace) {
    // Check if the provided type of work is valid
    $valid_typ_prace = ["DP", "DizP", "BP"];
    if (!in_array($typ_prace, $valid_typ_prace)) {
        echo json_encode(['error' => "Invalid type of work. Valid values are 'DP', 'DizP', and 'BP'"], 400);
    }
    
    $html_content = getDataFromPage($pracovisko);
    $themes_data = parseHtmlContent($html_content, [$typ_prace],$pracovisko);
    return $themes_data;
}

// Handling HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
      // Check if the "id_pracoviska" parameter is available
      if (isset($_GET['id_pracoviska'])) {
        $id_pracoviska = $_GET['id_pracoviska'];
  
        // Check if the "typ_prace" parameter is available
        if (isset($_GET['typ_prace'])) {
          $typ_prace = $_GET['typ_prace'];
  
          // Get data from the page for the specified workplace and type of work
          $data = getThemesData($id_pracoviska, $typ_prace);
  
          // Return the acquired data as a response
          echo json_encode($data);
          http_response_code(200);

        } else {
          // If the "typ_prace" parameter is missing
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Missing "typ_prace" parameter']);
          http_response_code(400);

        }
      } else {
        // If the "id_pracoviska" parameter is missing
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Missing "id_pracoviska" parameter'], 400);
        http_response_code(400);
      }
      break;
  
      case 'POST':
        // Obsluha POST požiadavky
          //handlePostRequest();
        break;
  
    case 'DELETE':
      // Not implemented in this example, but you can return a successful response
      header('Content-Type: application/json');
      echo json_encode(['success' => true]);
      break;
  
    case 'PUT':
      // Not implemented in this example, but you can return a successful response
      header('Content-Type: application/json');
      echo json_encode(['success' => true]);
      break;
  
    default:
      // If an invalid HTTP method is used
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Invalid HTTP method'], 405);
      break;
  }
  
?>
