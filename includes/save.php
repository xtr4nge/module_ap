<? 
/*
    Copyright (C) 2013-2016 xtr4nge [_AT_] gmail.com

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
?>
<?

include "../../../login_check.php";
include "../../../config/config.php";
include "../_info_.php";
include "../../../functions.php";

// Checking POST & GET variables...
if ($regex == 1) {
    regex_standard($_POST['ap_ht_capab'], "../../../msg.php", $regex_extra);
}

$ap_ht_capab = $_POST['ap_ht_capab'];

if ($ap_ht_capab != "") {
	
	$exec = "$bin_sed -i 's/ap_ht_capab=.*/ap_ht_capab=\\\"".$ap_ht_capab."\\\";/g' ../_info_.php";
	exec_fruitywifi($exec);

    header("Location: ../index.php?tab=4");
    exit;
}

header('Location: ../index.php');

?>