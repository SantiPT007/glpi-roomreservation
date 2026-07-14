<?php

// controlador do formulario do asset Sala (add/update/delete/purge), padrao CommonDBTM.
// sem Session::checkCSRF() manual: no GLPI 11 o Symfony consome o token primeiro.

include('../../../inc/includes.php');

$room = new \GlpiPlugin\Roomreservation\Room();

if (isset($_POST['add'])) {
    $room->check(-1, CREATE, $_POST);
    $room->add($_POST);
    \Html::back();
} elseif (isset($_POST['update'])) {
    $room->check((int) $_POST['id'], UPDATE, $_POST);
    $room->update($_POST);
    \Html::back();
} elseif (isset($_POST['delete'])) {
    $room->check((int) $_POST['id'], DELETE, $_POST);
    $room->delete($_POST);
    $room->redirectToList();
} elseif (isset($_POST['restore'])) {
    $room->check((int) $_POST['id'], DELETE, $_POST);
    $room->restore($_POST);
    $room->redirectToList();
} elseif (isset($_POST['purge'])) {
    $room->check((int) $_POST['id'], PURGE, $_POST);
    $room->delete($_POST, 1);
    $room->redirectToList();
} else {
    $id = (int) ($_GET['id'] ?? 0);

    \Html::header(
        \GlpiPlugin\Roomreservation\Room::getTypeName(\Session::getPluralNumber()),
        $_SERVER['PHP_SELF'],
        'assets',
        \GlpiPlugin\Roomreservation\Room::class
    );

    $room->display(['id' => $id]);

    \Html::footer();
}
