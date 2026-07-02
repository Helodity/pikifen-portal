<?php
	session_start();
	include '../../includes/globals.php';
	include '../../includes/functions.php';
	include_once '../../includes/database.php';
			
	header('Content-Type: text/plain; charset=utf-8');
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		header("Location: ". $SITE_ROOT);
		die();
	}

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location:". $SITE_ROOT);
		die();
	}

	//Validation based on PHP's website
	$accID = $_SESSION['accountID'];
	try {
		//Ensure the account is not banned
		$accID = $_SESSION['accountID'];
		$query = "SELECT permissions FROM accounts where id = $accID";
		$result = $conn->query($query);
		
		if(!account_has_permission($accID, PERMISSIONS::UPLOAD_PACKS)) {
			throw new RuntimeException('Forbidden.');
		}

		if (
			!isset($_FILES['pack']['error']) ||
			is_array($_FILES['pack']['error'])
		) {
			throw new RuntimeException('Invalid parameters.');
		}

		// Check $_FILES['upfile']['error'] value.
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
        $rootDir = $LOCAL_ROOT . '/tmp/' . $accID;
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

		//Checking to make sure it has the right files
		$hasThumbnail = $zip->locateName('thumbnail.png');

		$resource = $zip->getStream('data.txt');
		
		if(!$resource) {
		    throw new RuntimeException('Internal Error #4. Contact a moderator.');
		}
		
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
			if($divided[0] == 'tags') {
				//Convert from M.m.r to MMmmrr
				$packTags = htmlspecialchars($divided[1]);
			}
			if($divided[0] == 'version') {
				//Convert from M.m.r to MMmmrr
				$packVersion = version_to_int($divided[1]);
			}
		}
		
		if(!isset($packName) || !isset($packDescription) || !isset($packEngineVersion) || !isset($packVersion)) {
			throw new RuntimeException('Could not validate data.txt');
		}
		
		//File is valid!!! 

		//Making a new pack
		$stmt = $conn->prepare("INSERT INTO packs (name, description, account_id) VALUES (?, ?, ?)");
		$stmt->bind_param("ssi", $packName, $packDescription, $accID);
		$stmt->execute();

		$packID = $conn->insert_id;

		//Set the pack's tags
		replace_pack_tags($packID, $packTags);

		//Add the version now as well
		$stmt = $conn->prepare("INSERT INTO pack_versions (pack_id, engine_version, pack_version) VALUES (?, ?, ?)");
		$stmt->bind_param("iii", $packID, $packEngineVersion, $packVersion);
		$stmt->execute();
		
		$target_dir = $LOCAL_ROOT . "/packs/" . $packID  . "/";
		
		//Make the directory
		mkdir($target_dir, 0777, true);
		
		//Upload the thumbnail if we have it.
		if($hasThumbnail) {
			$thumbnail = $zip->getStream('thumbnail.png');
			file_put_contents($target_dir . "thumbnail.png", $thumbnail);
		}	
		$zip->close();
		
		//Upload the pack
		rename($zipName, $target_dir . getPackFilename($packID, $packVersion, $packEngineVersion));
		
		//Clear the tmp directory
		clear_tmp_directory($rootDir);

	} catch (RuntimeException $e) {
	    clear_tmp_directory($LOCAL_ROOT . '/tmp/' . $accID);
		$_SESSION['errors']['packUpload'] = $e->getMessage();
		header("Location: ../upload"); //Reload last page
		die();
	} catch (ValueError $e) {
	    clear_tmp_directory($LOCAL_ROOT . '/tmp/' . $accID);
		$_SESSION['errors']['packUpload'] = 'Internal Error #1. Contact a moderator.';
		header("Location: ../upload"); // Reload last page
		die();
	}
	header("Location: ../manage?id=" . $packID);
    
    
    function clear_tmp_directory($rootDir){

		if(!file_exists($rootDir)) {
			return;
		}

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
    }
    
?>
