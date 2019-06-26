<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=myexampleapp;AccountKey=zeniCmdQxnNBMn+GJJhlZLgUxFrl3hLnisNngUuj9s+b19UG7F5L/hjy2zWp4k1oaeJm9tklKqA5YnHiBl3RAw==";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);


 ?>

<html>
 <head>
 <Title>Registration Form</Title>
 <style type="text/css">
 	body { background-color: #fff; border-top: solid 10px #000;
 	    color: #333; font-size: .85em; margin: 20; padding: 20;
 	    font-family: "Segoe UI", Verdana, Helvetica, Sans-Serif;
 	}
 	h1, h2, h3,{ color: #000; margin-bottom: 0; padding-bottom: 0; }
 	h1 { font-size: 2em; }
 	h2 { font-size: 1.75em; }
 	h3 { font-size: 1.2em; }
 	table { margin-top: 0.75em; }
 	th { font-size: 1.2em; text-align: left; border: none; padding-left: 0; }
 	td { padding: 0.25em 2em 0.25em 0em; border: 0 none; }
 </style>
 </head>
 <body>
 <h1>Register here!</h1>
 <p>Fill in your name and email address, then click <strong>Submit</strong> to register.</p>
 <form method="post" action="testing.php" enctype="multipart/form-data" >
       Gambar  <input type="file" name="gambar"  accept=".jpeg,.jpg,.png"  id="gambar"/></br></br>

       <input type="submit" name="submit" value="Submit" />
       <input type="submit" name="load_data" value="Load Data" />
 </form>
 <?php
  $host = "tcp:gdocha.database.windows.net";
    $user = "gdocha";
    $pass = "Ananda66";
    $db = "mymail";

    try {
        $conn = new PDO("sqlsrv:server = $host; Database = $db", $user, $pass);
        $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    } catch(Exception $e) {
        echo "Failed: " . $e;
    }

    if (isset($_POST['submit'])) {
      $createContainerOptions = new CreateContainerOptions();
      $createContainerOptions->addMetaData("key1", "value1");
      $createContainerOptions->addMetaData("key2", "value2");
      $gambar = $_FILES['gambar']['name'];
      $sizeFile = $_FILES['gambar']['size'];
      $typeFile = $_FILES['gambar']['type'];
      $fileToUpload = $_FILES['gambar']['tmp_name'];
      $containerName = "blockblobs".generateRandomString();


        try {

          // Create container.
          $blobClient->createContainer($containerName, $createContainerOptions);

          // Getting local file so that we can upload it to Azure
         $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
          fclose($myfile);

          # Mengunggah file sebagai block blob
          echo "Uploading BlockBlob: ".PHP_EOL;
          echo $fileToUpload;
          echo "<br />";

          $content = fopen($fileToUpload, "r");

          //Upload blob
          $blobClient->createBlockBlob($containerName, $gambar, $content);

          // List blobs.
          $listBlobsOptions = new ListBlobsOptions();
          $listBlobsOptions->setPrefix("HelloWorld");
          $url="";

          do{
              $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
              foreach ($result->getBlobs() as $blob)
              {
                  echo $blob->getName().": ".$blob->getUrl()."<br />";
                  $url = $blob->getUrl();

              }

              $listBlobsOptions->setContinuationToken($result->getContinuationToken());
          } while($result->getContinuationToken());
          echo "<br />";
          //https://myexampleapp.blob.core.windows.net/blockblobsbqgwnp/Royal-Gems-Golf-City-003.jpg
      //  $url = 'https://myexampleapp.blob.core.windows.net/'.$containerName.'/'.$gambar.'';
          $sql_insert = "INSERT INTO tbl_vision2(gambar) VALUES (?);";
          $stmt = $conn->prepare($sql_insert);
           $stmt->bindValue(1, $url);
          $stmt->execute();

        } catch(Exception $e) {
            echo "Failed: " . $e;
        }

        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        catch(InvalidArgumentTypeException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }

        echo "<h3>Your're registered!</h3>";
    } else if (isset($_POST['load_data'])) {
        try {
            $sql_select = "SELECT * FROM tbl_vision2";
            $stmt = $conn->query($sql_select);
            $registrants = $stmt->fetchAll();
            if(count($registrants) > 0) {
                echo "<h2>People who are registered:</h2>";
                echo "<table>";
                echo "<tr><th>Gambar</th>";
                echo "<th>Action</th></tr>";
                foreach($registrants as $registrant) {
                    echo "<tr><td>".$registrant['gambar']."</td>";
                    echo "<td><a href='vision.php?url=".$registrant['gambar']."'>Analisa</a>  </td></tr>";
                }
                echo "</table>";
            } else {
                echo "<h3>No one is currently registered.</h3>";
            }
        } catch(Exception $e) {
            echo "Failed: " . $e;
        }
    }
 ?>

 </body>
 </html>
