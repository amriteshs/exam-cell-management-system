<?php

ecell_sec_session_start();

$dean_and_above = true;
if ($_SESSION['rank'] < $all_ranks['Dean']) {
    $dean_and_above = false;
}

$ar_and_above = true;
if ($_SESSION['rank'] < $all_ranks['AR']) {
    $ar_and_above = false;
}

?>
