<?php 

function curlConvert($fileName, $contentType, $imageName)
{
    if (function_exists('curl_file_create')) 
    {
        return curl_file_create($fileName, $contentType, $imageName);
    }
 
    $imageString = "@{$fileName};filename=" . $imageName;
    
    if ($contentType) 
    {
        $imageString .= ';type=' . $contentType;
    }
 
    return $imageString;
}   

$targetDirectory = "uploads/";
$targetFile = $targetDirectory . basename($_FILES['fileToUpload']['name']);

move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile);

$title = $_POST['formTitle'];

$fileName = realpath($targetFile);
$image = curlConvert($fileName,'image/jpeg',$_FILES["fileToUpload"]["name"]);
$utf8 = "&#x2713;";
$url = "http://ourdesigngroup.com/photos/new";
$cookie= "cookie.txt";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) 
    die(curl_error($ch));
    
$dom = new DomDocument();
$dom->loadHTML($response);

$metaElements = $dom->getElementsByTagName("meta");

for ($i = 0; $i < $metaElements->length; $i++)
{
    $meta = $metaElements->item($i);
    
    if($meta->getAttribute('name') == 'csrf-token')
        $csrfToken = $meta->getAttribute('content');
}

$postArray = array('utf8' => $utf8,
                  'authenticity_token' => $csrfToken,
                  'photo[title]' => $title,
                  'photo[image]' => $image
                  );

curl_setopt($ch, CURLOPT_URL, "http://ourdesigngroup.com/photos/");
curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postArray);

$html = curl_exec($ch);

if (curl_errno($ch)) print curl_error($ch);
    curl_close($ch);
    
echo "<SCRIPT LANGUAGE='JavaScript'>
    window.alert('Succesfully Uploaded')
    window.location.href='http://ourdesigngroup.com/photos';
    </SCRIPT>";          
?>