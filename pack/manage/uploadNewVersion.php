<?php
	session_start();
	include '../../includes/globals.php';
	include '../../includes/functions.php';
	include_once '../../includes/database.php';
			
	header('Content-Type: text/plain; charset=utf-8');
	//Checks we can't even show errors for.
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		//If the method wasnt set right, return to the home page
		header("Location: ". $SITE_ROOT);
		die();
	}
	
	if(!isset($_SESSION['accountID'])){
		//Require the user to be logged in.
		//Can't get here normally without being logged in, so redirect to home page.
		header("Location:". $SITE_ROOT);
		die();
	}
	
	if(!isset($_POST['packID'])){
		//Trying to modify an unset pack.
		header("Location:". $SITE_ROOT);
		die();
	}
	$packID = $_POST['packID'];

	//Validation based on PHP's website
	try {
		//Ensure the account is not banned
		$accID = $_SESSION['accountID'];
		$query = "SELECT permissions FROM accounts where id = $accID";
		$result = $conn->query($query);
		
		if(!account_has_permission($accID, PERMISSIONS::UPLOAD_PACKS)) {
			throw new RuntimeException('Forbidden.');
		}

		if(!account_owns_pack($accID, $packID)) {
			throw new RuntimeException('Forbidden.');
		}

		//Additional check. If content has a non-zero length but post is empty,
		//We tried to upload but it was wayyyy too big
		if (isset($_SERVER['CONTENT_LENGTH']) && empty($_FILES)) {
			throw new RuntimeException('Packs cannot exceed 30 Megabytes!');
		}

		// Check $_FILES['upfile']['error'] value.
		if (
			!isset($_FILES['pack']['error']) ||
			is_array($_FILES['pack']['error'])
		) {
			throw new RuntimeException('Invalid parameters.');
		}

		switch ($_FILES['pack']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded filesize limit.');
			default:
				throw new RuntimeException('Unknown error.');
		}

		// 5 Megabyte limit
		if ($_FILES['pack']['size'] > 30000000) {
			throw new RuntimeException('Packs cannot exceed 30 Megabytes!');
		}

		// Verify file extension
        $ext = pathinfo($_FILES["pack"]["name"], PATHINFO_EXTENSION);
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		if($ext != "zip" && $finfo->file($_FILES['pack']['tmp_name']) != 'zip'){
			throw new RuntimeException('Invalid file extension.');
		}

		//It sure is a zip file. Let's make sure it's a zip file for pikifen now!
		$zip = new ZipArchive();
		if(!$zip->open($_FILES['pack']['tmp_name'])) {
			throw new RuntimeException('Could not read file!');
		}

		//Extract the zip file, try to find data.txt.
        //We want to check one file deep as well, so zipArchive builtins dont work here.
        $rootDir = $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $accID;
        mkdir($rootDir, 0777, true);
        $zip->extractTo($rootDir);
		
		$pack_root = '';
		$found_data = file_exists($rootDir . '/data.txt');
		if(!$found_data) {
		    //Only check one level deep. We don't want to find the data.txt for mobs and areas.
    		foreach (new DirectoryIterator($rootDir) as $fileinfo) {
                if($fileinfo->isDot()) continue;
                if($fileinfo->isDir()){
                    if(file_exists($rootDir . '/' . $fileinfo->getFilename() . '/data.txt')) {
                        $pack_root = $fileinfo->getFilename() . '/';
                        $found_data = TRUE;
                        break;
                    }
                }
            }
		}
        
        //Could not find data.txt
        if(!$found_data){
            throw new RuntimeException('Could not validate data.txt');
        }
        
        //Rezip the file depending on where data.txt was found.
        $zipRoot = $rootDir . '/' . $pack_root;
        $zipName = $rootDir . '/tmp.zip';
        $zip = new ZipArchive;
        
        
        if($zip->open($zipName, ZipArchive::CREATE ) === TRUE) {

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($zipRoot),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($zipRoot));
            
                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }
            //Create archive
            $zip->close();
        } else {
            throw new RuntimeException('Internal Error #2. Contact a moderator.');
        }
        
        //Zip archive saved. reopen it now.
        $zip = new ZipArchive();
		if(!$zip->open($zipName)) {
			throw new RuntimeException('Internal Error #3. Contact a moderator.');
		}
		
		$resource = $zip->getStream('data.txt');
		
		while (($buffer = fgets($resource)) !== false) {
			
			//Split line into name and value
			$divided = explode('=',$buffer);
			for($i = 0; $i < count($divided); $i++) {
				$divided[$i] = trim($divided[$i]);			
			}
			//Check for sepcific names 
			if($divided[0] == 'name') {
				$packName = htmlspecialchars($divided[1]);
			}
			if($divided[0] == 'description') {
				$packDescription = htmlspecialchars($divided[1]);
			}
			if($divided[0] == 'engine_version') {
				//Convert from M.m.r to MMmmrr
				$packEngineVersion = version_to_int($divided[1]);
			}
			if($divided[0] == 'version') {
				//Convert from M.m.r to MMmmrr
				$packVersion = version_to_int($divided[1]);
			}
		}
		
		if(!isset($packName) || !isset($packDescription) || !isset($packEngineVersion) || !isset($packVersion)) {
			throw new RuntimeException('Could not validate data.txt');
		}
		$zip->close();
		
		//File is valid!!! 

		//Make sure we dont duplicate entries in the database.
		$stmt = $conn->prepare("SELECT * FROM pack_versions WHERE pack_id = ? AND engine_version = ? AND pack_version = ?");
		$stmt->bind_param("iii", $packID, $packEngineVersion, $packVersion);
		$stmt->execute();
		$result = $stmt->get_result();

		//If we dont have an entry for this combo, insert it.
		if(mysqli_num_rows($result) == 0) {
			$stmt = $conn->prepare("INSERT INTO pack_versions (pack_id, engine_version, pack_version) VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $packID, $packEngineVersion, $packVersion);
			$stmt->execute();
		}
		
		$target_dir = $LOCAL_ROOT . "/packs/" . $packID  . "/";
	
		//Upload the pack
		rename($zipName, $target_dir . getPackFilename($packID, $packVersion, $packEngineVersion));
		
		//Clear the tmp directory
    	$it = new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
                     RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
    	//Delete the directory
        rmdir($rootDir);

	} catch (RuntimeException $e) {
		$_SESSION['errors']['packUpload'] = $e->getMessage();
	} catch (ValueError $e) {
		$_SESSION['errors']['packUpload'] = "File is corrupted.";
	}
	header("Location: ../manage?id=" . $packID);

?>
