
<?php

	include_once('version_config.php');

	//version_index.php
	//author: Lu Zexi
	//get version json

	$version_name = empty($_GET["version"])?"":$_GET["version"];
	// $version_name = "v1";
	$version_index = 0;

	$version_array = array();
	$res_dir = $RESOURCES_DIR;
	$version_dir = $VERSION_DIR;

	function readAllFile($file_path)
	{
		//is file exist
		if(file_exists($file_path))
		{
			if($fp=fopen($file_path,"a+"))
			{
				//read file
				$conn=fread($fp,filesize($file_path));
				return $conn;
			}
			else
			{
				die("file open fail.\n");
			}
		}
		else
		{
			die("no file exist.\n");
		}
		fclose($fp);
		return null;
	}

	//get version file
	if(is_dir($version_dir))
	{
		if($dh=opendir($version_dir))
		{
			while (($file = readdir($dh))!= false)
			{
				list($file_name,$file_ex) = split('[.]', $file);
				if($file_ex != "json")
					continue;

				list($file_version , $file_time) = split("[_]", $file_name);

				// echo $file_name." - ".$file_ex." - ".$file_time." - ".$file_version."\n";
				//get path
				$filePath = $version_dir.$file;
				$conn = readAllFile($filePath);
				$data = json_decode($conn,true);

				//set data
				array_push($version_array, ["time"=>$file_time , "version"=>$file_version , "data"=>$data]);
			}
			closedir($dh);

			//sort
			function sort_by_time($a , $b)
			{
				if( $a["time"] == $b["time"])
					return 0;
				if( strcmp( $a["time"] , $b["time"] ) > 0 )
				{
					return 1;
				}
				else
				{
					return -1;
				}
			}
			usort($version_array, 'sort_by_time');
			for($i = 0 ; $i<count($version_array) ; $i++)
			{
				$version_file_item = $version_array[$i];
				// echo $version_file_item["version"]."\n";
				if($version_file_item["version"] == $version_name)
				{
					$version_index = $i + 1;
					// echo "version index ".$version_index."\n";
				}
			}
		}
	}

	$result = array();
	for($i = $version_index ; $i<count($version_array) ; $i++)
	{
		$version_item = $version_array[$i];
		$json_data = $version_item["data"];
		for($j = 0 ; $j<count($json_data) ; $j++)
		{
			$data_item = $json_data[$j];
			if( $data_item["operate"] == "new" )
			{
				$result[$data_item["file"]] = "new";
			}
			else if( $data_item["operate"] == "del" )
			{
				if(array_key_exists($data_item["file"], $result) && $result[$data_item["file"]] == "new")
				{
					unset($result[$data_item["file"]]);
					continue;
				}
				$result[$data_item["file"]] = "del";
			}
			else if( $data_item["operate"] == "update" )
			{
				if( array_key_exists($data_item["file"], $result) && $result[$data_item["file"]] == "new" )
				{
					continue;
				}
				$result[$data_item["file"]] = "update";
			}
		}
	}

	$res_data = array();
	$res_data["code"] = 0;
	$res_data["data"] = array();
	$res_data["data"]["version_prename"] = $version_name;
	$res_data["data"]["version_name"] = $version_array[count($version_array)-1]["version"];
	$res_data["data"]["version_file"] = array();
	$res_data["desc"] = "ok";
	foreach ($result as $key => $value)
	{
		array_push($res_data["data"]["version_file"], ["file"=>$key,"operate"=>$value]);
	}

	echo json_encode($res_data);
	return;
