<?php

function ecell_route()
{
    if (isset($_GET['page'])) {
        $page = $_GET['page'];

        if (preg_match("/^stf_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/stf/', 'staff', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^adm_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/adm/', 'admission', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^gen_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/gen/', 'general', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^prc_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/prc/', 'program_courses', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^stu_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/stu/', 'student', $page, 1);
            $page .= ".php";
            header("Location: $page");
        }  else if (preg_match("/^fac_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/fac/', 'faculty', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^res_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/res/', 'results', $page, 1);
            $page .= ".php";
            header("Location: $page");
        }  else if (preg_match("/^prf_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/prf/', 'profile', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else if (preg_match("/^hme_/", $page)) {
            $page = preg_replace('/_/', '/', $page, 1);
            $page = preg_replace('/hme/', 'home', $page, 1);
            $page .= ".php";
            header("Location: $page");
        } else {
            header("Location: home/home.php");
        }
    } else {
        header("Location: home/home.php");
    }
}

ecell_route();

?>