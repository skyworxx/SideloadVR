<?php
/*
	SideloadVR Server Core - Example Implementation
	
	January 27th 2017, Mark Schramm
	www.Mark-Schramm.com


	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.



*/

$apkname = $_GET["package"];
$sig = $_GET["sig"];

//setup folders
$prodAPKFolder='tmp/'.$sig.'_'.$apkname;
$prodAPKName= $prodAPKFolder.'.apk';
$prodSig= 'oculussig_'.$sig;

//some error checking
if (!file_exists("apks/".$apkname.'.apk')){
	echo "Error: apk does not exist";
	return;
}
if (!file_exists("sigs/".$prodSig)){
	echo "Error: signature does not exist. Please upload your signature file.";
	return;
}

//copy apk to main folder
copy("apks/".$apkname.".apk",$prodAPKName);

//decompile apk
mkdir ($prodAPKFolder, 0777);
exec("java -jar apktool.jar d \"".$prodAPKName."\" -f 2>&1", $output);

//copy signature file
exec("cp ./sigs/".$prodSig." ".$prodAPKFolder."/assets/".$prodSig." 2>&1", $output2);

//modifying manifest file
$file=$prodAPKFolder.'/AndroidManifest.xml';
$manifest = file_get_contents($file);
$manifest = str_replace("android.intent.category.INFO","android.intent.category.LAUNCHER",$manifest);
file_put_contents($file, $manifest);

//compile file again
exec("java -jar apktool.jar b \"".$prodAPKFolder."\" 2>&1", $output3);

//sign apk
exec("java -jar signapk.jar certificate.pem key.pk8 \"".$prodAPKFolder."/dist/".$sig.'_'.$apkname.'.apk'."\" \"./baked/signed_".$sig.'_'.$apkname.'.apk'."\" 2>&1", $output5);

//serve file
$file = "./baked/signed_".$prodAPKName;
 if (file_exists($file)) {
   header("Location: ".$file);	
}
?>