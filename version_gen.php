

<?php

	include_once('version_config.php');

	//version gen php
	//author: Lu Zexi
	//it is a script to genrate version json

	$arg_num = $_SERVER["argc"];
	if($arg_num <3)
	{
		die("please input the version -v version_name.\n");
	}
	$arg1 = $_SERVER["argv"][1];
	$arg1v = $_SERVER["argv"][2];
	$version_name = "v1";
	if($arg1 == "-v")
	{
		$version_name = $arg1v;
	}
	else
	{
		die("no version name ,please input the -v versio_name.\n");
	}
	$version_array = array();
	$version_content = array();
	$now_content = array();
	$new_content = array();
	$res_dir = $RESOURCES_DIR;
	$version_dir = $VERSION_DIR;

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
				if( $file_version == $version_name)
				{
					die("the version name $version_name is already exist.\n");
					return;
				}

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
		}
	}

	//set version content
	for($i = 0 ; $i<count($version_array) ; $i++)
	{
		$item = $version_array[$i];
		$json_data = $item["data"];
		for($j = 0 ; $j<count($json_data) ;$j++)
		{
			$json_item = $json_data[$j];
			if($json_item["operate"] == "new")
			{
				$version_content[$json_item["file"]] = $json_item["md5"];
			}
			else if( $json_item["operate"] == "update" )
			{
				$version_content[$json_item["file"]] = $json_item["md5"];
			}
			else if( $json_item["operate"] == "del" && array_key_exists( $json_item["file"] , $version_content ) )
			{
				unset($version_content[$json_item["file"]]);
			}
		}
	}

	//get all file to md5
	function searchDir($root,$path,&$data)
	{
		$full_path = $root.$path;
		if(is_dir($full_path))
		{
			$dp=dir($full_path);
			while($file=$dp->read())
			{
				if($file!='.'&& $file!='..')
				{
					searchDir($root,$path.'/'.$file,$data);
				}
			}
			$dp->close();
		}
		if(is_file($full_path))
		{
			$data[]=$path;
		}
	}
	function getAllFiles($dir)
	{
		$data = array();
		searchDir($dir,"",$data);
		return $data;
	}
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

	$res_files = getAllFiles($res_dir);
	for( $i = 0 ; $i<count($res_files) ; $i++ )
	{
		$file_content = readAllFile($res_dir."/".$res_files[$i]);
		$now_content[ substr($res_files[$i],1) ] = md5($file_content);
	}

	foreach ($now_content as $key => $value)
	{
		$file_name = $key;
		if( !array_key_exists($file_name , $version_content) )
		{
			$new_content []= [ "file"=>$key , "md5"=>$value , "operate"=>"new" ];
			continue;
		}
		else if( $version_content[$key] != $value )
		{
			$new_content []= [ "file"=>$key , "md5"=>$value , "operate"=>"update" ];
		}
	}
	foreach ($version_content as $key => $value)
	{
		$file_name = $key;
		$file_md5 = $value;
		if( !array_key_exists($file_name , $now_content) )
		{
			$new_content []= [ "file"=>$file_name , "md5"=>$file_md5 , "operate"=>"del" ];
			continue;
		}
	}

	$new_json_data = json_encode($new_content);
	$new_json_filename = $version_dir.$version_name."_".time().".json";

	$new_json_file = fopen($new_json_filename, "w") or die("Unable to open file!\n");
	fwrite($new_json_file, $new_json_data);
	fclose($new_json_file);
	echo "generate version file success: ".$new_json_filename."\n";

